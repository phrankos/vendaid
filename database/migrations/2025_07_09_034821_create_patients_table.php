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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string(column: "mosip");
            $table->string(column: "last_name", length:255);
            $table->string(column: "first_name", length:255);
            $table->string(column: "middle_name", length:255)->nullable();
            $table->foreignId(column: "sex_id")->constrained(
                table:"sexes"
            );
            $table->foreignId(column: "created_by")->constrained(
                table:"users"
            );
            $table->foreignId(column: "updated_by")->constrained(
                table:"users"
            );
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
