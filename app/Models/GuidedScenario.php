<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuidedScenario extends Model
{
    protected $fillable = ['title', 'description', 'module', 'steps', 'sort_order', 'is_active'];

    protected $casts = [
        'steps'     => 'array',
        'is_active' => 'boolean',
    ];

    public function stepCount(): int
    {
        return count($this->steps ?? []);
    }

    public static function moduleLabel(string $module): string
    {
        return match ($module) {
            'master_data' => 'Master Data',
            'trades'      => 'Physical Trades',
            'operations'  => 'Operations',
            'financials'  => 'Financials',
            'risk'        => 'Risk & Analytics',
            default       => ucfirst($module),
        };
    }
}
