@extends('layouts.admin_layouts')

@section('title', 'Edit Event')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <!-- Back Button & Header -->
    <div class="mb-6">
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Manajemen Event
        </a>
        <h1 class="text-3xl font-semibold text-gray-800 mt-3">Edit Event</h1>
        <p class="text-sm text-gray-500 mt-1">Perbarui data event dan tiket Anda di bawah ini.</p>
    </div>

    <!-- Warning banner if event has ticket sales -->
    @if($hasSales)
    <div class="alert alert-warning shadow-xs mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h3 class="font-bold text-sm">Perhatian: Event ini sudah memiliki penjualan tiket!</h3>
            <div class="text-xs mt-0.5">Beberapa pengaturan seperti Tanggal & Waktu serta tiket yang sudah terjual tidak dapat diubah atau dihapus untuk menjaga integritas data transaksi.</div>
        </div>
    </div>
    @endif

    <!-- Form Card -->
    <div class="card bg-white shadow-xs rounded-box border border-gray-100 overflow-hidden">
        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="card-body p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Form Section: Event Details -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Detail Event</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Judul Event -->
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Judul Event</span>
                            <span class="text-error">*</span>
                        </label>
                        <input type="text" name="judul" value="{{ old('judul', $event->judul) }}" placeholder="Masukkan judul event" class="input input-bordered w-full input-sm" required>
                        @error('judul')
                            <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Kategori Dropdown -->
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Kategori</span>
                            <span class="text-error">*</span>
                        </label>
                        <select name="kategori_id" class="select select-bordered w-full select-sm" required>
                            <option value="" disabled>Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('kategori_id', $event->kategori_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Lokasi -->
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Lokasi</span>
                            <span class="text-error">*</span>
                        </label>
                        <input type="text" name="lokasi" value="{{ old('lokasi', $event->lokasi) }}" placeholder="Contoh: Stadion Utama GBK" class="input input-bordered w-full input-sm" required>
                        @error('lokasi')
                            <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Tanggal & Waktu -->
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Tanggal & Waktu</span>
                            <span class="text-error">*</span>
                            @if($hasSales)
                                <span class="text-warning text-xs font-semibold ml-1.5">(Terkunci - Sudah ada penjualan)</span>
                            @endif
                        </label>
                        <input type="datetime-local" name="tanggal_waktu" value="{{ old('tanggal_waktu', $event->tanggal_waktu ? \Carbon\Carbon::parse($event->tanggal_waktu)->format('Y-m-d\TH:i') : '') }}" class="input input-bordered w-full input-sm" {{ $hasSales ? 'readonly onclick="return false;" style="background-color: #f3f4f6; cursor: not-allowed;"' : '' }} required>
                        @error('tanggal_waktu')
                            <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Gambar File Input & current image with cropping -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Gambar Banner</span>
                            <span class="text-xs text-gray-500">(Max 2MB, JPG/JPEG/PNG)</span>
                        </label>
                        
                        <!-- Current Image Display -->
                        <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50/50 mb-3 max-w-md">
                            <img src="{{ $event->image_url }}" alt="Banner Sekarang" class="w-24 h-16 object-cover rounded-md shadow-xs border bg-white">
                            <div>
                                <span class="text-xs font-bold text-gray-700 block">Banner Saat Ini</span>
                                <span class="text-xs text-gray-500 block mt-0.5 line-clamp-1">{{ $event->gambar }}</span>
                            </div>
                        </div>

                        <!-- Hidden input for cropped base64 image data -->
                        <input type="hidden" id="cropped-gambar-input" name="cropped_gambar">

                        <input type="file" id="gambar-input" accept="image/*" class="file-input file-input-bordered w-full file-input-sm">
                        <span class="text-xs text-gray-400 block mt-1">Kosongkan jika tidak ingin mengubah gambar</span>
                        @error('gambar')
                            <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                        @error('cropped_gambar')
                            <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                        @enderror

                        <!-- New Image Preview -->
                        <div id="image-preview-container" class="hidden mt-4 card bg-gray-50 border border-gray-200 p-4 items-center justify-center max-w-sm rounded-lg">
                            <span class="text-xs text-gray-500 mb-2 font-medium">Pratinjau Gambar Baru</span>
                            <img id="image-preview" src="#" alt="Pratinjau Gambar Baru" class="max-h-48 object-cover rounded-lg shadow-sm">
                            <button type="button" onclick="removeImagePreview()" class="btn btn-xs btn-outline btn-error mt-3">Batal Ganti Gambar</button>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi Event (Full Width) -->
                <div class="space-y-2 mt-6">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Deskripsi Event</span>
                        <span class="text-error">*</span>
                    </label>
                    <textarea name="deskripsi" rows="4" placeholder="Jelaskan detail mengenai konser/event ini..." class="textarea textarea-bordered w-full textarea-md" required>{{ old('deskripsi', $event->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Form Section: Ticket configurations -->
            <div class="pt-6 border-t border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Tiket Event</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola tipe tiket yang tersedia. Anda dapat menambahkan tipe tiket baru.</p>
                    </div>
                    <button type="button" onclick="addTicketCard()" class="btn btn-sm btn-outline btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Tiket Baru
                    </button>
                </div>

                <!-- Ticket list container -->
                <div id="tickets-container" class="space-y-4">
                    <!-- Cards will be dynamically added here -->
                </div>
                @error('tikets')
                    <span class="text-error text-sm mt-3 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit buttons -->
            <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.events.index') }}" class="btn btn-outline w-32">Batal</a>
                <button type="submit" class="btn btn-primary w-32 shadow-md">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Cropper Modal -->
<dialog id="cropper_modal" class="modal">
    <div class="modal-box max-w-2xl">
        <h3 class="font-bold text-lg mb-4">Crop Banner Event (16:9)</h3>
        <div class="max-h-96 overflow-hidden bg-gray-100 flex items-center justify-center rounded-lg border border-gray-200">
            <img id="cropper-image" src="" class="max-w-full max-h-96">
        </div>
        <div class="modal-action flex justify-end gap-3">
            <button type="button" id="crop-save-btn" class="btn btn-primary">Potong & Simpan</button>
            <button type="button" class="btn" onclick="closeCropperModal()">Batal</button>
        </div>
    </div>
</dialog>

<!-- Cropper.js CDNs -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<script>
    let ticketIndex = 0;

    function addTicketCard(tipe = 'reguler', harga = '', stok = '', id = '', hasOrders = false) {
        const container = document.getElementById('tickets-container');
        const index = ticketIndex++;
        const card = document.createElement('div');
        card.className = 'ticket-card card bg-gray-50 border border-gray-200/60 p-5 rounded-lg';
        card.id = `ticket-card-${index}`;

        // Build HTML
        let removeButtonHtml = '';
        if (hasOrders) {
            removeButtonHtml = `
                <span class="badge badge-warning text-xs font-semibold py-2 px-2.5 shadow-2xs">
                    Sudah Terjual (Terkunci)
                </span>
            `;
        } else {
            removeButtonHtml = `
                <button type="button" onclick="removeTicketCard(${index})" class="remove-ticket-btn btn btn-xs btn-outline btn-error px-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Hapus
                </button>
            `;
        }

        card.innerHTML = `
            <!-- Hidden inputs for ticket ID and order status -->
            <input type="hidden" name="tikets[${index}][id]" value="${id}">
            <input type="hidden" name="tikets[${index}][has_orders]" value="${hasOrders ? 1 : 0}">

            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h4 class="font-bold text-sm text-gray-700">Tiket #${index + 1}</h4>
                <div class="flex items-center gap-2">
                    ${removeButtonHtml}
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Tipe -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600">Tipe Tiket <span class="text-error">*</span></label>
                    <select name="tikets[${index}][tipe]" class="select select-bordered select-sm w-full" ${hasOrders ? 'readonly onclick="return false;" style="background-color: #f3f4f6; cursor: not-allowed;"' : ''} required>
                        <option value="reguler" ${tipe === 'reguler' ? 'selected' : ''}>Reguler</option>
                        <option value="premium" ${tipe === 'premium' ? 'selected' : ''}>Premium</option>
                    </select>
                </div>
                <!-- Harga -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600">Harga (Rp) <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-xs text-gray-500 font-medium">Rp</span>
                        <input type="number" name="tikets[${index}][harga]" value="${harga}" min="0" placeholder="Contoh: 150000" class="input input-bordered w-full input-sm pl-9" ${hasOrders ? 'readonly style="background-color: #f3f4f6; cursor: not-allowed;"' : ''} required>
                    </div>
                </div>
                <!-- Stok -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600">Stok <span class="text-error">*</span></label>
                    <div>
                        <input type="number" name="tikets[${index}][stok]" value="${stok}" min="0" placeholder="Contoh: 50" class="input input-bordered w-full input-sm" required>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
        updateRemoveButtons();
        updateTicketNumbers();
    }

    function removeTicketCard(index) {
        const card = document.getElementById(`ticket-card-${index}`);
        if (card) {
            card.remove();
            updateRemoveButtons();
            updateTicketNumbers();
        }
    }

    function updateRemoveButtons() {
        const cards = document.querySelectorAll('.ticket-card');
        const removeButtons = document.querySelectorAll('.remove-ticket-btn');
        if (cards.length <= 1) {
            removeButtons.forEach(btn => btn.style.display = 'none');
        } else {
            removeButtons.forEach(btn => btn.style.display = 'inline-flex');
        }
    }

    function updateTicketNumbers() {
        const cards = document.querySelectorAll('.ticket-card');
        cards.forEach((card, idx) => {
            const title = card.querySelector('h4');
            if (title) {
                title.textContent = `Tiket #${idx + 1}`;
            }
        });
    }

    // Handle Image Preview & Cropping
    let cropper = null;
    const gambarInput = document.getElementById('gambar-input');
    const croppedGambarInput = document.getElementById('cropped-gambar-input');
    const previewContainer = document.getElementById('image-preview-container');
    const previewImg = document.getElementById('image-preview');
    const cropperImage = document.getElementById('cropper-image');
    const cropperModal = document.getElementById('cropper_modal');
    const cropSaveBtn = document.getElementById('crop-save-btn');

    if (gambarInput) {
        gambarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    cropperImage.src = e.target.result;
                    cropperModal.showModal();
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    setTimeout(() => {
                        cropper = new Cropper(cropperImage, {
                            aspectRatio: 16 / 9,
                            viewMode: 1,
                            autoCropArea: 1,
                        });
                    }, 200);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    if (cropSaveBtn) {
        cropSaveBtn.addEventListener('click', function() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 800,
                    height: 450,
                });
                
                const base64 = canvas.toDataURL('image/jpeg');
                croppedGambarInput.value = base64;
                previewImg.src = base64;
                previewContainer.classList.remove('hidden');
                cropperModal.close();
            }
        });
    }

    function closeCropperModal() {
        cropperModal.close();
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (!croppedGambarInput.value) {
            gambarInput.value = '';
        }
    }

    function removeImagePreview() {
        if (gambarInput) {
            gambarInput.value = '';
        }
        croppedGambarInput.value = '';
        previewImg.src = '#';
        previewContainer.classList.add('hidden');
    }

    // Populate tickets
    document.addEventListener('DOMContentLoaded', () => {
        // Retrieve old tickets if validation failed, otherwise existing database tickets
        const oldTickets = {!! json_encode(old('tikets')) !!};
        
        if (oldTickets !== null) {
            // Render old tickets from failed submission
            if (oldTickets.length > 0) {
                oldTickets.forEach(t => {
                    addTicketCard(t.tipe, t.harga, t.stok, t.id || '', t.has_orders || false);
                });
            } else {
                addTicketCard();
            }
        } else {
            // Render database tickets
            const existingTickets = {!! json_encode($event->tikets->map(function($t) {
                return [
                    'id' => $t->id,
                    'tipe' => $t->tipe,
                    'harga' => $t->harga,
                    'stok' => $t->stok,
                    'has_orders' => $t->orders()->exists()
                ];
            })) !!};

            if (existingTickets.length > 0) {
                existingTickets.forEach(t => {
                    addTicketCard(t.tipe, t.harga, t.stok, t.id, t.has_orders);
                });
            } else {
                addTicketCard();
            }
        }
    });
</script>
@endsection
