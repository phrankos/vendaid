<?php

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string(column: "scan_id");
            $table->string(column: 'transaction');
            $table->timestamps();
        });
        
        Schema::create('pending_transactions', function (Blueprint $table) {
            $table->id();
            $table->string(column: "scan_id");
            $table->string(column: 'transaction_hash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
