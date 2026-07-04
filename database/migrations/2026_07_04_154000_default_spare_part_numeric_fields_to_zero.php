<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('spare_parts')
            ->whereNull('maintenance_cost')
            ->update(['maintenance_cost' => 0]);

        Schema::table('spare_parts', function (Blueprint $table) {
            $table->decimal('maintenance_cost', 12, 2)->default(0)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            $table->decimal('maintenance_cost', 12, 2)->nullable()->default(null)->change();
        });
    }
};
