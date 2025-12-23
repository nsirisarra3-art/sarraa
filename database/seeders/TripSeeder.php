<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;

class TripSeeder extends Seeder
{
    public function run()
    {
        Trip::insert([
            ['title'=>'Paris Getaway','description'=>'Weekend in Paris','destination'=>'Paris','price'=>299.99,'start_date'=>'2026-02-10','end_date'=>'2026-02-14','created_at'=>now(),'updated_at'=>now()],
            ['title'=>'Morocco Adventure','description'=>'Explore Marrakesh and desert','destination'=>'Marrakesh','price'=>599.00,'start_date'=>'2026-03-05','end_date'=>'2026-03-12','created_at'=>now(),'updated_at'=>now()],
            ['title'=>'Istanbul Discovery','description'=>'Historic Istanbul tour','destination'=>'Istanbul','price'=>399.50,'start_date'=>'2026-04-20','end_date'=>'2026-04-25','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
