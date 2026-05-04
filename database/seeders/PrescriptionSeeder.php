<?php

namespace Database\Seeders;
use App\Models\Prescription;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Prescription::create([
            'patient_id' => 1,
            'issued_by' => 1,
            'expires_at' => now(),
            'medicines_binary' => "10001100",
            'created_at' => now(),
            'updated_at' => now()
        ]);
        Prescription::create([
            'patient_id' => 2,
            'issued_by' => 1,
            'expires_at' => now(),
            'medicines_binary' => "10000000",
            'created_at' => now(),
            'updated_at' => now()
        ]);
        Prescription::create([
            'patient_id' => 3,
            'issued_by' => 1,
            'expires_at' => now(),
            'medicines_binary' => "01100000",
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
