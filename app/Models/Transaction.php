<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model {
    protected $primaryKey = 'ts_id';

    protected $casts = [
        'ts_date' => 'date',
        'ts_amount' => 'decimal:2',
    ];

    protected $fillable = [
        'cat_id',
        'ts_amount',
        'ts_date',
        'ts_note',
    ];

    public function category(): BelongsTo {
        return $this->belongsTo(ExpenseCategory::class, 'cat_id', 'cat_id');
    }
}
