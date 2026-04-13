<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndexGridPoint extends Model
{
    protected $fillable = ['index_id', 'price_date', 'price', 'entered_by'];
    protected $casts = ['price_date' => 'date', 'price' => 'decimal:6'];

    public function index(): BelongsTo
    {
        return $this->belongsTo(IndexDefinition::class, 'index_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
