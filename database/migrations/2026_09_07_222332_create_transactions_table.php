<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('ts_id');
            $table->foreignId('cat_id')
                ->constrained('expense_categories', 'cat_id')
                ->cascadeOnDelete();
            $table->decimal('ts_amount', 16, 2);
            $table->date('ts_date');
            $table->text('ts_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
