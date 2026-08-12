<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_amount', 'max_uses', 'used_count',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isValid(float $subtotal = 0): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($this->min_amount !== null && $subtotal < (float) $this->min_amount) {
            return false;
        }

        return true;
    }

    public function discountFor(float $subtotal): float
    {
        if (! $this->isValid($subtotal)) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? round($subtotal * ((float) $this->value / 100), 2)
            : (float) $this->value;

        return min($discount, $subtotal);
    }
}
