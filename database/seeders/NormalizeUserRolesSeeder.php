<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class NormalizeUserRolesSeeder extends Seeder
{
    public function run(): void
    {
        User::role('instructor')
            ->each(fn (User $user) => $user->syncRoles(['instructor']));
    }
}
