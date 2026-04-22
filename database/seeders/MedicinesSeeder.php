<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Medicine::create([
            'name' => 'Losartan 50mg and Amlopidine 5mg',
            'amount_left' => 10
        ]);
        Medicine::create([
            'name' => 'Metformin 500mg',
            'amount_left' => 5
        ]);
        Medicine::create([
            'name' => 'Simvastatin 20mg',
            'amount_left' => 7
        ]);
        Medicine::create([
            'name' => 'Aspirin 75mg',
            'amount_left' => 9
        ]);
        Medicine::create([
            'name' => 'Furosemide 20mg',
            'amount_left' => 10
        ]);
        Medicine::create([
            'name' => 'Omeprazole 20mg',
            'amount_left' => 3
        ]);
        Medicine::create([
            'name' => 'Paracetamol 500mg',
            'amount_left' => 1
        ]);
        Medicine::create([
            'name' => 'Calcium Carbonate 500mg',
            'amount_left' => 4
        ]);
        Medicine::create([
            'name' => 'Montelukast 10mg',
            'amount_left' => 7
        ]);
        Medicine::create([
            'name' => 'Cetirizine 10mg',
            'amount_left' => 3
        ]);
    }
}
