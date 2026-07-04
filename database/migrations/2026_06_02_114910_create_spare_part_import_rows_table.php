<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_part_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('spare_part_import_batches')->cascadeOnDelete();

            $table->string('city_name_raw')->nullable();
            $table->string('type_name_raw')->nullable();
            $table->string('category_name_raw')->nullable();
            $table->string('maintenance_city_name_raw')->nullable();
            $table->longText('location_raw')->nullable();
            $table->longText('technical_description_raw')->nullable();
            $table->string('quantity_raw')->nullable();
            $table->string('status_raw')->nullable();
            $table->string('estimated_cost_raw')->nullable();
            $table->string('maintenance_cost_raw')->nullable();

            $table->foreignId('city_id')->nullable()->constrained('cities')->restrictOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('spare_part_types')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('spare_part_categories')->restrictOnDelete();
            $table->foreignId('maintenance_city_id')->nullable()->constrained('cities')->restrictOnDelete();

            $table->integer('quantity')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('maintenance_cost', 12, 2)->nullable();
            $table->string('status')->nullable();

            $table->boolean('has_errors')->default(false);
            $table->json('errors')->nullable();

            $table->timestamps();

            $table->index(['batch_id', 'has_errors']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_part_import_rows');
    }
};
