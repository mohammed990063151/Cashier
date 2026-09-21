<?php

namespace App\Services\AiAssistant;

use Illuminate\Support\Facades\Schema;

class SchemaSafe
{
    public static function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
