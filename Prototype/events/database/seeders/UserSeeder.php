<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('data/users.csv');
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header

        while (($data = fgetcsv($file)) !== false) {
            User::create([
                'id' => $data[0],
                'name' => $data[1],
                'email' => $data[2],
                'password' => $data[3],
                'role' => $data[4],
                'created_at' => $data[5],
                'updated_at' => $data[6],
            ]);
        }
        fclose($file);
    }
}
