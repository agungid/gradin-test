<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_couriers_sorted_by_name_by_default(): void
    {
        Courier::factory()->create(['name' => 'Zaki Pratama']);
        Courier::factory()->create(['name' => 'Andi Wijaya']);
        Courier::factory()->create(['name' => 'Budi Santoso']);

        $response = $this->getJson('/api/couriers?per_page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
                'last_page',
            ])
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('total', 3)
            ->assertJsonCount(2, 'data');

        $names = array_column($response->json('data'), 'name');
        $this->assertSame(['Andi Wijaya', 'Budi Santoso'], $names);
    }

    public function test_index_can_override_default_sort_by_registered_at(): void
    {
        Courier::factory()->create([
            'name' => 'Alpha',
            'registered_at' => '2026-01-10 10:00:00',
        ]);
        Courier::factory()->create([
            'name' => 'Beta',
            'registered_at' => '2026-03-15 10:00:00',
        ]);
        Courier::factory()->create([
            'name' => 'Charlie',
            'registered_at' => '2026-02-20 10:00:00',
        ]);

        // Default direction for registered_at is desc (newest first)
        $descResponse = $this->getJson('/api/couriers?sort_by=registered_at');
        $descResponse->assertOk();
        $this->assertSame(
            ['Beta', 'Charlie', 'Alpha'],
            array_column($descResponse->json('data'), 'name')
        );

        // Explicit asc direction (oldest first)
        $ascResponse = $this->getJson('/api/couriers?sort_by=registered_at&sort_dir=asc');
        $ascResponse->assertOk();
        $this->assertSame(
            ['Alpha', 'Charlie', 'Beta'],
            array_column($ascResponse->json('data'), 'name')
        );
    }

    public function test_index_search_matches_partial_multi_word_names(): void
    {
        $target = Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Budi Santoso']);
        Courier::factory()->create(['name' => 'Agung Nugroho']);

        $response = $this->getJson('/api/couriers?search=budi+agung');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $target->id)
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');
    }

    public function test_index_filters_by_multiple_levels(): void
    {
        Courier::factory()->create(['name' => 'Level 1', 'level' => 1]);
        $lvl2 = Courier::factory()->create(['name' => 'Level 2', 'level' => 2]);
        $lvl3 = Courier::factory()->create(['name' => 'Level 3', 'level' => 3]);
        Courier::factory()->create(['name' => 'Level 4', 'level' => 4]);
        Courier::factory()->create(['name' => 'Level 5', 'level' => 5]);

        $response = $this->getJson('/api/couriers?level=2,3');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = array_column($response->json('data'), 'id');
        $this->assertEqualsCanonicalizing([$lvl2->id, $lvl3->id], $ids);
    }

    public function test_show_returns_all_courier_data(): void
    {
        $courier = Courier::factory()->create([
            'code' => 'KUR-101',
            'name' => 'Budiono Hadi Agung',
            'email' => 'budiono@example.com',
            'phone' => '081234567890',
            'level' => 3,
            'vehicle_type' => 'motorcycle',
            'vehicle_plate' => 'B 1234 XYZ',
            'service_area' => 'Jakarta Selatan',
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.code', 'KUR-101')
            ->assertJsonPath('data.name', 'Budiono Hadi Agung')
            ->assertJsonPath('data.email', 'budiono@example.com')
            ->assertJsonPath('data.phone', '081234567890')
            ->assertJsonPath('data.level', 3)
            ->assertJsonPath('data.vehicle_type', 'motorcycle')
            ->assertJsonPath('data.vehicle_plate', 'B 1234 XYZ')
            ->assertJsonPath('data.service_area', 'Jakarta Selatan')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'name',
                    'email',
                    'phone',
                    'level',
                    'vehicle_type',
                    'vehicle_plate',
                    'service_area',
                    'status',
                    'registered_at',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_store_validates_input_and_persists_courier_to_database(): void
    {
        // Validation failure: missing required fields & invalid level/vehicle_type
        $invalidResponse = $this->postJson('/api/couriers', [
            'name' => 'A',
            'email' => 'not-an-email',
            'phone' => '123',
            'level' => 6,
            'vehicle_type' => 'spaceship',
        ]);

        $invalidResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'email', 'phone', 'level', 'vehicle_type']);

        // Valid payload
        $payload = [
            'code' => 'KUR-999',
            'name' => 'Siti Aminah',
            'email' => 'siti.aminah@example.com',
            'phone' => '081298765432',
            'level' => 4,
            'vehicle_type' => 'van',
            'vehicle_plate' => 'D 4567 ABC',
            'service_area' => 'Bandung',
            'status' => 'active',
            'registered_at' => '2026-11-01 08:30:00',
        ];

        $response = $this->postJson('/api/couriers', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.code', 'KUR-999')
            ->assertJsonPath('data.name', 'Siti Aminah')
            ->assertJsonPath('data.level', 4);

        $this->assertDatabaseHas('couriers', [
            'code' => 'KUR-999',
            'name' => 'Siti Aminah',
            'email' => 'siti.aminah@example.com',
            'phone' => '081298765432',
            'level' => 4,
            'vehicle_type' => 'van',
            'vehicle_plate' => 'D 4567 ABC',
            'service_area' => 'Bandung',
            'status' => 'active',
        ]);
    }

    public function test_update_validates_input_and_persists_changes_to_database(): void
    {
        $existing = Courier::factory()->create(['code' => 'KUR-001', 'email' => 'taken@example.com']);
        $courier = Courier::factory()->create([
            'code' => 'KUR-002',
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'phone' => '081111111111',
            'level' => 1,
            'vehicle_type' => 'motorcycle',
        ]);

        // Validation failure: duplicate code/email & out-of-range level
        $invalidResponse = $this->putJson("/api/couriers/{$courier->id}", [
            'code' => $existing->code,
            'email' => $existing->email,
            'level' => 0,
        ]);

        $invalidResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'email', 'level']);

        // Valid update (keeping own code, updating other fields)
        $response = $this->putJson("/api/couriers/{$courier->id}", [
            'code' => 'KUR-002',
            'name' => 'Updated Name',
            'level' => 5,
            'vehicle_type' => 'car',
            'service_area' => 'Surabaya',
            'status' => 'inactive',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.level', 5)
            ->assertJsonPath('data.vehicle_type', 'car')
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'code' => 'KUR-002',
            'name' => 'Updated Name',
            'level' => 5,
            'vehicle_type' => 'car',
            'service_area' => 'Surabaya',
            'status' => 'inactive',
        ]);
    }

    public function test_destroy_deletes_courier_from_database(): void
    {
        $courier = Courier::factory()->create();

        $response = $this->deleteJson("/api/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Courier deleted successfully.');

        $this->assertDatabaseMissing('couriers', [
            'id' => $courier->id,
        ]);
    }
}
