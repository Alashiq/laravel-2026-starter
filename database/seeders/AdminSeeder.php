<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {


        DB::table('roles')->insert([
            'name' => 'المدير',
            'permissions' => '["HomeLabel","HomeChart","SettingLabel","RolePermissionsList","ReadAdmin","DeleteAdmin","CreateAdmin","ActiveAdmin","DisActiveAdmin","BannedAdmin","ResetPasswordAdmin","EditRoleAdmin","ReadRole","CreateRole","EditRole","DeleteRole","DataLabel","ReadCity","CreateCity","EditCity","DeleteCity","ReadHall","CreateHall","EditHall","DeleteHall","ActiveHall","DisActiveHall","ReadCaterer","CreateCaterer","EditCaterer","DeleteCaterer","ActiveCaterer","DisActiveCaterer","ReadProduct","CreateProduct","EditProduct","DeleteProduct","ActiveProduct","DisActiveProduct","ReadCatererProduct","CreateCatererProduct","EditCatererProduct","DeleteCatererProduct","ReadUser","CreateUser","EditUser","DeleteUser","ActiveUser","DisActiveUser","BanUser","UnbanUser","ReadUserNotification","CreateUserNotification","DeleteUserNotification"]',
        ]);


        DB::table('admins')->insert([
            'phone' => '0926503011',
            'first_name' => 'Abdulsmaia',
            'last_name' => 'Alashiq',
            'role_id' => 1,
            'password' => Hash::make('123123'),
            'status' => 1,
        ]);
    }
}
