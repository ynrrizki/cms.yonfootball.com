<?php

use App\Enums\AuditType;
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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('user_agent');
            $table->string('resource_type');
            $table->unsignedBigInteger('resource_id');
            $table->json('resource_snapshot')->nullable();
            $table->enum('type', array_map(fn (AuditType $type) => $type->value, AuditType::cases()))->index();
            $table->foreignId('users_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
