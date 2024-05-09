<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterSheet;

class BaseExport
{
    use Exportable;


    public function styleHeadings(string $columns): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) use ($columns) {
                $event->sheet->getStyle($columns)->applyFromArray(
                    [
                        'font' => [
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'strikethrough' => false,
                            'color' => [
                                'rgb' => '000000'
                            ]
                        ],
                    ]
                );
            },
        ];
    }
}
