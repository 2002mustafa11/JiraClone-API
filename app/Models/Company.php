<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'admin_id'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->id)) {
                $user->id = (string) Str::uuid();
            }
        });
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function workSpaces()
    {
        return $this->hasMany(WorkSpace::class, 'company_id');
    }
}
