<?php

namespace App\Models;

use App\Services\OtpService;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, MustVerifyEmailTrait, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'phone',
        'avatar',
        'bio',
        'is_active',
        'provider',
        'provider_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(InstructorEarning::class, 'instructor_id');
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class, 'instructor_id');
    }

    public function oauthConnections(): HasMany
    {
        return $this->hasMany(OAuthConnection::class);
    }

    public function liveClasses(): HasMany
    {
        return $this->hasMany(LiveClass::class, 'instructor_id');
    }

    public function isEnrolledIn(int $courseId): bool
    {
        return $this->enrollments()->where('course_id', $courseId)->exists();
    }

    public function sendEmailVerificationNotification(): void
    {
        app(OtpService::class)->generateAndSend($this, OtpCode::PURPOSE_EMAIL_VERIFICATION);
    }
}
