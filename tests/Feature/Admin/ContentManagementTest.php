<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use App\Models\UserRoleAssignment;

function adminUser(): User
{
    $u = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $u->id, 'role' => UserRole::Admin->value]);

    return $u;
}

it('creates a category with translated names and a slug', function (): void {
    $this->actingAs(adminUser())->post(route('admin.categories.store'), [
        'name' => ['pt' => 'Ópera', 'en' => 'Opera', 'fr' => 'Opéra'],
        'color' => '#123456',
        'is_active' => '1',
    ])->assertRedirect(route('admin.categories.index'));

    $cat = Category::where('slug', 'opera')->first();
    expect($cat)->not->toBeNull()
        ->and($cat->getTranslation('name', 'en'))->toBe('Opera')
        ->and($cat->getTranslation('name', 'fr'))->toBe('Opéra');
});

it('updates and deletes a category', function (): void {
    $admin = adminUser();
    $cat = Category::factory()->create();

    $this->actingAs($admin)->put(route('admin.categories.update', $cat), [
        'name' => ['pt' => 'Atualizada'],
        'is_active' => '1',
    ])->assertRedirect();
    expect($cat->fresh()->getTranslation('name', 'pt'))->toBe('Atualizada');

    $this->actingAs($admin)->delete(route('admin.categories.destroy', $cat))->assertRedirect();
    $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
});

it('creates a venue with a generated slug', function (): void {
    $this->actingAs(adminUser())->post(route('admin.venues.store'), [
        'name' => 'Teatro Novo',
        'municipality' => 'Funchal',
        'is_active' => '1',
    ])->assertRedirect(route('admin.venues.index'));

    $this->assertDatabaseHas('venues', ['name' => 'Teatro Novo', 'slug' => 'teatro-novo']);
});

it('creates an organization and grants the owner the organization role', function (): void {
    $owner = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $owner->id, 'role' => UserRole::User->value]);

    $this->actingAs(adminUser())->post(route('admin.organizations.store'), [
        'name' => 'Fundação Cultural',
        'user_id' => $owner->id,
        'is_active' => '1',
    ])->assertRedirect(route('admin.organizations.index'));

    $this->assertDatabaseHas('organizations', ['name' => 'Fundação Cultural', 'user_id' => $owner->id]);
    expect($owner->fresh()->isOrganization())->toBeTrue();
});

it('blocks non-admins from content management', function (): void {
    $promoter = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $promoter->id, 'role' => UserRole::Promoter->value]);

    $this->actingAs($promoter)->get(route('admin.categories.index'))->assertForbidden();
    $this->actingAs($promoter)->get(route('admin.venues.create'))->assertForbidden();
    $this->actingAs($promoter)->post(route('admin.organizations.store'), ['name' => 'X'])->assertForbidden();
});

it('validates the default-locale category name is required', function (): void {
    $this->actingAs(adminUser())->post(route('admin.categories.store'), [
        'name' => ['en' => 'Only English'],
    ])->assertSessionHasErrors('name.pt');
});
