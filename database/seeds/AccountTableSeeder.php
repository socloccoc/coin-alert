<?php

use Illuminate\Database\Seeder;

class AccountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('account')->insert([
            'name' => 'admin',
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'is_root_admin' => true,
            'is_active'=> true
        ]);
    }
}
