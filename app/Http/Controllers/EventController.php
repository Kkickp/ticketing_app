<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Kategori;
use App\Http\Requests\EventFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Exports\EventsExport;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request)
    {
        // Build paginated query
        $query = Event::with(['kategori', 'tikets.detailOrders']);

        // Filter by kategori_id
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Search by judul atau lokasi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%')
                  ->orWhere('lokasi', 'like', '%' . $search . '%');
            });
        }

        // Sort by tanggal_waktu (asc/desc)
        $sort = $request->query('sort', 'asc');
        if (!in_array(strtolower($sort), ['asc', 'desc'])) {
            $sort = 'asc';
        }
        $query->orderBy('tanggal_waktu', $sort);

        // Paginate dengan 10 items per page
        $events = $query->paginate(10);

        return view('pages.admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        $categories = Kategori::all();
        return view('pages.admin.events.create', compact('categories'));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(EventFormRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        // Handle image upload / crop using Intervention Image v3
        if ($request->filled('cropped_gambar')) {
            $base64Image = $request->input('cropped_gambar');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $type = strtolower($type[1]);
                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    try {
                        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                        $image = $manager->read($base64Image);
                        
                        // Scale/Resize image to standard max width of 800px for web performance
                        $image->scale(width: 800);
                        
                        $fileName = 'events/' . uniqid() . '.' . $type;
                        $encoded = ($type === 'png') ? $image->toPng() : $image->toJpeg();
                        Storage::disk('public')->put($fileName, $encoded->toString());
                        
                        $data['gambar'] = $fileName;
                    } catch (\Exception $e) {
                        // Fallback decoding if driver is not configured
                        $imageDecoded = base64_decode(substr($base64Image, strpos($base64Image, ',') + 1));
                        $fileName = 'events/' . uniqid() . '.' . $type;
                        Storage::disk('public')->put($fileName, $imageDecoded);
                        $data['gambar'] = $fileName;
                    }
                }
            }
        } elseif ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('events', 'public');
        } else {
            $data['gambar'] = 'konser.jpg';
        }

        // Create event
        $event = Event::create($data);

        // Create tickets
        foreach ($request->input('tikets', []) as $tiketData) {
            $event->tikets()->create([
                'tipe' => $tiketData['tipe'],
                'harga' => $tiketData['harga'],
                'stok' => $tiketData['stok'],
            ]);
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        // Load the event with its relationships
        $event->load(['kategori', 'tikets']);

        // Related events: same kategori, tanggal_waktu > now, max 4 events, excluding current event
        $relatedEvents = Event::where('kategori_id', $event->kategori_id)
            ->where('id', '!=', $event->id)
            ->where('tanggal_waktu', '>', now())
            ->take(4)
            ->get();

        return view('event.show', [
            'event' => $event,
            'relatedEvents' => $relatedEvents,
        ]);
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $event->load('tikets');
        $categories = Kategori::all();
        $hasSales = $event->hasSales();

        return view('pages.admin.events.edit', compact('event', 'categories', 'hasSales'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(EventFormRequest $request, Event $event)
    {
        $data = $request->validated();

        // Jika event sudah terjual (hasSales()): Tampilkan error jika tanggal_waktu berubah
        if ($event->hasSales()) {
            $originalDate = Carbon::parse($event->tanggal_waktu);
            $newDate = Carbon::parse($request->input('tanggal_waktu'));
            
            if ($originalDate->ne($newDate)) {
                throw ValidationException::withMessages([
                    'tanggal_waktu' => 'Tanggal dan waktu event tidak dapat diubah karena tiket sudah mulai terjual.',
                ]);
            }
        }

        // Handle image update (hapus old image jika ada) / crop using Intervention Image v3
        if ($request->filled('cropped_gambar')) {
            $base64Image = $request->input('cropped_gambar');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $type = strtolower($type[1]);
                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    try {
                        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                        $image = $manager->read($base64Image);
                        
                        // Scale/Resize image to standard max width of 800px for web performance
                        $image->scale(width: 800);
                        
                        if ($event->gambar && $event->gambar !== 'konser.jpg') {
                            Storage::disk('public')->delete($event->gambar);
                        }
                        
                        $fileName = 'events/' . uniqid() . '.' . $type;
                        $encoded = ($type === 'png') ? $image->toPng() : $image->toJpeg();
                        Storage::disk('public')->put($fileName, $encoded->toString());
                        
                        $data['gambar'] = $fileName;
                    } catch (\Exception $e) {
                        // Fallback decoding if driver is not configured
                        if ($event->gambar && $event->gambar !== 'konser.jpg') {
                            Storage::disk('public')->delete($event->gambar);
                        }
                        $imageDecoded = base64_decode(substr($base64Image, strpos($base64Image, ',') + 1));
                        $fileName = 'events/' . uniqid() . '.' . $type;
                        Storage::disk('public')->put($fileName, $imageDecoded);
                        $data['gambar'] = $fileName;
                    }
                }
            }
        } elseif ($request->hasFile('gambar')) {
            if ($event->gambar && $event->gambar !== 'konser.jpg') {
                Storage::disk('public')->delete($event->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('events', 'public');
        }

        // Update event
        $event->update($data);

        // Handle tickets
        $inputTickets = $request->input('tikets', []);
        $inputTicketIds = collect($inputTickets)->pluck('id')->filter()->toArray();

        // Delete removed tickets (hanya jika belum ada penjualan)
        if (!$event->hasSales()) {
            $event->tikets()->whereNotIn('id', $inputTicketIds)->delete();
        }

        // Update existing / Create new tickets
        foreach ($inputTickets as $ticketData) {
            if (!empty($ticketData['id'])) {
                $event->tikets()->where('id', $ticketData['id'])->update([
                    'tipe' => $ticketData['tipe'],
                    'harga' => $ticketData['harga'],
                    'stok' => $ticketData['stok'],
                ]);
            } else {
                $event->tikets()->create([
                    'tipe' => $ticketData['tipe'],
                    'harga' => $ticketData['harga'],
                    'stok' => $ticketData['stok'],
                ]);
            }
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil diperbarui.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        // 1. Cek apakah event memiliki penjualan
        if ($event->hasSales()) {
            return redirect()->route('admin.events.index')
                ->with('error', 'Event tidak dapat dihapus karena sudah memiliki penjualan tiket.');
        }

        // 2. Hapus image dari storage (jika bukan default)
        if ($event->gambar && $event->gambar !== 'konser.jpg') {
            Storage::disk('public')->delete($event->gambar);
        }

        // 3. Delete event
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    /**
     * Replicate/clone the specified event and its tickets.
     */
    public function clone(Event $event)
    {
        $clone = $event->replicate();
        
        $clone->judul = '[Copy] ' . $event->judul;
        
        if ($clone->tanggal_waktu < now()) {
            $clone->tanggal_waktu = now()->addDays(7);
        }
        
        $clone->save();
        
        foreach ($event->tikets as $tiket) {
            $newTiket = $tiket->replicate();
            $newTiket->event_id = $clone->id;
            $newTiket->save();
        }
        
        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil diduplikat.');
    }

    /**
     * Remove multiple events from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('admin.events.index')
                ->with('error', 'Tidak ada event yang dipilih.');
        }
        
        $events = Event::whereIn('id', $ids)->get();
        $deletedCount = 0;
        $skippedCount = 0;
        
        foreach ($events as $event) {
            if ($event->hasSales()) {
                $skippedCount++;
                continue;
            }
            
            if ($event->gambar && $event->gambar !== 'konser.jpg') {
                Storage::disk('public')->delete($event->gambar);
            }
            
            $event->delete();
            $deletedCount++;
        }
        
        if ($skippedCount > 0) {
            return redirect()->route('admin.events.index')
                ->with('success', "$deletedCount event berhasil dihapus. $skippedCount event dilewati karena sudah memiliki penjualan.");
        }
        
        return redirect()->route('admin.events.index')
            ->with('success', 'Semua event yang terpilih berhasil dihapus.');
    }

    /**
     * Export events to Excel using maatwebsite/excel package.
     */
    public function exportExcel()
    {
        return Excel::download(new EventsExport, 'events_export_' . date('Ymd_His') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
