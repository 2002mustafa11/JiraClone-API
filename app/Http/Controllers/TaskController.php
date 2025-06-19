<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function index(string $id)
    {
        $user = Auth::user();

        if ($user->hasRole('employee')) {
            $tasks = Task::where('project_id', $id)
                ->where('employee_id', $user->id)
                ->with(['project:id,name', 'employee:id,name'])
                ->get();
        } else {
            $tasks = Task::where('project_id', $id)
                ->with(['project:id,name', 'employee:id,name'])
                ->get();
        }

        return ApiResponse::success($tasks, 'تم جلب تفاصيل المشروع بنجاح');
    }

    public function show(string $id)
    {
        $task = Task::with(['project:id,name', 'employee:id,name'])->findOrFail($id);
        return ApiResponse::success($task, 'تم جلب تفاصيل المهمة بنجاح');
    }

    public function store(Request $request)
    {
        $manager = Auth::user();
        abort_unless($manager->hasAnyRole(['admin', 'manager']), 403, 'غير مصرح لك');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'dueDate' => 'nullable|string',
            'start_date' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('فشل التحقق من البيانات', 422, $validator->errors());
        }

        if (Task::where('name', $request->name)->exists()) {
            return ApiResponse::error('اسم المهمة موجود بالفعل.', 409);
        }

        $project = Project::findOrFail($request->project_id);
        $employee = User::findOrFail($request->employee_id);

        abort_if($project->workspace_id !== $employee->workspace_id, 403, 'غير مسموح لك بإنشاء مهام لهذا المشروع.');
        abort_if(!$employee->hasRole('employee'), 422, 'الموظف المحدد غير صالح أو ليس لديه دور الموظف');

        $task = Task::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'dueDate' => $request->dueDate,
            'start_date' => $request->start_date,
            'position' => 1000,
            'project_id' => $request->project_id,
            'employee_id' => $request->employee_id,
        ]);

        return ApiResponse::success($task, 'تم إنشاء المهمة بنجاح', 201);
    }

    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($id);
        
        abort_unless(
            $user->hasAnyRole(['admin', 'manager']) || $user->hasPermissionTo('updateTask'),
            403,
            'غير مصرح لك بتعديل المهمة'
        );


        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'dueDate' => 'nullable|string',
            'start_date' => 'nullable|string',
            'employee_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('فشل التحقق من البيانات', 422, $validator->errors());
        }

        if ($request->employee_id) {
            $employee = User::findOrFail($request->employee_id);
            abort_if($task->project->workspace_id !== $employee->workspace_id, 403, 'غير مسموح لك بتعيين هذا الموظف');
        }

        $task->update($request->only(['name', 'description', 'status', 'start_date','dueDate', 'employee_id']));

        return ApiResponse::success($task, 'تم تحديث المهمة بنجاح');
    }
    public function updatePosition(Request $request)
    {
        abort_unless(Auth::user()->hasRole(['admin', 'manager']), 403, 'غير مصرح لك');

        $validator = Validator::make($request->all(), [
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.status' => 'required|string',
            'tasks.*.position' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $updatedTasks = [];

        foreach ($request->tasks as $value) {
            try {
                $task = Task::findOrFail($value['id']);
                $task->update([
                    'status' => $value['status'],
                    'position' => $value['position'],
                ]);
                $updatedTasks[] = $task;
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'حدث خطأ أثناء تحديث المهمة',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث المهام بنجاح',
            'tasks' => $updatedTasks
        ]);
    }

    public function destroy(string $id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($id);

        abort_unless($user->hasAnyRole(['admin', 'manager']), 403, 'غير مصرح لك بحذف المهمة');

        $task->delete();

        return ApiResponse::success(null, 'تم حذف المهمة بنجاح');
    }
}
