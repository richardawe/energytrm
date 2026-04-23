<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = ['name', 'commodity_group', 'description', 'is_active', 'version'];
    protected $casts = ['is_active' => 'boolean', 'version' => 'integer'];
}
