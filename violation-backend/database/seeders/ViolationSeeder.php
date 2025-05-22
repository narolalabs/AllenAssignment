<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Violation;

class ViolationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Violation::create([
            'borough' => 'MANHATTAN',
            'house_number' => '110',
            'street_name' => 'W 97 ST',
            'violation_type' => 'Sidewalk',
            'description' => 'Cracked sidewalk',
            'violation_number' => 'V123456'
        ]);

        Violation::create([
            'borough' => 'MANHATTAN',
            'house_number' => '150',
            'street_name' => 'E 86 ST',
            'violation_type' => 'Sidewalk',
            'description' => 'Uneven pavement',
            'violation_number' => 'V789123'
        ]);
    }
}
