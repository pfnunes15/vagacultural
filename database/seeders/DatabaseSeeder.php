<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin VAGA',
            'email' => 'admin@vagacultural.pt',
        ]);

        UserRoleAssignment::create([
            'user_id' => $admin->id,
            'role' => UserRole::Admin->value,
        ]);
    }
}
