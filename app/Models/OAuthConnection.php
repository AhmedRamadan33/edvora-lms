<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthConnection extends Model
{
    protected $table = 'oauth_connections';

    public const PROVIDER_ZOOM = 'zoom';

    public const PROVIDER_GOOGLE_MEET = 'google_meet';

    protected $fillable = [
        'user_id', 'provider', 'provider_user_id', 'provider_email',
        'access_token', 'refresh_token', 'expires_at', 'scopes', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
