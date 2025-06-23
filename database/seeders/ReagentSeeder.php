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

        $handle = fopen($file, 'r');

        // Ambil header kolom
        $header = fgetcsv($handle, 1000, ';');

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $reagent = array_combine($header, $row);

            $reagent = array_map(fn($v) => mb_convert_encoding($v, 'UTF-8', 'auto'), $reagent);

            DB::table('reagents')->insert([
                'name'       => $reagent['name'] ?? null,
                'vendor'     => $reagent['vendor'] ?? null,
                'catalog_no' => $reagent['catalog_no'] ?? null,
                'type'       => $reagent['Type'] ?? null,
            ]);
        }

        fclose($handle);

        $this->command->info('Seeder ReagentSeeder berhasil dijalankan.');
    }
}
