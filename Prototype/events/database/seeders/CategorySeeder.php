<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('data/categories.csv');
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header

        while (($data = fgetcsv($file)) !== false) {
            Category::create([
                'id' => $data[0],
                'name' => $data[1],
                'created_at' => $data[2],
                'updated_at' => $data[3],
            ]);
        }
        fclose($file);
    }
}
