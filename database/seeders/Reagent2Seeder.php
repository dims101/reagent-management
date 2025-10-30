<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Reagent2Seeder extends Seeder
{
    public function run(): void
    {
        $data = [];
        $now = Carbon::now();

        for ($i = 0; $i < 10000; $i++) {
            $deptOwnerId = [1, 2][array_rand([1, 2])];
            $inputBy = [6, 13][array_rand([6, 13])];

            $data[] = [
                'reagent_name'   => 'Reagent ' . Str::random(5),
                'po_no'          => 'PO-' . rand(10000, 99999),
                'maker'          => ['Sigma', 'Merck', 'Thermo', 'BioLab'][array_rand(['Sigma', 'Merck', 'Thermo', 'BioLab'])],
                'catalog_no'     => 'CAT-' . strtoupper(Str::random(6)),
                'site'           => ['Lab A', 'Lab B', 'Lab C'][array_rand(['Lab A', 'Lab B', 'Lab C'])],
                'price'          => rand(1000, 100000),
                'lead_time'      => rand(1, 30),
                'initial_qty'    => rand(1, 100),
                'remaining_qty'  => rand(0, 100),
                'minimum_qty'    => rand(1, 20),
                'quantity_uom'   => ['ml', 'L', 'g', 'kg', 'pcs'][array_rand(['ml', 'L', 'g', 'kg', 'pcs'])],
                'expired_date'   => $now->copy()->addDays(rand(30, 1000)),
                'dept_owner_id'  => $deptOwnerId,
                'created_at'     => $now,
                'updated_at'     => $now,
                'deleted_at'     => null,
                'location'       => ['Gudang Utama', 'Gudang 2', 'Lab Storage'][array_rand(['Gudang Utama', 'Gudang 2', 'Lab Storage'])],
                'input_by'       => $inputBy,
            ];

            // insert per 1000 untuk efisiensi
            if (count($data) >= 1000) {
                DB::table('stocks')->insert($data);
                $data = [];
            }
        }

        if (!empty($data)) {
            DB::table('stocks')->insert($data);
        }
    }
}
