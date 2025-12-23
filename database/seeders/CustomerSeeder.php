<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        Customer::insert([
            ['name'=>'Alice Dupont','email'=>'alice@example.com','phone'=>'+33123456789','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Mohamed Ben','email'=>'mohamed@example.com','phone'=>'+212612345678','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Leyla Kaya','email'=>'leyla@example.com','phone'=>'+905321234567','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
