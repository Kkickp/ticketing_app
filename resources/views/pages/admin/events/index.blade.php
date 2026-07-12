@extends('layouts.admin_layouts')

@section('title', 'Manajemen Event')

@section('content')
<div class="container mx-auto p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-semibold text-gray-800">Manajemen Event</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data event, penjualan tiket, dan kategori terkait.</p>
        </div>
        <div class="flex gap-2">
            <!-- Bulk Delete Button (form-linked, hidden by default) -->
            <button type="submit" form="bulk-delete-form" id="bulk-delete-btn" class="btn bg-red-500 border-red-500 text-white hover:bg-red-600 shadow-md transition-all duration-300 hidden" onclick="return confirm('Apakah Anda yakin ingin menghapus event yang terpilih?')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-15 0M9 6v12m6-12v12" />
                </svg>
                Hapus Terpilih
            </button>
            <a href="{{ route('admin.events.export') }}" class="btn bg-green-600 border-green-600 hover:bg-green-700 text-white shadow-md transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m0 0l-3-3m3 3l3-3m-9 6h12" />
                </svg>
                Export Excel
            </a>
            
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary shadow-md hover:shadow-lg transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Event
            </a>
        </div>
    </div>



    <!-- Filters Section -->
    <div class="card bg-white shadow-xs rounded-box mb-6 border border-gray-100">
        <div class="card-body p-6">
            <form method="GET" action="{{ route('admin.events.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Search Input -->
                <div class="form-control w-full">
                    <label class="label mb-1.5 py-0">
                        <span class="label-text font-medium text-gray-600 text-xs">Cari Event</span>
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul atau lokasi..." class="input input-bordered w-full input-sm" />
                </div>

                <!-- Category Filter -->
                <div class="form-control w-full">
                    <label class="label mb-1.5 py-0">
                        <span class="label-text font-medium text-gray-600 text-xs">Kategori</span>
                    </label>
                    <select name="kategori_id" class="select select-bordered select-sm w-full">
                        <option value="">Semua Kategori</option>
                        @foreach(\App\Models\Kategori::all() as $cat)
                            <option value="{{ $cat->id }}" {{ request('kategori_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="form-control w-full">
                    <label class="label mb-1.5 py-0">
                        <span class="label-text font-medium text-gray-600 text-xs">Urutkan Tanggal</span>
                    </label>
                    <select name="sort" class="select select-bordered select-sm w-full">
                        <option value="asc" {{ request('sort', 'asc') === 'asc' ? 'selected' : '' }}>Terdekat (Ascending)</option>
                        <option value="desc" {{ request('sort') === 'desc' ? 'selected' : '' }}>Terjauh (Descending)</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 w-full">
                    <button type="submit" class="btn btn-primary btn-sm flex-1">
                        Filter
                    </button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline btn-sm flex-1">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Table Card inside Bulk Delete Form -->
    <div class="card bg-white shadow-xs rounded-box border border-gray-100 overflow-hidden">
        <form id="bulk-delete-form" method="POST" action="{{ route('admin.events.bulk-destroy') }}">
            @csrf
            @method('DELETE')
            
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-700 text-xs uppercase tracking-wider">
                            <th class="w-12 text-center">
                                <input type="checkbox" id="select-all" class="checkbox checkbox-xs border-gray-400" />
                            </th>
                            <th>Info Event</th>
                            <th>Kategori</th>
                            <th>Waktu & Lokasi</th>
                            <th>Penjualan Tiket</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <!-- Bulk Selection Checkbox -->
                            <td class="text-center">
                                <input type="checkbox" name="ids[]" value="{{ $event->id }}" class="checkbox checkbox-xs event-checkbox border-gray-300" />
                            </td>

                            <!-- Event Info -->
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar">
                                        <div class="mask mask-squircle w-16 h-16 object-cover shadow-sm bg-gray-100">
                                            <img src="{{ $event->image_url }}" alt="{{ $event->judul }}" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 line-clamp-1">{{ $event->judul }}</div>
                                        <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">Oleh: {{ $event->user->name ?? 'Admin' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td>
                                <span class="badge badge-outline badge-md py-2.5 font-medium border-gray-200 text-gray-700">
                                    {{ $event->kategori->nama ?? 'Tidak Ada' }}
                                </span>
                            </td>

                            <!-- Date & Location -->
                            <td>
                                <div class="text-sm font-medium text-gray-800">
                                    {{ $event->tanggal_waktu ? \Carbon\Carbon::parse($event->tanggal_waktu)->locale('id')->translatedFormat('d M Y, H:i') : '-' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    {{ $event->lokasi }}
                                </div>
                            </td>

                            <!-- Penjualan Tiket -->
                            <td>
                                <div class="flex flex-col gap-2 min-w-[160px]">
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
                                                <span class="font-medium text-gray-750">{{ $icon }} {{ $label }}</span>
                                                <span class="{{ $textColor }} text-[10px]">{{ $sisa }}/{{ $total }} Sisa</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                                                <div class="{{ $progressColor }} h-1 rounded-full transition-all duration-500" style="width: {{ $persentase_sisa }}%"></div>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-xs text-gray-400">Tidak ada tiket</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                @php
                                    $status = $event->status;
                                    $statusClasses = match($status) {
                                        'Upcoming' => 'badge-success text-white',
                                        'Ongoing' => 'badge-warning text-gray-800',
                                        'Completed' => 'badge-neutral text-gray-500 bg-gray-100 border-none',
                                        default => 'badge-ghost',
                                    };
                                @endphp
                                <span class="badge {{ $statusClasses }} font-semibold text-xs py-2 px-3 shadow-xs">
                                    {{ $status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-right">
                                <div class="inline-flex gap-1.5">
                                    <!-- Public Show Link -->
                                    <a href="{{ route('events.show', $event) }}" target="_blank" class="btn btn-square btn-sm btn-ghost hover:bg-gray-100" title="Lihat Halaman Publik">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>

                                    <!-- Clone/Duplicate Action -->
                                    <button type="button" onclick="submitClone({{ $event->id }})" class="btn btn-square btn-sm bg-purple-500 border-purple-500 text-white hover:bg-purple-600 shadow-xs" title="Duplikat Event">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5A3.375 3.375 0 006.375 7.5H5.25A2.25 2.25 0 003 9.75v8.25A2.25 2.25 0 005.25 20.25h9a2.25 2.25 0 002.25-2.25z" />
                                        </svg>
                                    </button>

                                    <!-- Edit Link -->
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-square btn-sm btn-info text-white hover:bg-blue-600 shadow-xs" title="Edit Event">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>

                                    <!-- Delete Trigger -->
                                    <button type="button" onclick="openDeleteEventModal(this)" data-id="{{ $event->id }}" data-title="{{ $event->judul }}" class="btn btn-square btn-sm bg-red-500 border-red-500 text-white hover:bg-red-600 shadow-xs" title="Hapus Event">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" class="w-12 h-12 text-gray-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
                                    </svg>
                                    <span class="font-medium text-sm">Tidak ada event yang ditemukan.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Pagination Section -->
        @if($events->hasPages())
        <div class="p-6 border-t border-gray-100 flex justify-center bg-gray-50/50">
            {{ $events->appends(request()->except('page'))->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<dialog id="delete_event_modal" class="modal">
    <form method="POST" class="modal-box max-w-md">
        @csrf
        @method('DELETE')

        <div class="text-center p-4">
            <!-- Warning Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="h-6 w-6 text-red-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Hapus Event</h3>
            <p class="text-sm text-gray-500 mt-2">
                Apakah Anda yakin ingin menghapus event <span id="delete_event_title" class="font-bold text-gray-800"></span>? Tindakan ini bersifat permanen.
            </p>
        </div>

        <div class="modal-action flex justify-center gap-3">
            <button type="submit" class="btn bg-red-500 border-red-500 text-white hover:bg-red-600 w-28">Hapus</button>
            <button type="button" class="btn w-28" onclick="delete_event_modal.close()">Batal</button>
        </div>
    </form>
</dialog>

<!-- Hidden Form for Cloning -->
<form id="clone-form" method="POST" action="" style="display: none;">
    @csrf
</form>

<script>
    function openDeleteEventModal(button) {
        const id = button.dataset.id;
        const title = button.dataset.title;
        const form = document.querySelector('#delete_event_modal form');
        
        document.getElementById("delete_event_title").textContent = title;
        form.action = `{{ url('/admin/events') }}/${id}`;
        
        delete_event_modal.showModal();
    }

    function submitClone(eventId) {
        if (confirm('Apakah Anda yakin ingin menduplikat event ini beserta tiketnya?')) {
            const form = document.getElementById('clone-form');
            form.action = `{{ url('/admin/events') }}/${eventId}/clone`;
            form.submit();
        }
    }

    // Checkbox bulk action scripts
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.event-checkbox');
        const bulkBtn = document.getElementById('bulk-delete-btn');
        
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                toggleBulkButton();
            });
        }
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    selectAll.checked = allChecked;
                }
                toggleBulkButton();
            });
        });
        
        function toggleBulkButton() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            if (anyChecked) {
                bulkBtn.classList.remove('hidden');
            } else {
                bulkBtn.classList.add('hidden');
            }
        }
    });
</script>
@endsection
