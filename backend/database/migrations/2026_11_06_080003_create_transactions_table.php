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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->foreignId('coa_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->string('description', 255);
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['date', 'coa_id']);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'sqlite', 'pgsql'], true)) {
            DB::statement('ALTER TABLE transactions ADD CONSTRAINT chk_transactions_non_negative CHECK (debit >= 0 AND credit >= 0)');
            DB::statement('ALTER TABLE transactions ADD CONSTRAINT chk_transactions_debit_xor_credit CHECK ((debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

