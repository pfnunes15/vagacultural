<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Organization;
use App\Models\Promoter;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Venue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(CategorySeeder::class);

        // Admin
        $this->makeUser('Admin VAGA', 'admin@vagacultural.pt', UserRole::Admin);

        // Organization account -> organization -> managed promoter (trusted) -> published event
        $orgUser = $this->makeUser('Câmara Municipal do Funchal', 'cultura@funchal.pt', UserRole::Organization);
        $organization = Organization::factory()->create([
            'user_id' => $orgUser->id,
            'name' => 'Câmara Municipal do Funchal',
            'slug' => 'cmf',
        ]);

        $promoterUser = $this->makeUser('Teatro Baltazar Dias', 'teatro@funchal.pt', UserRole::Promoter);
        $trusted = Promoter::factory()->create([
            'user_id' => $promoterUser->id,
            'organization_id' => $organization->id,
            'name' => 'Teatro Municipal Baltazar Dias',
            'slug' => 'teatro-baltazar-dias',
            'is_verified' => true,
            'auto_publish' => true,
        ]);

        // Independent promoter (not trusted yet -> events land in "pending")
        $indieUser = $this->makeUser('Associação Raízes', 'geral@raizes.pt', UserRole::Promoter);
        Promoter::factory()->create([
            'user_id' => $indieUser->id,
            'name' => 'Associação Raízes do Atlântico',
            'slug' => 'raizes-do-atlantico',
            'auto_publish' => false,
        ]);

        $venue = Venue::factory()->create([
            'name' => 'Teatro Municipal Baltazar Dias',
            'slug' => 'teatro-municipal-baltazar-dias',
            'municipality' => 'Funchal',
        ]);

        $concertos = Category::query()->where('slug', 'concertos')->first();

        $event = Event::factory()->published()->create([
            'promoter_id' => $trusted->id,
            'title' => 'Orquestra Clássica da Madeira — Noites de Outono',
            'slug' => 'orquestra-classica-noites-de-outono',
            'is_featured' => true,
            'is_free' => false,
            'price_from' => 12,
        ]);
        if ($concertos) {
            $event->categories()->attach($concertos, ['is_primary' => true]);
        }
        EventOccurrence::factory()->create([
            'event_id' => $event->id,
            'venue_id' => $venue->id,
            'starts_at' => now()->addDays(2)->setTime(21, 0),
        ]);
    }

    private function makeUser(string $name, string $email, UserRole $role): User
    {
        [$first, $last] = array_pad(explode(' ', $name, 2), 2, '');
        $user = User::factory()->create(['first_name' => $first, 'last_name' => $last, 'email' => $email]);
        UserRoleAssignment::create(['user_id' => $user->id, 'role' => $role->value]);

        return $user;
    }
}
