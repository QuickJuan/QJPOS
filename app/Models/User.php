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
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles {
        hasRole as protected traitHasRole;
        hasPermissionTo as protected traitHasPermissionTo;
    }

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
        'username',
        'email',
        'password',
        'pincode',
        'branch_id',
        'user_interface',
        'otp_secret',
        'otp_enabled',
        'otp_enabled_at',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'pincode',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'otp_secret',
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
            'pincode'           => 'hashed',
            'user_interface'    => \App\Enums\CurrentRole::class,
            'otp_enabled'       => 'boolean',
            'otp_enabled_at'    => 'datetime',
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

    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }

    public function isStaff(): bool
    {
        return $this->user_type === 'staff' || $this->user_type === null;
    }

    /**
     * Use the trait's roles() method without override
     * The trait handles polymorphic relationships correctly
     */

    /**
     * Use the trait's permissions() method without override
     * The trait handles polymorphic relationships correctly
     */

    /**
     * Safe hasRole method for central domain
     */
    public function hasRole($roles, string $guard = null): bool
    {
        if (!function_exists('tenancy') || !tenancy()->initialized) {
            return false;
        }

        return $this->traitHasRole($roles, $guard);
    }

    /**
     * Safe hasPermissionTo method for central domain
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (!function_exists('tenancy') || !tenancy()->initialized) {
            return false;
        }

        return $this->traitHasPermissionTo($permission, $guardName);
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

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
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
