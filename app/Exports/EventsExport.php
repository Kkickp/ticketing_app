<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * Return collection of events.
     */
    public function collection()
    {
        return Event::with(['kategori', 'tikets'])->get();
    }

    /**
     * Define the headings.
     */
    public function headings(): array
    {
        return [
            'ID Event',
            'Judul Event',
            'Kategori',
            'Tanggal & Waktu',
            'Lokasi',
            'Status',
            'Harga Tiket Reguler',
            'Stok Reguler',
            'Harga Tiket Premium',
            'Stok Premium',
            'Deskripsi'
        ];
    }

    /**
     * Map each row of the collection.
     */
    public function map($event): array
    {
        $reguler = $event->tikets->firstWhere('tipe', 'reguler');
        $premium = $event->tikets->firstWhere('tipe', 'premium');

        return [
            $event->id,
            $event->judul,
            $event->kategori->nama ?? '-',
            $event->tanggal_waktu ? $event->tanggal_waktu->locale('id')->translatedFormat('d F Y, H:i') : '-',
            $event->lokasi,
            $event->status,
            $reguler ? $reguler->harga : '-',
            $reguler ? $reguler->stok : '-',
            $premium ? $premium->harga : '-',
            $premium ? $premium->stok : '-',
            strip_tags($event->deskripsi)
        ];
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Make header row bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
