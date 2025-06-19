<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'dueDate',
        'project_id',
        'employee_id',
        'status',
        'position',
        'start_date'
    ];
 protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (empty($task->id)) {
                $task->id = (string) Str::uuid();
            }
        });
    }
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class,'project_id');
    }
}
