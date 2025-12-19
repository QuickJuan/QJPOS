<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_code',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user', 'user_id', 'branch_id')
            ->withTimestamps();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function canLoginTo(Branch $branch): bool
    {
        return $this->branches()->where('branches.id', $branch->id)->exists();
    }

    /**
     * Override roles relationship to only work in tenant context
     */
    public function roles(): BelongsToMany
    {
        if (!function_exists('tenancy') || !tenancy()->initialized) {
            return $this->belongsToMany(
                config('permission.models.role'),
                config('permission.table_names.model_has_roles'),
                'model_id',
                'role_id'
            )->where('id', 0); // Return empty relationship
        }

        // Call the trait's roles method
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        );
    }

    /**
     * Override permissions relationship to only work in tenant context
     */
    public function permissions(): BelongsToMany
    {
        if (!function_exists('tenancy') || !tenancy()->initialized) {
            return $this->belongsToMany(
                config('permission.models.permission'),
                config('permission.table_names.model_has_permissions'),
                'model_id',
                'permission_id'
            )->where('id', 0); // Return empty relationship
        }

        // Call the trait's permissions method
        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            'permission_id'
        );
    }

    /**
     * Safe hasRole method for central domain
     */
    public function hasRole($roles, string $guard = null): bool
    {
        if (!function_exists('tenancy') || !tenancy()->initialized) {
            return false;
        }

        return parent::hasRole($roles, $guard);
    }

    /**
     * Safe hasPermissionTo method for central domain
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (!function_exists('tenancy') || !tenancy()->initialized) {
            return false;
        }

        return parent::hasPermissionTo($permission, $guardName);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function cashierSessions(): HasMany
    {
        return $this->hasMany(CashierSession::class, 'cashier_id');
    }


    public function cashierSession(): HasOne
    {
        return $this->hasOne(CashierSession::class, 'cashier_id')->latestOfMany();

    }

    public function hasOpenSessionToAnotherBranch(Branch $branch): bool
    {
        return $this->cashierSessions()
            ->where('branch_id', '!=', $branch->id)
            ->whereNull('closing_time')
            ->exists();
    }

    public function getBranchWithOpenSession(Branch $branch): ?Branch
    {
        $session = $this->cashierSessions()
            ->where('branch_id', '!=', $branch->id)
            ->whereNull('closing_time')
            ->first();

        return $session ? $session->branch : null;
    }
}
