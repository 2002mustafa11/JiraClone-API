<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkSpace extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'admin_id', 'image'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workspace) {
            if (empty($workspace->id)) {
                $workspace->id = (string) Str::uuid();
            }
        });
    }


    public function employees()
    {
        return $this->hasMany(User::class, 'workspace_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'workspace_id');
    }
}
