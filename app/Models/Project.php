<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'workspace_id',
        'admin_id',
        'image',
        'status',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->id)) {
                $project->id = (string) Str::uuid();
            }
        });
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function workspace()
    {
        return $this->belongsTo(WorkSpace::class, 'workspace_id');
    }
    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id');
    }

}
