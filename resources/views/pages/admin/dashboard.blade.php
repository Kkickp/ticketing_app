@extends('layouts.admin_layouts')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container mx-auto p-6">
    <!-- Welcome Header -->
    <div class="card bg-gradient-to-r from-blue-700 to-indigo-800 text-white shadow-md rounded-box p-8 mb-8">
        <h1 class="text-3xl font-bold">Selamat Datang Kembali, {{ auth()->user()->name }}!</h1>
        <p class="mt-2 text-blue-100 text-sm max-w-xl">
            Selamat datang di panel kontrol BengTix. Gunakan panel ini untuk memantau aktivitas penjualan tiket event, stok kuota, dan memanajemen detail kategori.
        </p>
    </div>

    <!-- Dashboard Analytics & Summary Cards -->
    <h2 class="text-xl font-bold text-gray-800 mb-4">Statistik Global</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Stat 1: Total Event -->
        <div class="card bg-white shadow-xs border border-gray-100 p-6 flex flex-row items-center justify-between rounded-box">
            <div>
                <span class="text-gray-500 text-sm font-medium">Total Event</span>
                <div class="text-3xl font-bold mt-1 text-gray-800">{{ $totalEvents }}</div>
                <div class="text-xs text-gray-500 mt-2">
                    <span class="text-green-600 font-semibold">{{ $upcomingCount }} Upcoming</span> • 
                    <span class="text-warning font-semibold">{{ $ongoingCount }} Ongoing</span> • 
                    <span class="text-gray-400 font-semibold">{{ $completedCount }} Selesai</span>
                </div>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
        </div>

        <!-- Stat 2: Sisa Stok Tiket -->
        <div class="card bg-white shadow-xs border border-gray-100 p-6 flex flex-row items-center justify-between rounded-box">
            <div>
                <span class="text-gray-500 text-sm font-medium">Sisa Stok Tiket</span>
                <div class="text-3xl font-bold mt-1 text-gray-800">
                    {{ number_format($globalSisaStok) }} <span class="text-sm font-normal text-gray-500">/ {{ number_format($totalCapacity) }}</span>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <div class="w-24 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $globalStokPersentase }}%"></div>
                    </div>
                    <span class="text-xs text-green-600 font-semibold">{{ $globalStokPersentase }}% Tersedia</span>
                </div>
            </div>
            <div class="p-3 bg-green-50 text-green-600 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 9a3 3 0 0 1 0-6h16a3 3 0 0 1 0 6H2Z"></path>
                    <path d="M2 21a3 3 0 0 1 0-6h16a3 3 0 0 1 0 6H2Z"></path>
                    <path d="M10 3v18"></path>
                </svg>
            </div>
        </div>

        <!-- Stat 3: Total Kategori -->
        <div class="card bg-white shadow-xs border border-gray-100 p-6 flex flex-row items-center justify-between rounded-box">
            <div>
                <span class="text-gray-500 text-sm font-medium">Total Kategori</span>
                <div class="text-3xl font-bold mt-1 text-gray-800">{{ $totalCategories }}</div>
                <div class="text-xs text-gray-500 mt-2">Kategori aktif saat ini</div>
            </div>
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
            </div>
        </div>
    </div>

    <!-- Recent Upcoming Events -->
    <h2 class="text-xl font-bold text-gray-800 mb-4 font-sans">3 Event Mendatang Terdekat</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @forelse($recentEvents as $event)
        <div class="card bg-white shadow-xs border border-gray-100 p-6 rounded-box flex flex-col justify-between hover:shadow-sm transition-all duration-300">
            <div>
                <!-- Badge Kategori & Status -->
                <div class="flex justify-between items-center mb-3">
                    <span class="badge badge-outline badge-sm py-2 px-3 border-gray-200 text-gray-600 font-medium">
                        {{ $event->kategori->nama ?? 'Umum' }}
                    </span>
                    <span class="badge badge-success text-white font-semibold text-[10px] py-1.5 px-2.5">
                        Upcoming
                    </span>
                </div>
                
                <!-- Judul Event -->
                <h3 class="font-bold text-gray-800 text-base line-clamp-1 mb-1">{{ $event->judul }}</h3>
                <p class="text-xs text-gray-500 flex items-center gap-1 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $event->tanggal_waktu ? $event->tanggal_waktu->locale('id')->translatedFormat('d M Y, H:i') : '-' }}
                </p>
                
                <!-- Ticket Sales Progress per Ticket type -->
                <div class="space-y-3 border-t border-gray-100 pt-3">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Penjualan Tiket:</h4>
                    @forelse($event->tikets as $tiket)
                        @php
                            $total = $tiket->stok;
                            $terjual = $tiket->detailOrders->sum('jumlah');
                            $sisa = max(0, $total - $terjual);
                            $persentase_sisa = $total > 0 ? round(($sisa / $total) * 100) : 0;
                            
                            $icon = $tiket->tipe === 'reguler' ? '🎫' : '🎟️';
                            $label = ucfirst($tiket->tipe);
                            
                            $progressColor = $persentase_sisa <= 20 ? 'bg-red-500' : 'bg-green-500';
                            $textColor = $persentase_sisa <= 20 ? 'text-red-600 font-semibold' : 'text-gray-600';
                        @endphp
                        <div class="text-xs">
                            <div class="flex justify-between items-center mb-0.5">
                                <span class="font-medium text-gray-700">{{ $icon }} {{ $label }}</span>
                                <span class="{{ $textColor }} text-[10px]">{{ $sisa }}/{{ $total }} Sisa</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                                <div class="{{ $progressColor }} h-1 rounded-full" style="width: {{ $persentase_sisa }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400">Tidak ada tiket dibuat.</div>
                    @endforelse
                </div>
            </div>
            
            <!-- View Button -->
            <div class="mt-5 pt-3 border-t border-gray-100">
                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline btn-xs w-full">
                    Kelola Event
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 card bg-white border border-gray-100 p-8 text-center text-gray-400 rounded-box">
            Belum ada event mendatang terdekat.
        </div>
        @endforelse
    </div>

    <!-- Quick Navigation Shortcuts -->
    <h2 class="text-xl font-bold text-gray-800 mb-4">Navigasi Cepat</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Shortcut 1: Manajemen Event -->
        <a href="{{ route('admin.events.index') }}" class="card bg-white shadow-xs border border-gray-100 hover:shadow-md hover:border-blue-300 transition-all duration-300 p-6 flex flex-row items-center gap-4 rounded-box">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Manajemen Event</h3>
                <p class="text-xs text-gray-500 mt-0.5">Kelola tiket dan rincian event</p>
            </div>
        </a>

        <!-- Shortcut 2: Kelola Kategori -->
        <a href="{{ route('admin.kategori.index') }}" class="card bg-white shadow-xs border border-gray-100 hover:shadow-md hover:border-purple-300 transition-all duration-300 p-6 flex flex-row items-center gap-4 rounded-box">
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Manajemen Kategori</h3>
                <p class="text-xs text-gray-500 mt-0.5">Tambahkan/ubah kategori event</p>
            </div>
        </a>

        <!-- Shortcut 3: Tambah Event Baru -->
        <a href="{{ route('admin.events.create') }}" class="card bg-white shadow-xs border border-gray-100 hover:shadow-md hover:border-green-300 transition-all duration-300 p-6 flex flex-row items-center gap-4 rounded-box">
            <div class="p-3 bg-green-50 text-green-600 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Tambah Event Baru</h3>
                <p class="text-xs text-gray-500 mt-0.5">Buat event dan tiket baru</p>
            </div>
        </a>
    </div>
</div>
@endsection