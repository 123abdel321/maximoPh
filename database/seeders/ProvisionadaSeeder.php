<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Database\Seeders\EntornosTableSeeder;


class ProvisionadaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        $this->call(EntornosTableSeeder::class);

        DB::statement('SET foreign_key_checks = 1');
    }
}
