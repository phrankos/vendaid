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
            'scan_id' => random_int(1000000000, 9999999999),
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
            'scan_id' => random_int(1000000000, 9999999999),
            'first_name' => "Maria",
            'middle_name' => "Clara",
            'last_name' => "Ole",
            'sex_id' => 2,
            'created_by' => 1,
            'updated_by' => 1,
            'claimed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        Patient::create([
            'scan_id' => random_int(1000000000, 9999999999),
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
