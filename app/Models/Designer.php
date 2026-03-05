<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\MarketplacePost;
use App\Models\Traits\HasSubscriptions;
use App\Models\ConversationRating;

class Designer extends Authenticatable
{
    use HasFactory, Notifiable, HasSubscriptions;

    protected $table = 'designers';

    /**
     * Boot the model - set initial active status based on admin auto-accept setting
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($designer) {
            // Only set is_active if not already explicitly set and not an admin
            if (!isset($designer->is_active) && !$designer->is_admin) {
                // Check if auto-accept is enabled for designers
                $designer->is_active = AdminSetting::isAutoAcceptEnabled('designers');
            }
        });
    }

    protected static function booted(): void
    {
        static::saved(function ($model) {
            \App\Services\CacheService::clearDashboardCache();
        });

        static::deleted(function ($model) {
            \App\Services\CacheService::clearDashboardCache();
            \App\Services\CacheService::clearDesignerCache($model->id);
        });
    }

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'title',
        'email',
        'email_verified_at',
        'avatar',
        'cover_image',
        'bio',
        'location',
        'website',
        'linkedin',
        'instagram',
        'facebook',
        'behance',
        'password',
        'sector',
        'sub_sector',
        'company_name',
        'position',
        'phone_number',
        'phone_country',
        'city',
        'address',
        'years_of_experience',
        'certifications',
        'show_email',
        'show_phone',
        'show_location',
        'allow_messages',
        'email_marketing',
        'email_notifications',
        'is_active',
        'is_tevet',
    ];

    // Protected fields - cannot be mass assigned (security critical)
    protected $guarded = [
        'id',
        'verified',
        'is_admin',
        'is_trusted',
        'followers_count',
        'following_count',
        'projects_count',
        'appreciations_count',
        'views_count',
        'likes_count',
        'remember_token',
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
            'verified' => 'boolean',
            'is_admin' => 'boolean',
            'is_trusted' => 'boolean',
            'is_active' => 'boolean',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'show_location' => 'boolean',
            'allow_messages' => 'boolean',
            'email_marketing' => 'boolean',
            'email_notifications' => 'boolean',
            'followers_count' => 'integer',
            'following_count' => 'integer',
            'projects_count' => 'integer',
            'appreciations_count' => 'integer',
            'views_count' => 'integer',
            'likes_count' => 'integer',
            'certifications' => 'array',
        ];
    }

    /**
     * Check if designer is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if designer is trusted (can bypass content approval)
     */
    public function isTrusted(): bool
    {
        return $this->is_trusted === true;
    }

    /**
     * Check if designer account is active
     */
    public function isActive(): bool
    {
        return $this->is_active !== false;
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'designer_skills', 'designer_id', 'skill_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'designer_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'designer_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'designer_id');
    }

    public function marketplacePosts()
    {
        return $this->hasMany(MarketplacePost::class, 'designer_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function sentMessageRequests()
    {
        return $this->hasMany(MessageRequest::class, 'from_designer_id');
    }

    public function receivedMessageRequests()
    {
        return $this->hasMany(MessageRequest::class, 'to_designer_id');
    }

    public function followers()
    {
        return $this->belongsToMany(Designer::class, 'designer_follows', 'following_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(Designer::class, 'designer_follows', 'follower_id', 'following_id');
    }

    /**
     * Get ratings received on this designer's profile
     */
    public function ratingsReceived()
    {
        return $this->hasMany(ProfileRating::class, 'designer_id');
    }

    /**
     * Get ratings given by this designer
     */
    public function ratingsGiven()
    {
        return $this->hasMany(ProfileRating::class, 'rater_id');
    }

    /**
     * Get average rating for this designer (combines profile + chat ratings, chat counts as 2x)
     */
    public function getAverageRatingAttribute()
    {
        $profileAvg = ProfileRating::getAverageRating($this->id);
        $profileCount = ProfileRating::getRatingCount($this->id);
        $chatAvg = ConversationRating::getAverageRating($this->id);
        $chatCount = ConversationRating::getRatingCount($this->id);

        $totalWeight = $profileCount + ($chatCount * 2);
        if ($totalWeight === 0) return 0;

        return (($profileAvg * $profileCount) + ($chatAvg * $chatCount * 2)) / $totalWeight;
    }

    /**
     * Get rating count for this designer (chat ratings count as 2 each)
     */
    public function getRatingCountAttribute()
    {
        return ProfileRating::getRatingCount($this->id) + (ConversationRating::getRatingCount($this->id) * 2);
    }
}
