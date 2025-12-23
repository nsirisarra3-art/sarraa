<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;

class BookingSeeder extends Seeder
{
    public function run()
    {
        Booking::insert([
            ['trip_id'=>1,'customer_id'=>1,'seats'=>2,'total_price'=>599.98,'status'=>'confirmed','created_at'=>now(),'updated_at'=>now()],
            ['trip_id'=>2,'customer_id'=>2,'seats'=>1,'total_price'=>599.00,'status'=>'pending','created_at'=>now(),'updated_at'=>now()],
            ['trip_id'=>3,'customer_id'=>3,'seats'=>3,'total_price'=>1198.5,'status'=>'confirmed','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
