<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('middle_name');
            $table->string('barangay')->nullable()->after('birthdate');
            $table->string('scan_id')->nullable()->unique()->change();
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('updated_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'birthdate')) $table->dropColumn('birthdate');
            if (Schema::hasColumn('patients', 'barangay')) $table->dropColumn('barangay');
            $table->string('scan_id')->nullable(false)->change();
        });

        DB::table('patients')->whereNull('created_by')->update(['created_by' => 1]);
        DB::table('patients')->whereNull('updated_by')->update(['updated_by' => 1]);

        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->unsignedBigInteger('updated_by')->nullable(false)->change();
        });
    }
};
