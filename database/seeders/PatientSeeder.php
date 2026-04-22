<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Patient::create([
            'mosip' => 1,
            'first_name' => "Juan",
            'middle_name' => "de la",
            'last_name' => "Cruz",
            'sex_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        Patient::create([
            'mosip' => 2,
            'first_name' => "Maria",
            'middle_name' => "Clara",
            'last_name' => "Ole",
            'sex_id' => 2,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        Patient::create([
            'mosip' => 3,
            'first_name' => "Rosario",
            'middle_name' => "Garcia",
            'last_name' => " Lopez",
            'sex_id' => 2,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
