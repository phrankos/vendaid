<?php

namespace Database\Seeders;

use App\Models\User;
// use App\Models\Patient;
// use App\Models\Role;
// use Database\Seeders\RolesSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
        	RoleSeeder::class,
    	]);
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'role_id'=> 1,
        ]);
        $this->call([
            SexSeeder::class,
            MedicinesSeeder::class,
            PatientSeeder::class,
            PrescriptionSeeder::class,
    	]);
    }
}
