<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'fx_rate_to_usd', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'fx_rate_to_usd' => 'decimal:8'];
}
