<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model {
    public const INCOME = 'รายรับ';
    public const EXPENSE = 'รายจ่าย';

    protected $primaryKey = 'cat_id';

    protected $fillable = [
        'cat_name',
        'cat_type',
    ];

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class, 'cat_id', 'cat_id');
    }

    public function isExpense(): bool {
        return $this->cat_type === self::EXPENSE;
    }
}
