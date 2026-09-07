<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;
    use HasRoles {
        hasRole as traitHasRole;
        hasAnyRole as traitHasAnyRole;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'division_id',
        'location_type',
        'location_id',
        'profile_photo',
        'role_name',
        'unit_id',
        'matrix_group_id'
    ];

    public function hasRole($roles, string $guard = null): bool
    {
        if ($this->traitHasRole($roles, $guard)) {
            return true;
        }

        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = explode('|', $roles);
        }

        if (is_string($roles)) {
            return $roles === $this->role_name;
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->role_name === $role) {
                    return true;
                }
            }
        }
        
        return false;
    }

    public function hasAnyRole(...$roles): bool
    {
        if ($this->traitHasAnyRole(...$roles)) {
            return true;
        }

        $roles = collect($roles)->flatten()->all();
        return in_array($this->role_name, $roles);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id');
    }
}
