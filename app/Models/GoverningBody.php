<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GoverningBody extends Model
{
    protected $fillable = ['name', 'jurisdiction', 'country', 'is_active', 'version'];
    protected $casts = ['is_active' => 'boolean', 'version' => 'integer'];
}
