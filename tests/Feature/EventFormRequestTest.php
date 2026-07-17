<?php

use App\Models\User;
use App\Models\Kategori;
use App\Http\Requests\EventFormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;

beforeEach(function () {
    // Register the test route
    Route::post('/test-event-request', function (EventFormRequest $request) {
        return response()->json(['success' => true]);
    });

    // Create Admin User bypassing mass assignment protection
    $this->adminUser = new User();
    $this->adminUser->name = 'Admin User';
    $this->adminUser->email = 'admin@example.com';
    $this->adminUser->password = bcrypt('password');
    $this->adminUser->role = 'admin';
    $this->adminUser->save();

    // Create Regular User bypassing mass assignment protection
    $this->regularUser = new User();
    $this->regularUser->name = 'Regular User';
    $this->regularUser->email = 'user@example.com';
    $this->regularUser->password = bcrypt('password');
    $this->regularUser->role = 'user';
    $this->regularUser->save();

    $this->kategori = Kategori::create([
        'nama' => 'Music',
    ]);
});

test('only admin user can authorize the request', function () {
    // 1. Unauthenticated guest
    $response = $this->postJson('/test-event-request', []);
    $response->assertStatus(403);

    // 2. Regular user (role = 'user')
    $response = $this->actingAs($this->regularUser)->postJson('/test-event-request', []);
    $response->assertStatus(403);

    // 3. Admin user (role = 'admin') -> gets validation error (422) instead of auth error (403)
    $response = $this->actingAs($this->adminUser)->postJson('/test-event-request', []);
    $response->assertStatus(422);
});

test('validation rules pass with correct data', function () {
    $validData = [
        'judul' => 'Konser Musik',
        'deskripsi' => 'Konser musik spektakuler tahun ini.',
        'lokasi' => 'Stadion Gelora Bung Karno',
        'kategori_id' => $this->kategori->id,
        'tanggal_waktu' => Carbon::now()->addDays(5)->format('Y-m-d H:i:s'),
        'tikets' => [
            [
                'tipe' => 'reguler',
                'harga' => 50000,
                'stok' => 100,
            ],
            [
                'tipe' => 'premium',
                'harga' => 150000,
                'stok' => 20,
            ],
        ],
    ];

    $response = $this->actingAs($this->adminUser)->postJson('/test-event-request', $validData);
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('validation rules fail with incorrect data and return custom messages', function () {
    $invalidData = [
        'judul' => '', // required
        'deskripsi' => '', // required
        'lokasi' => '', // required
        'kategori_id' => 9999, // exists check fails
        'tanggal_waktu' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'), // after:now check fails
        'tikets' => [], // min:1 check fails and triggers required validation
    ];

    $response = $this->actingAs($this->adminUser)->postJson('/test-event-request', $invalidData);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'judul' => 'Judul event wajib diisi.',
        'deskripsi' => 'Deskripsi event wajib diisi.',
        'lokasi' => 'Lokasi event wajib diisi.',
        'kategori_id' => 'Kategori yang dipilih tidak valid.',
        'tanggal_waktu' => 'Tanggal dan waktu event harus setelah waktu sekarang.',
        'tikets' => 'Tiket event wajib diisi.',
    ]);
});

test('nested ticket validation rules and messages', function () {
    $invalidTicketsData = [
        'judul' => 'Konser Musik',
        'deskripsi' => 'Konser musik spektakuler tahun ini.',
        'lokasi' => 'Stadion Gelora Bung Karno',
        'kategori_id' => $this->kategori->id,
        'tanggal_waktu' => Carbon::now()->addDays(5)->format('Y-m-d H:i:s'),
        'tikets' => [
            [
                'tipe' => 'vip', // invalid type
                'harga' => -100, // min:0 fails
                'stok' => -5, // min:0 fails
            ]
        ],
    ];

    $response = $this->actingAs($this->adminUser)->postJson('/test-event-request', $invalidTicketsData);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'tikets.0.tipe' => 'Tipe tiket harus reguler atau premium.',
        'tikets.0.harga' => 'Harga tiket tidak boleh kurang dari 0.',
        'tikets.0.stok' => 'Stok tiket tidak boleh kurang dari 0.',
    ]);
});
