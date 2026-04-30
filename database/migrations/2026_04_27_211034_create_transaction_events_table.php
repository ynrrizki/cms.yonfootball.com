<?php

use App\Enums\TransactionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->string('provider_event_id')->nullable()->unique();
            $table->enum('source', ['WEBHOOK', 'SYSTEM', 'ADMIN'])->index();
            $table->enum('status_before', array_map(fn (TransactionStatus $status) => $status->value, TransactionStatus::cases()))->nullable();
            $table->enum('status_after', array_map(fn (TransactionStatus $status) => $status->value, TransactionStatus::cases()));
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_events');
    }
};
