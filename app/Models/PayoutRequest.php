<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    protected $fillable = [
        'instructor_id', 'amount', 'method', 'account_details', 'status', 'admin_note',
        'transaction_reference', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public static function methodLabel(string $method): string
    {
        return match ($method) {
            'paypal' => __('PayPal'),
            'bank_transfer' => __('Bank transfer'),
            'e_wallet' => __('E-wallet'),
            default => $method,
        };
    }
}
