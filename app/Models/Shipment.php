<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_number', 'trade_id', 'vessel_name', 'carrier_id',
        'incoterm_code', 'load_port', 'discharge_port',
        'bl_date', 'eta_load', 'eta_discharge', 'actual_load', 'actual_discharge',
        'qty_nominated', 'qty_loaded', 'qty_discharged',
        'delivery_status', 'comments', 'created_by',
    ];

    protected $casts = [
        'bl_date'          => 'date',
        'eta_load'         => 'date',
        'eta_discharge'    => 'date',
        'actual_load'      => 'date',
        'actual_discharge' => 'date',
        'qty_nominated'    => 'decimal:4',
        'qty_loaded'       => 'decimal:4',
        'qty_discharged'   => 'decimal:4',
    ];

    public static function nextShipmentNumber(): string
    {
        $year = now()->year;
        $last = static::where('shipment_number', 'like', "SHP-{$year}-%")->max('shipment_number');
        $seq  = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('SHP-%d-%04d', $year, $seq);
    }

    public function trade(): BelongsTo    { return $this->belongsTo(Trade::class); }
    public function carrier(): BelongsTo  { return $this->belongsTo(Party::class, 'carrier_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
