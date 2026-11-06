<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('type', 20)->default('expense')->index(); // 'income' | 'expense'
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'sqlite', 'pgsql'], true)) {
            DB::statement("ALTER TABLE categories ADD CONSTRAINT chk_categories_type CHECK (type IN ('income', 'expense'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

