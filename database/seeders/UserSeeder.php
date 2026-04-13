<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Admins
            ['name' => 'Admin User',     'email' => 'admin@energytrm.com',       'role' => 'admin',        'password' => 'Admin@123!'],
            // Traders
            ['name' => 'James Okafor',   'email' => 'j.okafor@energytrm.com',    'role' => 'trader',       'password' => 'Trader@123!'],
            ['name' => 'Sarah Chen',     'email' => 's.chen@energytrm.com',      'role' => 'trader',       'password' => 'Trader@123!'],
            ['name' => 'Marcus Reeves',  'email' => 'm.reeves@energytrm.com',    'role' => 'trader',       'password' => 'Trader@123!'],
            ['name' => 'Priya Sharma',   'email' => 'p.sharma@energytrm.com',    'role' => 'trader',       'password' => 'Trader@123!'],
            ['name' => 'Tom Hartley',    'email' => 't.hartley@energytrm.com',   'role' => 'trader',       'password' => 'Trader@123!'],
            // Back Office
            ['name' => 'Lisa Kovacs',    'email' => 'l.kovacs@energytrm.com',    'role' => 'back_office',  'password' => 'BackOffice@123!'],
            ['name' => 'David Mensah',   'email' => 'd.mensah@energytrm.com',    'role' => 'back_office',  'password' => 'BackOffice@123!'],
            ['name' => 'Helen Park',     'email' => 'h.park@energytrm.com',      'role' => 'back_office',  'password' => 'BackOffice@123!'],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name'              => $u['name'],
                    'role'              => $u['role'],
                    'password'          => Hash::make($u['password']),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
