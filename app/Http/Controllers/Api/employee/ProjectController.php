<?php

namespace App\Http\Controllers\Api\employee;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WorkSpace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        $projects = Project::where('workspace_id', $user->workspace_id)
            ->with(['tasks' => function ($query) use ($user) { 
                $query->where('assigned_to', $user->id);
            }])
            ->get();

        return response()->json($projects);
    }


}
