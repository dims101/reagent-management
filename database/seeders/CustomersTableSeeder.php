<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersTableSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/data/customers.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        if (($handle = fopen($csvPath, 'r')) !== false) {

            $header = fgetcsv($handle, 1000, ';'); // Read header row

            if ($header === false || !in_array('name', $header)) {
                $this->command->error("CSV header missing 'name' column.");
                fclose($handle);
                return;
            }

            $nameIndex = array_search('name', $header);

            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                if (isset($row[$nameIndex]) && $row[$nameIndex] !== '') {
                    DB::table('customers')->insert([
                        'name' => $row[$nameIndex],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            fclose($handle);
            $this->command->info('Customers imported successfully.');
        } else {
            $this->command->error("Unable to open CSV file.");
        }
    }
}
