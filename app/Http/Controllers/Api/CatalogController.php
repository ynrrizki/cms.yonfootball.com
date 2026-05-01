<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
    public function products(Request $request)
    {
        $query = Product::query()
            ->with([
                'category',
                'variants' => fn ($q) => $q->where('is_active', true),
            ])
            ->where('is_active', true);

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
            });
        }

        $products = $query
            ->orderByDesc('is_popular')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $products->map(fn (Product $product) => $this->productSummary($product)),
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->with([
                'category',
                'variants' => fn ($q) => $q->where('is_active', true)->orderBy('price'),
            ])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->productDetail($product),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productSummary(Product $product): array
    {
        $variants = $product->variants;
        $prices = $variants->map(fn (ProductVariant $variant) => $this->effectivePrice($variant));

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'code' => $product->code,
            'category' => $product->category?->only(['id', 'name']),
            'leading_image_url' => $this->storageUrl($product->leading_url),
            'background_image_url' => $this->storageUrl($product->background_url),
            'is_popular' => $product->is_popular,
            'from_price' => $prices->isEmpty() ? null : $prices->min(),
            'variant_count' => $variants->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productDetail(Product $product): array
    {
        return [
            ...$this->productSummary($product),
            'inputs' => $product->inputs ?? [],
            'variants' => $product->variants->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'price_original' => $variant->price_original,
                'price' => $variant->price,
                'price_discount' => $variant->price_discount,
                'effective_price' => $this->effectivePrice($variant),
                'is_active' => $variant->is_active,
            ]),
        ];
    }

    private function effectivePrice(ProductVariant $variant): int
    {
        if ($variant->price_discount > 0) {
            return max(0, $variant->price - $variant->price_discount);
        }

        return $variant->price;
    }

    private function storageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
