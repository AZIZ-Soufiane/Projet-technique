<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Category;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('data/events.csv');
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header

        while (($data = fgetcsv($file)) !== false) {
            $eventId = DB::table('events')->insertGetId([
                'id' => $data[0],
                'title' => $data[1],
                'description' => $data[2],
                'event_date' => $data[3],
                'image' => $data[4],
                'status' => $data[5],
                'user_id' => 1, // Assign to Admin User
                'created_at' => $data[7],
                'updated_at' => $data[8],
            ]);

            // Assign multiple categories to the event by name
            // Categories are pipe-separated (|) in the CSV
            $categoryNames = explode('|', $data[6]);
            foreach ($categoryNames as $categoryName) {
                $category = DB::table('categories')
                    ->where('name', trim($categoryName))
                    ->first();
                
                if ($category) {
                    DB::table('category_event')->insert([
                        'event_id' => $eventId,
                        'category_id' => $category->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        fclose($file);
    }
}
