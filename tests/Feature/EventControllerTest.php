<?php

use App\Models\User;
use App\Models\Kategori;
use App\Models\Event;
use App\Models\Tiket;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    // Admin user
    $this->adminUser = new User();
    $this->adminUser->name = 'Admin User';
    $this->adminUser->email = 'admin@example.com';
    $this->adminUser->password = bcrypt('password');
    $this->adminUser->role = 'admin';
    $this->adminUser->save();

    // Regular user
    $this->regularUser = new User();
    $this->regularUser->name = 'Regular User';
    $this->regularUser->email = 'user@example.com';
    $this->regularUser->password = bcrypt('password');
    $this->regularUser->role = 'user';
    $this->regularUser->save();

    $this->kategori1 = Kategori::create(['nama' => 'Music']);
    $this->kategori2 = Kategori::create(['nama' => 'Sport']);
});

test('public event show page loads with related events', function () {
    $event = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Main Concert',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    // Related event (same category, in the future)
    $related = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Related Concert',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(3),
    ]);

    // Unrelated event (different category)
    $unrelated = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori2->id,
        'judul' => 'Unrelated Sport',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(3),
    ]);

    $response = $this->get('/events/' . $event->id);
    $response->assertStatus(200);
    $response->assertViewHas('event', function ($viewEvent) use ($event) {
        return $viewEvent->id === $event->id;
    });
    $response->assertViewHas('relatedEvents');
    
    $relatedEvents = $response->viewData('relatedEvents');
    expect($relatedEvents->pluck('id'))->toContain($related->id)
        ->and($relatedEvents->pluck('id'))->not->toContain($unrelated->id);
});

test('admin events index filters, searches, and sorts', function () {
    $event1 = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Rock Fest',
        'deskripsi' => 'Desc',
        'lokasi' => 'Jakarta',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(5),
    ]);

    $event2 = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori2->id,
        'judul' => 'Soccer Match',
        'deskripsi' => 'Desc',
        'lokasi' => 'Bandung',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    // 1. Unauthenticated gets redirected
    $this->get('/admin/events')->assertRedirect('/login');

    // 2. Admin user can access and filter
    $response = $this->actingAs($this->adminUser)->get('/admin/events?kategori_id=' . $this->kategori1->id);
    $response->assertStatus(200);
    $events = $response->viewData('events');
    expect($events->pluck('id'))->toContain($event1->id)
        ->and($events->pluck('id'))->not->toContain($event2->id);

    // 3. Search by judul/lokasi
    $response = $this->actingAs($this->adminUser)->get('/admin/events?search=Bandung');
    $events = $response->viewData('events');
    expect($events->pluck('id'))->toContain($event2->id)
        ->and($events->pluck('id'))->not->toContain($event1->id);

    // 4. Sort by tanggal_waktu asc
    $response = $this->actingAs($this->adminUser)->get('/admin/events?sort=asc');
    $events = $response->viewData('events');
    expect($events->first()->id)->toBe($event2->id); // day 2 before day 5

    // 5. Sort by tanggal_waktu desc
    $response = $this->actingAs($this->adminUser)->get('/admin/events?sort=desc');
    $events = $response->viewData('events');
    expect($events->first()->id)->toBe($event1->id);
});

test('admin can see create form', function () {
    $response = $this->actingAs($this->adminUser)->get('/admin/events/create');
    $response->assertStatus(200);
    $response->assertViewHas('categories');
});

test('admin can store event and tickets with image upload', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('concert.jpg');

    $postData = [
        'judul' => 'Jazz Night',
        'deskripsi' => 'Beautiful jazz evening.',
        'lokasi' => 'Bandung Art Center',
        'kategori_id' => $this->kategori1->id,
        'tanggal_waktu' => now()->addDays(4)->format('Y-m-d H:i:s'),
        'gambar' => $file,
        'tikets' => [
            [
                'tipe' => 'reguler',
                'harga' => 50000,
                'stok' => 100,
            ],
            [
                'tipe' => 'premium',
                'harga' => 120000,
                'stok' => 30,
            ],
        ],
    ];

    $response = $this->actingAs($this->adminUser)->post('/admin/events', $postData);
    $response->assertRedirect('/admin/events');

    $event = Event::where('judul', 'Jazz Night')->first();
    expect($event)->not->toBeNull();
    
    // Check image stored
    expect($event->gambar)->not->toBe('konser.jpg');
    Storage::disk('public')->assertExists($event->gambar);

    // Check tickets created
    expect($event->tikets)->toHaveCount(2);
    expect($event->tikets->where('tipe', 'reguler')->first()->harga)->toEqual(50000);
});

test('admin can store event using fallback image', function () {
    $postData = [
        'judul' => 'Classical Solo',
        'deskripsi' => 'Solo piano.',
        'lokasi' => 'Aula Barat ITB',
        'kategori_id' => $this->kategori1->id,
        'tanggal_waktu' => now()->addDays(4)->format('Y-m-d H:i:s'),
        'tikets' => [
            [
                'tipe' => 'reguler',
                'harga' => 30000,
                'stok' => 50,
            ],
        ],
    ];

    $response = $this->actingAs($this->adminUser)->post('/admin/events', $postData);
    $response->assertRedirect('/admin/events');

    $event = Event::where('judul', 'Classical Solo')->first();
    expect($event->gambar)->toBe('konser.jpg');
});

test('admin can see edit form', function () {
    $event = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Indie Fest',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $response = $this->actingAs($this->adminUser)->get('/admin/events/' . $event->id . '/edit');
    $response->assertStatus(200);
    $response->assertViewHas('event');
    $response->assertViewHas('hasSales', false);
});

test('admin can update event details and tickets', function () {
    Storage::fake('public');

    $event = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Pop Concert',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'old-image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $tiket1 = $event->tikets()->create([
        'tipe' => 'reguler',
        'harga' => 30000,
        'stok' => 50,
    ]);

    $tiket2 = $event->tikets()->create([
        'tipe' => 'premium',
        'harga' => 90000,
        'stok' => 10,
    ]);

    $updateData = [
        'judul' => 'Updated Pop Concert',
        'deskripsi' => 'Updated Desc',
        'lokasi' => 'New Loc',
        'kategori_id' => $this->kategori2->id,
        'tanggal_waktu' => now()->addDays(3)->format('Y-m-d H:i:s'),
        'tikets' => [
            [
                'id' => $tiket1->id,
                'tipe' => 'reguler',
                'harga' => 35000,
                'stok' => 45,
            ],
            [
                'tipe' => 'premium', // new ticket
                'harga' => 100000,
                'stok' => 15,
            ]
        ]
    ];

    $response = $this->actingAs($this->adminUser)->put('/admin/events/' . $event->id, $updateData);
    $response->assertRedirect('/admin/events');

    $event->refresh();
    expect($event->judul)->toBe('Updated Pop Concert');
    expect($event->kategori_id)->toBe($this->kategori2->id);

    // Ticket 2 should be deleted (since no sales)
    expect($event->tikets)->toHaveCount(2);
    expect($event->tikets->pluck('id'))->not->toContain($tiket2->id);
    expect($event->tikets->where('id', $tiket1->id)->first()->harga)->toEqual(35000);
});

test('admin cannot update date if event has sales', function () {
    $event = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Sold Out Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    // Create an order to simulate sales
    $order = new Order();
    $order->user_id = $this->adminUser->id;
    $order->event_id = $event->id;
    $order->order_date = Carbon::now();
    $order->total_harga = 100000;
    $order->save();

    $updateData = [
        'judul' => 'Sold Out Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'kategori_id' => $this->kategori1->id,
        'tanggal_waktu' => now()->addDays(3)->format('Y-m-d H:i:s'), // changed date
        'tikets' => [
            [
                'tipe' => 'reguler',
                'harga' => 50000,
                'stok' => 10,
            ]
        ]
    ];

    $response = $this->actingAs($this->adminUser)->put('/admin/events/' . $event->id, $updateData);
    $response->assertStatus(302);
    $response->assertSessionHasErrors('tanggal_waktu');
});

test('admin cannot destroy event if it has sales', function () {
    $event = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Sold Out Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    // Create an order
    $order = new Order();
    $order->user_id = $this->adminUser->id;
    $order->event_id = $event->id;
    $order->order_date = Carbon::now();
    $order->total_harga = 100000;
    $order->save();

    $response = $this->actingAs($this->adminUser)->delete('/admin/events/' . $event->id);
    $response->assertRedirect('/admin/events');
    $response->assertSessionHas('error');

    // Event should still exist
    expect(Event::find($event->id))->not->toBeNull();
});

test('admin can destroy event if it has no sales', function () {
    $event = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Empty Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $response = $this->actingAs($this->adminUser)->delete('/admin/events/' . $event->id);
    $response->assertRedirect('/admin/events');
    $response->assertSessionHas('success');

    // Event should be deleted
    expect(Event::find($event->id))->toBeNull();
});

test('admin can clone event', function () {
    $event = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Original Concert',
        'deskripsi' => 'Original Desc',
        'lokasi' => 'Original Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $event->tikets()->create([
        'tipe' => 'reguler',
        'harga' => 50000,
        'stok' => 100,
    ]);

    $response = $this->actingAs($this->adminUser)->post("/admin/events/{$event->id}/clone");
    $response->assertRedirect('/admin/events');
    $response->assertSessionHas('success');

    $cloned = Event::where('judul', '[Copy] Original Concert')->first();
    expect($cloned)->not->toBeNull();
    expect($cloned->deskripsi)->toBe('Original Desc');
    expect($cloned->tikets)->toHaveCount(1);
    expect($cloned->tikets->first()->tipe)->toBe('reguler');
});

test('admin can bulk delete events', function () {
    $event1 = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Event One',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $event2 = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Event Two',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $response = $this->actingAs($this->adminUser)->delete('/admin/events/bulk-delete', [
        'ids' => [$event1->id, $event2->id]
    ]);
    $response->assertRedirect('/admin/events');
    $response->assertSessionHas('success');

    expect(Event::find($event1->id))->toBeNull();
    expect(Event::find($event2->id))->toBeNull();
});

test('admin bulk delete skips events with sales', function () {
    $eventNoSales = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'No Sales Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $eventWithSales = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'With Sales Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    // Create order to simulate sales
    $order = new Order();
    $order->user_id = $this->adminUser->id;
    $order->event_id = $eventWithSales->id;
    $order->order_date = Carbon::now();
    $order->total_harga = 50000;
    $order->save();

    $response = $this->actingAs($this->adminUser)->delete('/admin/events/bulk-delete', [
        'ids' => [$eventNoSales->id, $eventWithSales->id]
    ]);
    $response->assertRedirect('/admin/events');
    $response->assertSessionHas('success');

    expect(Event::find($eventNoSales->id))->toBeNull();
    expect(Event::find($eventWithSales->id))->not->toBeNull();
});

test('admin can store event with cropped image base64', function () {
    $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

    $data = [
        'judul' => 'Event with Cropped Image',
        'deskripsi' => 'Event description here.',
        'lokasi' => 'Stadion GBK',
        'kategori_id' => $this->kategori1->id,
        'tanggal_waktu' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'cropped_gambar' => $base64Image,
        'tikets' => [
            [
                'tipe' => 'reguler',
                'harga' => 150000,
                'stok' => 100,
            ]
        ]
    ];

    $response = $this->actingAs($this->adminUser)->post('/admin/events', $data);
    $response->assertRedirect('/admin/events');
    $response->assertSessionHas('success');

    $event = Event::where('judul', 'Event with Cropped Image')->first();
    expect($event)->not->toBeNull();
    expect($event->gambar)->not->toBeNull();
    expect($event->gambar)->not->toBe('konser.jpg');
    
    if ($event->gambar && Storage::disk('public')->exists($event->gambar)) {
        Storage::disk('public')->delete($event->gambar);
    }
});

test('admin can update event with cropped image base64', function () {
    $event = Event::create([
        'user_id' => $this->adminUser->id,
        'kategori_id' => $this->kategori1->id,
        'judul' => 'Event To Update Crop',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'old-image.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

    $data = [
        'judul' => 'Event Updated Crop',
        'deskripsi' => 'Updated Desc',
        'lokasi' => 'Updated Loc',
        'kategori_id' => $this->kategori1->id,
        'tanggal_waktu' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'cropped_gambar' => $base64Image,
        'tikets' => [
            [
                'tipe' => 'reguler',
                'harga' => 200000,
                'stok' => 50,
            ]
        ]
    ];

    $response = $this->actingAs($this->adminUser)->put("/admin/events/{$event->id}", $data);
    $response->assertRedirect('/admin/events');
    $response->assertSessionHas('success');

    $event->refresh();
    expect($event->judul)->toBe('Event Updated Crop');
    expect($event->gambar)->not->toBe('old-image.jpg');
    expect($event->gambar)->not->toBeNull();

    if ($event->gambar && Storage::disk('public')->exists($event->gambar)) {
        Storage::disk('public')->delete($event->gambar);
    }
});

test('admin can export events to excel xlsx', function () {
    $response = $this->actingAs($this->adminUser)->get('/admin/events/export');
    
    $response->assertStatus(200);
    $contentType = $response->headers->get('Content-Type');
    expect(
        str_contains($contentType, 'spreadsheet') || 
        str_contains($contentType, 'excel') || 
        str_contains($contentType, 'openxmlformats')
    )->toBeTrue();
});
