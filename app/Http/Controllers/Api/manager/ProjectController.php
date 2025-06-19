<?php

namespace App\Http\Controllers\Api\manager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\WorkSpace;
use Illuminate\Http\Request;
// use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;
use App\Http\traits\UploadFile;

class ProjectController extends Controller
{
    use UploadFile;

    public function index($workspace_id)
    {
        $user = Auth::user();

        $workspace = WorkSpace::find($workspace_id);
        if (!$workspace) {
            return ApiResponse::error('مساحة العمل غير موجودة.', 404);
        }

        if ($user->hasRole('admin') && $user->id != $workspace->admin_id) {
            return ApiResponse::error('غير مسموح لك بعرض هذا المشروع.', 403);
        }
        elseif ($user->hasRole('manager') && $user->workspace_id != $workspace_id) {
            return ApiResponse::error('غير مسموح لك بعرض هذا المشروع.', 403);
        }
        if ($user->hasRole('employee') ) {
            // $projects = Project::where('workspace_id', $workspace_id)
            //     ->whereHas('tasks', function ($query) use ($user) {
            //         $query->where('employee_id', $user->id);
            //     })
            //     ->with(['tasks' => function ($query) use ($user) {
            //         $query->where('employee_id', $user->id);
            //     }])
            //     ->get();
            $projects = Project::where('workspace_id', $workspace_id)
            ->whereHas('tasks', function ($query) use ($user) {
                $query->where('employee_id', $user->id);
            })
            ->with(['tasks' => function ($query) use ($user) {
                $query->when(request('status') !== 'open', function ($q) use ($user) {
                $q->where('employee_id', $user->id);
             });
            }])
           ->get();

        } else{
            $projects = Project::where('workspace_id', $workspace_id)
                ->with('tasks', 'workspace')
                ->get();
        }

        return ApiResponse::success( $projects, 'تم جلب جميع المشاريع بنجاح');
    }


    public function store(Request $request)
    {
        $user = Auth::user();

        // abort_unless($user->hasAnyRole(['admin','manager']), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workspace_id' => 'required|exists:work_spaces,id',
            'image' => 'nullable|image',
        ]);
        $workspace = WorkSpace::find($request->workspace_id);
        if (!$workspace) {
            return ApiResponse::error('مساحة العمل غير موجودة.', 404);
        }
        $admin_id = null;
        if ($user->hasRole('admin')) {
            $admin_id = Auth::id();
        } elseif ($user->hasRole('manager')) {
            $workspace = WorkSpace::find($request->workspace_id);
            if (!$workspace) {
                return ApiResponse::error('المساحة غير موجودة.', 404);
            }
            $admin_id = $workspace->admin_id;
        }

        $filename = null;
        if ($request->hasFile('image')) {
            $filename = $this->UploadImage($request->image, 'project/images');
        }else{
            $filename = rand(1,5).'.jpg';
        }

        $project = Project::create([
            // 'id' => Str::uuid(),
            'name' => $request->name,
            'description' => '$request->description',
            'admin_id' => $admin_id,
            'workspace_id' => $request->workspace_id,
            'image' => $filename,
            'status' => 'open'

        ]);

        return ApiResponse::success($project, 'تم إنشاء المشروع بنجاح', 201);
    }



    public function show(string $id)
    {
        $user = Auth::user();

        // abort_unless($user->hasAnyRole(['admin', 'manager']), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $project = Project::with( 'tasks','workspace:id,admin_id')->findOrFail($id);
        // $tasks = Task::where('project_id',$project->id)->get();
        if (!$project->workspace) {
            return ApiResponse::error('المشروع غير مرتبط بمساحة عمل.', 404);
        }

        // if ($user->hasRole('admin') && $user->id != $project->workspace->admin_id) {
        //     return ApiResponse::error('غير مسموح لك بعرض هذا المشروع.', 403);
        // } elseif ($user->hasRole('manager') && $user->workspace_id != $project->workspace_id) {
        //     return ApiResponse::error('غير مسموح لك بعرض هذا المشروع.', 403);
        // }

        return ApiResponse::success($project, 'تم جلب تفاصيل المشروع بنجاح');
    }



    public function update(Request $request, string $id)
    {

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'image' => 'nullable|image',
        ]);
        $project = Project::findOrFail($id);

        $filename = $project->image;

        if ($request->hasFile('image')) {
            if ($project->image) {
                $this->DeleteImage($project->image, 'project/images');
            }
            $filename = $this->UploadImage($request->image, 'project/images');
        }elseif(!$filename){
            $filename = rand(1,5).'.jpg';
        }
        $project->update([
            'name' => $request->name,
            'description' => '$request->description',
            'image' => $filename,
        ]);

        return ApiResponse::success($project, 'تم تحديث المشروع بنجاح');
    }

    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        if ($project->image) {
            $this->DeleteImage($project->image,'project/images');

        }
        return ApiResponse::success(null, 'تم حذف المشروع بنجاح');
    }


}
