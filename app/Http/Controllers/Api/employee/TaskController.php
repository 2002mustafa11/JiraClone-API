<?php

namespace App\Http\Controllers\Api\employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;


class TaskController extends Controller
{

    public function index()
    {
        // dd('');
        $employee_id = auth()->id();
        $tasks = Task::where('employee_id', $employee_id)->get();

        $projectIds = $tasks->pluck('project_id')->unique();

        $projects = Project::whereIn('id', $projectIds)->get();

        return response()->json([
            'tasks' => $tasks,
            'projects' => $projects
        ]);
    }


}
