<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::create('prescriptions', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId(column: "patient_id")->constrained(
        //         table:"patients"
        //     );
        //     $table->foreignId(column: "issued_by")->constrained(
        //         table:"users"
        //     );
        //     $table->date(column: "expires_at");
        //     $table->timestamps();
        // });
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId(column: "patient_id")->constrained(
                table:"patients"
            );
            $table->foreignId(column: "issued_by")->constrained(
                table:"users"
            );
            $table->date(column: "expires_at");
            // $table->boolean(column: "medicine_01");
            // $table->boolean(column: "medicine_02");
            // $table->boolean(column: "medicine_03");
            // $table->boolean(column: "medicine_04");
            // $table->boolean(column: "medicine_05");
            // $table->boolean(column: "medicine_06");
            // $table->boolean(column: "medicine_07");
            // $table->boolean(column: "medicine_08");
            // $table->boolean(column: "medicine_09");
            // $table->boolean(column: "medicine_10");
            $table->binary(column: "medicines_binary");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
