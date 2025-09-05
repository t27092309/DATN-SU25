<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarAttribute($value)
    {
        // Kiểm tra nếu giá trị tồn tại
        if ($value) {
            // Trả về URL đầy đủ
            return Storage::url($value);
        }

        return null; // Trả về null nếu không có ảnh
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->role === $role || $this->roles()->where('name', $role)->exists();
        }
        
        return $this->roles()->whereIn('name', $role)->exists();
    }

    public function hasPermission($permission)
    {
        // Kiểm tra quyền từ role trực tiếp
        if ($this->role === 'admin') {
            return true; // Admin có tất cả quyền
        }

        // Kiểm tra quyền từ roles quan hệ
        return $this->roles()->whereJsonContains('permissions', $permission)->exists();
    }

    public function hasAnyPermission($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        if ($this->role === 'admin') {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function getAllPermissions()
    {
        $permissions = [];
        
        // Thêm quyền từ role trực tiếp
        if ($this->role === 'admin') {
            $permissions = [
                'dashboard:view',
                'products:view', 'products:create', 'products:edit', 'products:delete',
                'categories:view', 'categories:create', 'categories:edit', 'categories:delete',
                'brands:view', 'brands:create', 'brands:edit', 'brands:delete',
                'orders:view', 'orders:edit', 'orders:delete',
                'users:view', 'users:create', 'users:edit', 'users:delete',
                'reports:view',
                'settings:view', 'settings:edit'
            ];
        } elseif ($this->role === 'staff') {
            $permissions = [
                'dashboard:view',
                'products:view', 'products:edit',
                'categories:view',
                'brands:view',
                'orders:view', 'orders:edit',
                'reports:view'
            ];
        }

        // Thêm quyền từ roles quan hệ
        foreach ($this->roles as $role) {
            if ($role->permissions) {
                $permissions = array_merge($permissions, $role->permissions);
            }
        }

        return array_unique($permissions);
    }
}
