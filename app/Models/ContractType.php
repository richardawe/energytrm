<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ContractType extends Model
{
    protected $fillable = ['name', 'code', 'description', 'incoterm', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
