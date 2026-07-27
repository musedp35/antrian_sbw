<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'      => 'Super Admin',
                'email'     => 'admin@sistem-sbw.com',
                'password'  => bcrypt('admin123'),
                'role'      => 'super_admin',
            ],
            [
                'name'      => 'Admin Kasir Budi',
                'email'     => 'kasir@sistem-sbw.com',
                'password'  => bcrypt('kasir123'),
                'role'      => 'admin_kasir',
            ],
            [
                'name'      => 'Admin SPP Siti',
                'email'     => 'spp@sistem-sbw.com',
                'password'  => bcrypt('spp123'),
                'role'      => 'admin_spp',
            ],
            [
                'name'      => 'Admin PJ Kartu Andi',
                'email'     => 'pj_kartu@sistem-sbw.com',
                'password'  => bcrypt('pj1234'),
                'role'      => 'admin_pj_kartu',
            ],
        ];

        foreach ($users as $user) {
            \App\Models\User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
