<?php

namespace App\Imports;

use App\Models\BaseModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ModelImport implements
    ToCollection,
    WithHeadingRow,
    SkipsOnError,
    WithValidation,
    SkipsOnFailure,
    WithEvents
{
    use Importable, SkipsErrors, SkipsFailures, RegistersEventListeners;
    /**
     * @param Collection $collection
     */

    public function rules(): array
    {
        return [
            '*.unit_en' => ['required'],
            '*.unit_ar' => ['required'],
        ];
    }

    // auto format excel file  [alt ,h, o, i] 
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $unit_row =  [
                'en' => ['text' => $row['unit_en']],
                'ar' => ['text' => $row['unit_ar']],
                'type' => 'CALIBER_UNIT'
            ];

            $curr_unit = BaseModel::where('text', $row['unit_en'])->first();
            $unit = $curr_unit ? $curr_unit : BaseModel::create($unit_row);
        }
    }
}
