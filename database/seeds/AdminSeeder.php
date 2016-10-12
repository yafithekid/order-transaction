<?php

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (config('database.default') == 'pgsql'){
            DB::statement("SELECT setval('admins_id_seq',1,FALSE)");
        }
        $admin = new Admin();
        $admin->name = 'admin 1';
        $admin->token = 'token1';
        $admin->save();
    }
}
