<?php

namespace App\Models;

use App\Models\Traits\HasSubscriptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Authenticatable model for academic and TVET institution accounts.
 *
 * Separate auth guard from Designer. Types: university, college, tvet,
 * training_center. Can submit trainings, workshops, and announcements
 * which require admin approval before going live. Uses HasSubscriptions
 * for profile and category notification subscriptions.
 */
class AcademicAccount extends Authenticatable
{
    use HasFactory, Notifiable, HasSubscriptions;

    protected $table = 'academic_accounts';

    protected $fillable = [
        'name',
        'email',
        'password',
        'institution_type',
        'logo',
        'banner',
        'description',
        'website',
        'phone',
        'address',
        'city',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // ==========================================
    // Relationships
    // ==========================================

    public function trainings()
    {
        return $this->hasMany(AcademicTraining::class, 'academic_account_id');
    }

    public function workshops()
    {
        return $this->hasMany(AcademicWorkshop::class, 'academic_account_id');
    }

    public function announcements()
    {
        return $this->hasMany(AcademicAnnouncement::class, 'academic_account_id');
    }

    // ==========================================
    // Accessors
    // ==========================================

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            $path = str_starts_with($this->logo, 'academic-accounts/') ? $this->logo : 'academic-accounts/' . $this->logo;
            return url('media/' . $path);
        }
        return null;
    }

    public function getBannerUrlAttribute()
    {
        if ($this->banner) {
            $path = str_starts_with($this->banner, 'academic-accounts/') ? $this->banner : 'academic-accounts/' . $this->banner;
            return url('media/' . $path);
        }
        return null;
    }

    public function getInstitutionTypeLabelAttribute()
    {
        return match($this->institution_type) {
            'university' => __('University'),
            'tvet' => __('TVET'),
            'college' => __('College'),
            'ebdc' => __('EBDC'),
            'other' => __('Other'),
            default => __(ucfirst($this->institution_type)),
        };
    }

    public function getInstitutionTypeColorAttribute()
    {
        return match($this->institution_type) {
            'university' => 'blue',
            'tvet' => 'purple',
            'college' => 'green',
            'ebdc' => 'amber',
            'other' => 'gray',
            default => 'gray',
        };
    }

    // ==========================================
    // Methods
    // ==========================================

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('institution_type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%");
        });
    }

    // ==========================================
    // Statistics
    // ==========================================

    public function getPendingContentCount()
    {
        return $this->trainings()->pending()->count()
             + $this->workshops()->pending()->count()
             + $this->announcements()->pending()->count();
    }

    public function getApprovedContentCount()
    {
        return $this->trainings()->approved()->count()
             + $this->workshops()->approved()->count()
             + $this->announcements()->approved()->count();
    }

    public function getActiveContentCount()
    {
        return $this->trainings()->active()->count()
             + $this->workshops()->active()->count()
             + $this->announcements()->active()->count();
    }
}
