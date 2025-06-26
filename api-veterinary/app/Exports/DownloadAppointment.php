<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DownloadAppointment implements FromView
{
    protected $appointments;

    public function __construct($appointments)
    {
        $this->appointments = $appointments;
    }

    public function view(): View
    {
        return view("appointments.download_excel", [
            "appointments" => $this->appointments,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Aplica negrita al encabezado
            1 => ['font' => ['bold' => true]],
        ];
    }
}
