<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            DB::table('users')->insert([
                'id' => $data[0],
                'name' => $data[1],
                'email' => $data[2],
                'password' => $data[3], // Already hashed in CSV or needs hashing? Prompt says "$2y$12$..." which is a hash.
                'role' => $data[4],
                'created_at' => $data[5],
                'updated_at' => $data[6],
            ]);
        }
        fclose($file);
    }
}
