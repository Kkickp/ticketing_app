<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'gambar',
        'tanggal_waktu',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($event) {
            $event->statusHistories()->create([
                'status_sebelum' => '-',
                'status_sesudah' => $event->status,
                'user_id' => auth()->id() ?? $event->user_id,
            ]);
        });

        static::updating(function ($event) {
            if ($event->isDirty('tanggal_waktu')) {
                $originalTanggalWaktu = $event->getOriginal('tanggal_waktu');
                $originalStatus = self::calculateStatusFromDate($originalTanggalWaktu);
                $newStatus = self::calculateStatusFromDate($event->tanggal_waktu);

                if ($originalStatus !== $newStatus) {
                    $event->statusHistories()->create([
                        'status_sebelum' => $originalStatus,
                        'status_sesudah' => $newStatus,
                        'user_id' => auth()->id() ?? $event->user_id,
                    ]);
                }
            }
        });
    }

    public function tikets()
    {
        return $this->hasMany(Tiket::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(EventStatusHistory::class);
    }

    public static function calculateStatusFromDate($tanggalWaktu)
    {
        if (!$tanggalWaktu) {
            return 'Completed';
        }

        $dateTime = \Carbon\Carbon::parse($tanggalWaktu);
        $now = now();

        if ($dateTime->isFuture()) {
            return 'Upcoming';
        }

        if ($dateTime->isPast() && $dateTime->diffInHours($now) <= 3) {
            return 'Ongoing';
        }

        return 'Completed';
    }

    public function getStatusAttribute()
    {
        return self::calculateStatusFromDate($this->tanggal_waktu);
    }

    public function hasSales()
    {
        return $this->orders()->exists();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_waktu', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('tanggal_waktu', '<=', now())
            ->where('tanggal_waktu', '>=', now()->subHours(3));
    }

    public function scopeCompleted($query)
    {
        return $query->where('tanggal_waktu', '<', now()->subHours(3));
    }

    public function getImageUrlAttribute()
    {
        if ($this->gambar && filter_var($this->gambar, FILTER_VALIDATE_URL)) {
            return $this->gambar;
        }

        $imageExists = !empty($this->gambar) && (
            file_exists(public_path('storage/' . $this->gambar)) ||
            \Illuminate\Support\Facades\Storage::disk('public')->exists($this->gambar)
        );

        $imageName = $imageExists ? $this->gambar : 'konser.jpg';

        return asset('storage/' . $imageName);
    }
}
