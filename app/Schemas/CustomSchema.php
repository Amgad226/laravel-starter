<?php

namespace App\Schemas;

use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Traits\Macroable;

class CustomSchema extends Schema
{
    public static function hasForeign($table, $columns)
    {
        $schemaManager = parent::getConnection()->getDoctrineSchemaManager();
        $tableDetails = $schemaManager->listTableDetails($table);
        $foreignKeys = $tableDetails->getForeignKeys();

        foreach ($foreignKeys as $foreignKey) {
            foreach ($columns as $column) {
                if (in_array($column, $foreignKey->getColumns())) {
                    return true;
                }
            }
        }
        return false;
    }
}
