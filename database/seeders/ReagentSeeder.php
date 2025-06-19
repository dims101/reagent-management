<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReagentSeeder extends Seeder
{
    public function run(): void
    {
        $file = base_path('database/seeders/data/reagents.csv');
        if (!file_exists($file)) {
            $this->command->error('File reagents.csv tidak ditemukan.');
            return;
        }

        $rows = array_map('str_getcsv', file($file));
        $header = array_map('trim', explode(';', array_shift($rows)));

        foreach ($rows as $row) {
            $row = array_map('trim', explode(';', implode(';', $row)));
            $reagent = array_combine($header, $row);

            DB::table('reagents')->insert([
                'name'       => $reagent['name'],
                'vendor'     => $reagent['vendor'],
                'catalog_no' => $reagent['catalog_no'],
                'type'       => $reagent['Type'],
            ]);
        }

        $this->command->info('Seeder ReagentSeeder berhasil dijalankan.');
    }
}
