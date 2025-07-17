<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Number;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingAPITest extends TestCase
{

    use RefreshDatabase;

    function withAuthentication(){
        Sanctum::actingAs(
            User::factory()->create()
        );
    }

    public function test_booking_listing_requires_authentication(){
        $this->withAuthentication();
    
        $this
            ->getJson('/api/bookings')
            ->assertStatus(200);
    }

    public function test_booking_listing_reject_unauthenticated(){
        // without token/user    
        $this->getJson('/api/bookings')
            ->assertStatus(401);
    }


    /**
     * A basic feature test example.
     */
    public function test_booking_can_list(): void
    {
        $this->withAuthentication();

        $response = $this->getJson('/api/bookings');
        $response->assertJson(fn (AssertableJson $json) => $json->hasAll(['data', 'meta', 'links']));
        $response->assertStatus(200);
    }

    public function test_booking_can_list_with_data(): void
    {
        $this->withAuthentication();

        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        $booking = Booking::factory()->create([
            'customer_id' => fn() => $customer->id,
            'service_id' => fn() => $service->id,
        ]);

        $this->getJson('/api/bookings')
             ->assertJson(fn (AssertableJson $json) =>
                $json->has("meta")
                    ->has("links")
                    ->has('data.0', fn (AssertableJson $json) =>
                        $json->where('id', $booking->id)
                            ->where('customer_name', $customer->full_name)
                            ->where('service_name', $service->name)
                            ->where("starts_at", $booking->starts_at->format("Y-m-d"))
                            ->where("ends_at", $booking->ends_at->format("Y-m-d"))
                            ->etc()
                    )
            )->assertStatus(200);
    }

    public function test_booking_conditionaly_show_total_price(): void
    {
        $this->withAuthentication();

        Booking::factory()->create([
            'status' => BookingStatus::PENDING, // which is not confirmed
            'total_price_cents' => 10 * 100,
        ]);

        Booking::factory()->create([
            'status' => BookingStatus::CONFIRMED, // which is not confirmed
            'total_price_cents' => 10 * 100,
        ]);

        $this->getJson('/api/bookings')
             ->assertJson(fn (AssertableJson $json) =>
                $json->has("meta")
                    ->has("links")
                    ->has('data.0', fn (AssertableJson $json) =>
                            $json->missing('total_price')
                                ->etc()
                    )
                    ->has('data.1', fn (AssertableJson $json) =>
                            $json->has('total_price')
                                ->where("total_price", Number::currency(10, "USD"))
                                ->etc()
                    )
            )->assertStatus(200);
    }

    public function test_booking_can_list_without_data(): void
    {
        $this->withAuthentication();

        $this->getJson('/api/bookings')
            ->assertJson(fn (AssertableJson $json) =>
                $json->has("meta")
                    ->has("links")
                    ->has('data', 0)
            )->assertStatus(200);
    }

    public function test_bookings_can_be_filtered_with_service_id(){
        $this->withAuthentication();

        // make sure that it will not exceed the paginated count of data in the list
        Booking::factory()->count(10)->create();

        $service = Service::first();
        $numberOfBookings = Booking::where("service_id", $service->id)->count();

        $this->getJson('/api/bookings?service_id=' . $service->id)
             ->assertJson(fn (AssertableJson $json) =>
                $json->has("meta")
                    ->has("links")
                    ->has('data', $numberOfBookings)
            )->assertStatus(200);
    }

    public function test_bookings_can_be_filtered_with_status(){
        $this->withAuthentication();

        // make sure that it will not exceed the paginated count of data in the list
        Booking::factory()->count(10)->create([
            'status' => fake()->randomElement(BookingStatus::values()),
        ]);

        $randomStatus = BookingStatus::cases()[array_rand(BookingStatus::cases())]->value;
        $numberOfBookings = Booking::where("status",$randomStatus)->count();

        $this->getJson('/api/bookings?status=' . $randomStatus)
             ->assertJson(fn (AssertableJson $json) =>
                $json->has("meta")
                    ->has("links")
                    ->has('data', $numberOfBookings)
            )->assertStatus(200);
    }

    public function test_bookings_can_be_filtered_with_dates(){
        $this->withAuthentication();

        $now = Carbon::now();

        // make sure that it will not exceed the paginated count of data in the list
        Booking::factory()->count(3)->create([
            'status' => fake()->randomElement(BookingStatus::values()),
            'starts_at' => $now
        ]);

        // make sure that it will not exceed the paginated count of data in the list
        Booking::factory()->count(5)->create([
            'status' => fake()->randomElement(BookingStatus::values()),
            'starts_at' => $now->copy()->addDays(1)
        ]);

        // Filtering the today as starting day (with date_from but without date_to)
        $this->getJson('/api/bookings?date_from=' . $now->format("Y-m-d"))
             ->assertJson(fn (AssertableJson $json) =>
                $json->has("meta")
                    ->has("links")
                    ->has('data', 8) // its should be 8 since its been created 3 items for today and 5 for tomorrow
            )->assertStatus(200);

        // Filtering bookings within yesterday and tomorrow (with date_from and date_to)
        $numberOfBookings = Booking::whereDate("starts_at", ">=", $now->copy()->subDay())->whereDate("ends_at", "<=", $now)->count();
        $this->getJson('/api/bookings?date_from=' . $now->copy()->subDay()->format("Y-m-d") . '&date_to=' . $now->format("Y-m-d"))
             ->assertJson(fn (AssertableJson $json) =>
                $json->has("meta")
                    ->has("links")
                    ->has('data', $numberOfBookings) 
            )->assertStatus(200);        
    }
}
