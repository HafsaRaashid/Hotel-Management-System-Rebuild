<?php

namespace Tests\Feature;

use App\Models\RoomCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-003 (BL-005). AC-N references are to
 * .specclaw/changes/003-room-rate-management/spec.md.
 *
 * Only the CSRF tests override app('env') to 'production' - see
 * tests/Feature/CsrfProtectionTest.php's comment on why every other test
 * relies on the default APP_ENV=testing bypass instead.
 */
class RoomCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_seeds_exactly_the_three_legacy_categories(): void
    {
        $this->assertSame(3, RoomCategory::count());

        $this->assertDatabaseHas('room_categories', ['name' => 'Single Room', 'price' => 99]);
        $this->assertDatabaseHas('room_categories', ['name' => 'Double Room', 'price' => 149]);
        $this->assertDatabaseHas('room_categories', ['name' => 'Deluxe Room', 'price' => 199]);
    }

    public function test_create_persists_name_and_price(): void
    {
        $this->post(route('room-categories.store'), [
            'name' => 'Suite',
            'price' => 299,
        ])->assertRedirect(route('room-categories.index'));

        $this->assertDatabaseHas('room_categories', ['name' => 'Suite', 'price' => 299]);
    }

    public function test_edit_updates_name_and_price(): void
    {
        $category = RoomCategory::create(['name' => 'Temp', 'price' => 50]);

        $this->put(route('room-categories.update', $category), [
            'name' => 'Updated',
            'price' => 75,
        ])->assertRedirect(route('room-categories.index'));

        $category->refresh();

        $this->assertSame('Updated', $category->name);
        $this->assertSame(75, $category->price);
    }

    public function test_delete_removes_category(): void
    {
        $category = RoomCategory::create(['name' => 'Temp', 'price' => 50]);

        $this->delete(route('room-categories.destroy', $category))
            ->assertRedirect(route('room-categories.index'));

        $this->assertDatabaseMissing('room_categories', ['id' => $category->id]);
    }

    public function test_store_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $countBefore = RoomCategory::count();

        $this->post(route('room-categories.store'), [
            'name' => 'No Token',
            'price' => 10,
        ])->assertStatus(419);

        $this->assertSame($countBefore, RoomCategory::count());
    }

    public function test_update_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $category = RoomCategory::create(['name' => 'Temp', 'price' => 50]);

        $this->put(route('room-categories.update', $category), [
            'name' => 'Changed',
            'price' => 999,
        ])->assertStatus(419);

        $category->refresh();
        $this->assertSame('Temp', $category->name);
    }

    public function test_destroy_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $category = RoomCategory::create(['name' => 'Temp', 'price' => 50]);

        $this->delete(route('room-categories.destroy', $category))->assertStatus(419);

        $this->assertDatabaseHas('room_categories', ['id' => $category->id]);
    }
}
