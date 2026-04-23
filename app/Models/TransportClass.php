<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransportClass extends Model
{
    protected $fillable = ['name', 'description', 'transfer_point', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
