<?php

use App\Models\User;
use App\Models\Kategori;
use App\Models\Event;
use App\Models\Tiket;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->kategori = Kategori::create([
        'nama' => 'Test Kategori',
    ]);
});

test('event fillable fields are correct', function () {
    $event = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Event Title',
        'deskripsi' => 'Event Description',
        'lokasi' => 'Event Location',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => Carbon::now()->addDay(),
    ]);

    expect($event->judul)->toBe('Event Title')
        ->and($event->user_id)->toBe($this->user->id)
        ->and($event->kategori_id)->toBe($this->kategori->id);
});

test('event relationships work', function () {
    $event = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Event Title',
        'deskripsi' => 'Event Description',
        'lokasi' => 'Event Location',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => Carbon::now()->addDay(),
    ]);

    // Test Kategori relationship
    expect($event->kategori->id)->toBe($this->kategori->id);

    // Test User relationship
    expect($event->user->id)->toBe($this->user->id);

    // Test Tikets relationship
    $tiket = Tiket::create([
        'event_id' => $event->id,
        'tipe' => 'premium',
        'harga' => 100000,
        'stok' => 50,
    ]);
    expect($event->tikets)->toHaveCount(1)
        ->and($event->tikets->first()->id)->toBe($tiket->id);

    // Test Orders relationship
    $order = new Order();
    $order->user_id = $this->user->id;
    $order->event_id = $event->id;
    $order->order_date = Carbon::now();
    $order->total_harga = 100000;
    $order->save();

    expect($event->orders)->toHaveCount(1)
        ->and($event->orders->first()->id)->toBe($order->id);
});

test('event status attribute and scopes', function () {
    // 1. Upcoming event
    $upcomingEvent = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Upcoming Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => Carbon::now()->addHours(2),
    ]);

    // 2. Ongoing event (started 1 hour ago, within 3 hours)
    $ongoingEvent = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Ongoing Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => Carbon::now()->subHours(1),
    ]);

    // 3. Completed event (started 4 hours ago)
    $completedEvent = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Completed Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => Carbon::now()->subHours(4),
    ]);

    // Test status attribute
    expect($upcomingEvent->status)->toBe('Upcoming')
        ->and($ongoingEvent->status)->toBe('Ongoing')
        ->and($completedEvent->status)->toBe('Completed');

    // Test query scopes
    expect(Event::upcoming()->get()->pluck('id'))->toContain($upcomingEvent->id)
        ->and(Event::upcoming()->get()->pluck('id'))->not->toContain($ongoingEvent->id)
        ->and(Event::upcoming()->get()->pluck('id'))->not->toContain($completedEvent->id);

    expect(Event::ongoing()->get()->pluck('id'))->toContain($ongoingEvent->id)
        ->and(Event::ongoing()->get()->pluck('id'))->not->toContain($upcomingEvent->id)
        ->and(Event::ongoing()->get()->pluck('id'))->not->toContain($completedEvent->id);

    expect(Event::completed()->get()->pluck('id'))->toContain($completedEvent->id)
        ->and(Event::completed()->get()->pluck('id'))->not->toContain($upcomingEvent->id)
        ->and(Event::completed()->get()->pluck('id'))->not->toContain($ongoingEvent->id);
});

test('event hasSales helper works', function () {
    $event = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Event Title',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => Carbon::now()->addDay(),
    ]);

    expect($event->hasSales())->toBeFalse();

    $order = new Order();
    $order->user_id = $this->user->id;
    $order->event_id = $event->id;
    $order->order_date = Carbon::now();
    $order->total_harga = 50000;
    $order->save();

    expect($event->hasSales())->toBeTrue();
});

test('event image URL accessor works', function () {
    Storage::fake('public');

    // Case 1: Valid URL
    $event1 = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Event 1',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'https://example.com/some-image.jpg',
        'tanggal_waktu' => Carbon::now()->addDay(),
    ]);
    expect($event1->image_url)->toBe('https://example.com/some-image.jpg');

    // Case 2: File exists in storage
    Storage::disk('public')->put('my-event.jpg', 'fake contents');
    
    $event2 = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Event 2',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'my-event.jpg',
        'tanggal_waktu' => Carbon::now()->addDay(),
    ]);
    
    expect($event2->image_url)->toBe(asset('storage/my-event.jpg'));

    // Case 3: Fallback 'konser.jpg'
    $event3 = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Event 3',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'non-existent.jpg',
        'tanggal_waktu' => Carbon::now()->addDay(),
    ]);
    expect($event3->image_url)->toBe(asset('storage/konser.jpg'));
});

test('event stores status history correctly', function () {
    // 1. Creating event should log initial status
    $event = Event::create([
        'user_id' => $this->user->id,
        'kategori_id' => $this->kategori->id,
        'judul' => 'Status History Test',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'image.jpg',
        'tanggal_waktu' => Carbon::now()->addDays(2), // Upcoming
    ]);

    expect($event->statusHistories)->toHaveCount(1);
    expect($event->statusHistories->first()->status_sebelum)->toBe('-');
    expect($event->statusHistories->first()->status_sesudah)->toBe('Upcoming');

    // 2. Updating time to a past date should transition to Completed and log it
    $event->update([
        'tanggal_waktu' => Carbon::now()->subDays(2), // Completed
    ]);

    $event->refresh();
    expect($event->statusHistories)->toHaveCount(2);
    
    $latestHistory = $event->statusHistories()->orderBy('id', 'desc')->first();
    expect($latestHistory->status_sebelum)->toBe('Upcoming');
    expect($latestHistory->status_sesudah)->toBe('Completed');
});
