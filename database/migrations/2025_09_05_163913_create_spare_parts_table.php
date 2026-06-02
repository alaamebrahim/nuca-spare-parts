<?php

use App\Enums\SparePartStatusEnum;
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
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities');
            $table->foreignId('type_id')->constrained('spare_part_types');
            $table->foreignId('category_id')->constrained('spare_part_categories');
            $table->longText('location')->nullable();
            $table->longText('technical_description')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('status')->default(SparePartStatusEnum::New->value);
            $table->decimal('estimated_cost', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
