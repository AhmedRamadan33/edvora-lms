<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsDemoHelpers;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use SeedsDemoHelpers;

    public function run(): void
    {
        $this->makeUser('Edvora Admin', 'admin@edvora.test', 'en', ['admin'], 'Platform administrator');
        $this->makeUser('Sara Hassan', 'instructor@edvora.test', 'en', ['instructor'], 'Senior Laravel instructor');
        $this->makeUser('Ahmed Ali', 'ahmed@edvora.test', 'ar', ['instructor'], 'UI/UX and frontend mentor');
        $this->makeUser('Nour Ibrahim', 'nour@edvora.test', 'en', ['instructor'], 'Data & AI instructor');
        $this->makeUser('Layla Mansour', 'layla@edvora.test', 'ar', ['instructor'], 'Marketing & business coach');
        $this->makeUser('Karim Farouk', 'karim@edvora.test', 'en', ['instructor'], 'DevOps & cloud engineer');
        $this->makeUser('Mona Pending', 'pending@edvora.test', 'en', ['instructor'], 'Awaiting instructor approval');
        $this->makeUser('Omar Student', 'student@edvora.test', 'ar', ['student'], 'Demo student account');
        $this->makeUser('Lina Student', 'lina@edvora.test', 'en', ['student'], 'Demo student account');
        $this->makeUser('Youssef Student', 'youssef@edvora.test', 'ar', ['student'], 'Demo student account');
    }
}
