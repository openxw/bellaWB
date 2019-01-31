<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function boot()
    {
        # code...
        parent::boot();

        static::creating(function ($user)
        {
            # 在user模型初始化后,创建随机字符串
            $user->activation_token = str_random(30);
        });
    }

    /**
     * gravatar 通用头像
     */
    public function gravatar($size = '100')
        {
            # code...
            $hash = md5(strtolower(trim($this->attributes['email'])));
            return "http://www.gravatar.com/avatar/$hash?s=$size";
        }

    public function sendPasswordResetNotification($token)
    {
        # code...
        $this->notify(new ResetPassword($token));
    }

    public function statuses()
    {
        # code...
        return $this->hasMany(Status::class);
    }

    public function feed()
        {
            # code...
            return $this->statuses()
                            ->orderBy('created_at','desc');
        }
}