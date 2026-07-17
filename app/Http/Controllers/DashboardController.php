<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Kategori;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Fetch all events with tickets and detail orders for global stats calculations
        $allEvents = Event::with(['tikets.detailOrders'])->get();
        
        $totalEvents = $allEvents->count();
        $upcomingCount = $allEvents->filter(fn($e) => $e->status === 'Upcoming')->count();
        $ongoingCount = $allEvents->filter(fn($e) => $e->status === 'Ongoing')->count();
        $completedCount = $allEvents->filter(fn($e) => $e->status === 'Completed')->count();
        
        // Sum total capacity and sisa stok
        $totalCapacity = $allEvents->sum(function($e) {
            return $e->tikets->sum('stok');
        });
        
        $totalSold = $allEvents->sum(function($e) {
            return $e->tikets->sum(function($t) {
                return $t->detailOrders->sum('jumlah');
            });
        });
        
        $globalSisaStok = max(0, $totalCapacity - $totalSold);
        $globalStokPersentase = $totalCapacity > 0 ? round(($globalSisaStok / $totalCapacity) * 100) : 0;
        
        $totalCategories = Kategori::count();

        // Recent 3 upcoming events (closest date first)
        $recentEvents = Event::with(['kategori', 'tikets.detailOrders'])
            ->where('tanggal_waktu', '>', now())
            ->orderBy('tanggal_waktu', 'asc')
            ->take(3)
            ->get();

        return view('pages.admin.dashboard', compact(
            'totalEvents',
            'upcomingCount',
            'ongoingCount',
            'completedCount',
            'globalSisaStok',
            'totalCapacity',
            'globalStokPersentase',
            'totalCategories',
            'recentEvents'
        ));
    }
}
