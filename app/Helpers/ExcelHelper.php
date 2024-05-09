<?php

namespace App\Helpers;

use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Str;
use \Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

class ExcelHelper
{

    //SECTION [import File]

    public static function importExcelFile($import_class, $file)
    {
        $import = new $import_class();
        $import->import($file);
        return $import;
    }

    public static function excelFileToArray($import_class, $file)
    {
        $data = Excel::toCollection(new $import_class(), $file)->first();
        return $data->toArray();
    }

    public static function importAndExportErrorFile($import_class, $export_status_class, $file): array
    {
        $data_inside_excel = self::excelFileToArray($import_class, $file);

        $import = self::importExcelFile($import_class, $file);

        $invalid_rows = [];

        $keysFile = array_keys($data_inside_excel[0]);
        array_push($keysFile, 'import_status');
        array_unshift($data_inside_excel, $keysFile);
        array_unshift($invalid_rows, $keysFile);


        foreach ($import->failures() as $key => $failure) {
            $row_index = $failure->row() - 1;
            $data_inside_excel[$failure->row() - 1]['import_status'] = 'invalid';
            $data_inside_excel[$failure->row() - 1][$failure->attribute()] = $failure->errors();
            $invalid_rows[] = $data_inside_excel[$row_index];
        }
        if (count($invalid_rows) == 1) {
            return [
                'have_validation_errors' => true,
            ];
        } else {
            return [
                'have_validation_errors' => false,
                'validation_errors_file_link' => self::storeProductsStatusManagementFile(new $export_status_class($invalid_rows))
            ];
        }
    }

    //SECTION [Export File]

    public static function convertValidationExcelExceptionToArray(ExcelValidationException $e): array
    {
        $failures = $e->failures();
        $invalid_rows = [];
        $keysFile = [];
        array_push($keysFile, 'import_status');
        $keysFile = array_merge($keysFile, array_keys($failures[0]->values()));
        array_unshift($invalid_rows, $keysFile);
        foreach ($failures as $key => $failure) {
            $attribute = $failure->attribute();
            $invalid_row = [];
            $invalid_row['import_status'] = $attribute;
            $invalid_row = array_merge($invalid_row, $failure->values());
            $invalid_row[$attribute] = $failure->errors();
            $invalid_rows[] = $invalid_row;
        }
        return $invalid_rows;
    }
    public static function storeProductsStatusManagementFile($exports): string
    {
        $user_id = auth()->user()->id ?? 'user_id';
        $name = uniqid("", true) . '-' . Carbon::now()->toDateString();
        $extDate = Carbon::now()->toDateString();
        $extra = Str::random(10);
        $filePath = "Products/{$user_id}/$extra/Products-Status{$extDate}/Export{$name}.xlsx";
        Excel::store($exports, $filePath, 'public');
        return config('app.APP_URL') . '/storage/' . $filePath;
    }
}
