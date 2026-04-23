<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    protected $fillable = ['name', 'days_net', 'discount_rate', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'discount_rate' => 'decimal:4'];
}
