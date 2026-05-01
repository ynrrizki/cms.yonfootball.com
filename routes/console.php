<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\Product;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('catalog:import-images {manifest=storage/app/catalog-image-import.json}', function (string $manifest): void {
    if (! File::exists($manifest)) {
        $this->error("Manifest not found: {$manifest}");
        return;
    }

    $rows = json_decode(File::get($manifest), true);
    if (! is_array($rows)) {
        $this->error('Invalid manifest format.');
        return;
    }
    if (count($rows) === 0) {
        $this->warn('Manifest is empty. No image records to import.');
        return;
    }

    $updated = 0;
    $missing = 0;

    foreach ($rows as $row) {
        $slug = $row['slug'] ?? null;
        $leadingUrl = $row['leading_url'] ?? null;
        $backgroundUrl = $row['background_url'] ?? null;

        if (! $slug || ! $leadingUrl || ! $backgroundUrl) {
            continue;
        }

        $product = Product::query()->where('slug', $slug)->first();
        if (! $product) {
            $missing++;
            $this->warn("Product not found for slug: {$slug}");
            continue;
        }

        $product->update([
            'leading_url' => $leadingUrl,
            'background_url' => $backgroundUrl,
        ]);
        $updated++;
    }

    $this->info("Done. Updated: {$updated}, missing products: {$missing}");
})->purpose('Import scraped catalog images into product media fields');

Artisan::command('catalog:validate-images', function (): void {
    $products = Product::query()
        ->orderBy('name')
        ->get(['name', 'slug', 'leading_url', 'background_url']);

    $missingBoth = [];
    $missingLeading = [];
    $missingBackground = [];

    foreach ($products as $product) {
        $hasLeading = filled($product->leading_url);
        $hasBackground = filled($product->background_url);

        if (! $hasLeading && ! $hasBackground) {
            $missingBoth[] = $product;
            continue;
        }

        if (! $hasLeading) {
            $missingLeading[] = $product;
        }

        if (! $hasBackground) {
            $missingBackground[] = $product;
        }
    }

    $completeCount = $products->count() - count($missingBoth) - count($missingLeading) - count($missingBackground);
    $this->info("Catalog image validation summary:");
    $this->line("- Total products: {$products->count()}");
    $this->line("- Complete (leading + background): {$completeCount}");
    $this->line("- Missing both: " . count($missingBoth));
    $this->line("- Missing leading only: " . count($missingLeading));
    $this->line("- Missing background only: " . count($missingBackground));

    if (count($missingBoth) > 0) {
        $this->newLine();
        $this->warn('Products missing both images:');
        foreach ($missingBoth as $product) {
            $this->line("- {$product->name} ({$product->slug})");
        }
    }

    if (count($missingLeading) > 0) {
        $this->newLine();
        $this->warn('Products missing leading image:');
        foreach ($missingLeading as $product) {
            $this->line("- {$product->name} ({$product->slug})");
        }
    }

    if (count($missingBackground) > 0) {
        $this->newLine();
        $this->warn('Products missing background image:');
        foreach ($missingBackground as $product) {
            $this->line("- {$product->name} ({$product->slug})");
        }
    }

    if (count($missingBoth) === 0 && count($missingLeading) === 0 && count($missingBackground) === 0) {
        $this->newLine();
        $this->info('All products have leading and background images.');
    }
})->purpose('Validate catalog image completeness for products');

Artisan::command('catalog:images-pipeline {manifest=storage/app/catalog-image-import.json}', function (string $manifest): void {
    $this->info('Step 1/3 - Importing catalog images...');
    $this->call('catalog:import-images', [
        'manifest' => $manifest,
    ]);

    $this->newLine();
    $this->info('Step 2/3 - Building validation report...');

    $products = Product::query()
        ->orderBy('name')
        ->get(['name', 'slug', 'leading_url', 'background_url']);

    $missingBoth = [];
    $missingLeading = [];
    $missingBackground = [];
    $complete = [];

    foreach ($products as $product) {
        $hasLeading = filled($product->leading_url);
        $hasBackground = filled($product->background_url);

        if ($hasLeading && $hasBackground) {
            $complete[] = [
                'name' => $product->name,
                'slug' => $product->slug,
            ];
            continue;
        }

        if (! $hasLeading && ! $hasBackground) {
            $missingBoth[] = [
                'name' => $product->name,
                'slug' => $product->slug,
            ];
            continue;
        }

        if (! $hasLeading) {
            $missingLeading[] = [
                'name' => $product->name,
                'slug' => $product->slug,
            ];
        }

        if (! $hasBackground) {
            $missingBackground[] = [
                'name' => $product->name,
                'slug' => $product->slug,
            ];
        }
    }

    $report = [
        'generated_at' => now()->toIso8601String(),
        'manifest' => $manifest,
        'summary' => [
            'total_products' => $products->count(),
            'complete' => count($complete),
            'missing_both' => count($missingBoth),
            'missing_leading_only' => count($missingLeading),
            'missing_background_only' => count($missingBackground),
        ],
        'complete' => $complete,
        'missing_both' => $missingBoth,
        'missing_leading_only' => $missingLeading,
        'missing_background_only' => $missingBackground,
    ];

    $reportPath = storage_path('app/catalog-image-validation-report.json');
    File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->newLine();
    $this->info('Step 3/3 - Done.');
    $this->line("Report saved to: {$reportPath}");
    $this->line('Summary:');
    $this->line("- Total products: {$report['summary']['total_products']}");
    $this->line("- Complete: {$report['summary']['complete']}");
    $this->line("- Missing both: {$report['summary']['missing_both']}");
    $this->line("- Missing leading only: {$report['summary']['missing_leading_only']}");
    $this->line("- Missing background only: {$report['summary']['missing_background_only']}");
})->purpose('Run import + validation pipeline and export catalog image report');
