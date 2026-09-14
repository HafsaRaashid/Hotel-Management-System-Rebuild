<?php

namespace Tests\Feature;

use App\Models\RoomCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-006 (BL-009). AC-N references are to
 * .specclaw/changes/004-public-marketing-site/spec.md.
 */
class MarketingSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_live_category_pricing(): void
    {
        $response = $this->get(route('marketing.home'));

        $response->assertStatus(200);

        foreach (RoomCategory::all() as $category) {
            $response->assertSee($category->name);
            $response->assertSee((string) $category->price);
        }
    }

    public function test_room_details_page_shows_live_category_pricing(): void
    {
        $response = $this->get(route('marketing.room'));

        $response->assertStatus(200);

        foreach (RoomCategory::all() as $category) {
            $response->assertSee($category->name);
            $response->assertSee((string) $category->price);
        }
    }

    public function test_home_page_reflects_a_price_change(): void
    {
        $category = RoomCategory::where('name', 'Single Room')->firstOrFail();
        $category->update(['price' => 12345]);

        $response = $this->get(route('marketing.home'));

        $response->assertSee('12345');
    }

    public function test_services_page_answers(): void
    {
        $this->get(route('marketing.services'))->assertStatus(200);
    }

    public function test_food_page_answers(): void
    {
        $this->get(route('marketing.food'))->assertStatus(200);
    }

    public function test_all_four_pages_show_the_nav_links(): void
    {
        foreach ([route('marketing.home'), route('marketing.room'), route('marketing.services'), route('marketing.food')] as $url) {
            $response = $this->get($url);

            $response->assertSee('Home');
            $response->assertSee('Room');
            $response->assertSee('Services');
            $response->assertSee('Foods');
            $response->assertSee('Book Now');
        }
    }

    /**
     * AC-15 (.specclaw/changes/005-booking-stay-lifecycle/spec.md) - "Book
     * Now" used to be a plain, unrouted /book string (BL-015 wasn't built
     * yet); now that it is, the link must resolve to the real booking form,
     * not 404.
     */
    public function test_book_now_link_resolves_to_the_booking_form(): void
    {
        $response = $this->get(route('marketing.home'));

        $response->assertSee('href="'.route('booking.create').'"', false);

        $this->get(route('booking.create'))->assertStatus(200);
    }
}
