<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FieldDescription extends Model
{
    protected $fillable = ['tab', 'subtab', 'field_name', 'source_type', 'short_description'];

    // Static cache: keyed by "tab::field_name"
    private static ?Collection $cache = null;

    public static function tip(string $fieldName, string $tab = ''): ?string
    {
        if (static::$cache === null) {
            static::$cache = static::all()->keyBy(fn($r) => strtolower($r->tab . '::' . $r->field_name));
        }

        // Try tab-specific match first, then any-tab fallback
        $key      = strtolower($tab . '::' . $fieldName);
        $anyKey   = strtolower('::' . $fieldName);
        $record   = static::$cache->get($key)
                 ?? static::$cache->first(fn($r) => strtolower($r->field_name) === strtolower($fieldName));

        return $record?->short_description;
    }
}
