<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlaceSearchTest extends TestCase
{
    public function test_place_search_merges_local_and_remote_results(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                [
                    'lat' => '48.8566',
                    'lon' => '2.3522',
                    'name' => 'Paris',
                    'address' => [
                        'city' => 'Paris',
                        'country' => 'France',
                    ],
                ],
            ], 200),
        ]);

        $this->getJson('/places/search?q=paris')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Paris',
                'country' => 'France',
            ]);
    }

    public function test_short_queries_do_not_hit_nominatim(): void
    {
        Http::fake();

        $this->getJson('/places/search?q=p')
            ->assertOk()
            ->assertExactJson([]);

        Http::assertNothingSent();
    }
}
