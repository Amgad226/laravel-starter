<?php

namespace App\Exports;

use App\Models\BaseModel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ModelExport extends BaseExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $models;
    public function __construct()
    {
        $this->models = BaseModel::with([
            'translations',

        ])->get();
    }
    public function headings(): array
    {
        return [
            'id',
            'unit_en',
            'unit_ar',
            'created_at',
        ];
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->models as $unit) {

            $data[] = [
                'id' => $unit->id,
                'unit_en' => $unit->translate('en')->text,
                'unit_ar' => $unit->translate('ar')->text,
                'created_at' => $unit->created_at->format('Y-m-d H:i:s'),
            ];
        }
        return $data;
    }
    public function registerEvents(): array
    {
        return $this->styleHeadings('A1:D1');
    }
}
