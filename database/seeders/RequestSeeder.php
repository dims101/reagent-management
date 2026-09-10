<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequestSeeder extends Seeder
{
    public function run()
    {
        $requests = [];

        for ($i = 1; $i <= 2000; $i++) {
            $requests[] = [
                'request_no' => 1100 + $i,
                'reagent_id' => 2000 + $i,
                'request_qty' => 1,
                'purpose' => 'Request for '.Str::random(10),
                'requested_by' => 3,
                'approval_id' => 1,
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
                'customer_id' => rand(1, 20),
            ];
        }

        DB::table('requests')->insert($requests);
    }
}
