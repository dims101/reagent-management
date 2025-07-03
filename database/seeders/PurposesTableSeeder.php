<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurposesTableSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/data/purposes.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        if (($handle = fopen($csvPath, 'r')) !== false) {

            $header = fgetcsv($handle, 1000, ';'); // Read header row

            if ($header === false || !in_array('name', $header) || !in_array('type', $header)) {
                $this->command->error("CSV header must contain 'name' and 'type' columns.");
                fclose($handle);
                return;
            }

            $nameIndex = array_search('name', $header);
            $typeIndex = array_search('type', $header);

            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                if (isset($row[$nameIndex], $row[$typeIndex])) {
                    DB::table('purposes')->insert([
                        'name' => $row[$nameIndex],
                        'type' => $row[$typeIndex],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            fclose($handle);
            $this->command->info('Purposes imported successfully.');
        } else {
            $this->command->error("Unable to open CSV file.");
        }
    }
}
