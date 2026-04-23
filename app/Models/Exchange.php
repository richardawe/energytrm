<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    protected $fillable = ['code', 'name', 'timezone', 'country', 'is_active', 'version'];
    protected $casts = ['is_active' => 'boolean', 'version' => 'integer'];
}
