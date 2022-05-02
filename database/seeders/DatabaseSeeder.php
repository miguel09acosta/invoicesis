<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
/* use App\Models\Product;
use App\Models\Store; */

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        DB::table('users')->insert([
            'name' => 'Miguel Angel',
            'email' => 'miguel09acosta@gmail.com',
            'password' => Hash::make('123'),
            'rol' => 1,
        ]);
        DB::table('users')->insert([
            'name' => 'Usuario',
            'email' => 'usuario@test.com',
            'password' => Hash::make('123'),
            'rol' => 2,
        ]);

        /* $products = Product::factory()->count(3)->create();
        $products = Store::factory()->count(3)->create(); */
    }
}
