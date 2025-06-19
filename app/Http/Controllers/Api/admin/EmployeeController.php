<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use App\Helpers\ApiResponse;
use App\Models\WorkSpace;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreEmployeeRequest;

class EmployeeController extends Controller
{
    public function index($workspace_id)
    {
        $workspace = WorkSpace::find($workspace_id);
        if (!$workspace) {
            return ApiResponse::error('خطأ في الطلب', 400, []);
        }

        $employees = User::where('workspace_id', $workspace_id)->with('roles')->get();

        return ApiResponse::success($employees, 'تم جلب جميع الموظفين بنجاح.');
    }

    public function store(StoreEmployeeRequest $request, $workspace_id)
    {
        $user = Auth::user();
        abort_if(!$user->hasRole(['admin', 'manager']), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($user->hasRole('manager')) {
            $workspace_id = $user->workspace_id;
        }

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'workspace_id' => $workspace_id,
        ]);

        $newUser->assignRole('employee');

        return ApiResponse::success($newUser, 'تم إنشاء الموظف بنجاح.', 201);
    }

    public function show(string $id)
    {
        $user = Auth::user();
        abort_if(!$user->hasRole(['admin', 'manager']), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $employee = User::role('employee')->findOrFail($id);
        return ApiResponse::success($employee, 'تم جلب بيانات الموظف بنجاح.');
    }

    public function updatePermissions(Request $request, string $id)
    {
        $admin = Auth::user();
        abort_if(!$admin->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = User::findOrFail($id);

        // تحديث الأذونات بشكل كامل
        $employee->syncPermissions($request->permissions);

        return ApiResponse::success([
            'user_id' => $employee->id,
            'new_permissions' => $employee->getPermissionNames()
        ], 'تم تحديث صلاحيات الموظف بنجاح.');
    }

    public function updateRole(Request $request, string $id)
    {
        $admin = Auth::user();
        abort_if(!$admin->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = User::findOrFail($id);

        // تحديث الدور
        $employee->syncRoles([$request->role]);

        return ApiResponse::success([
            'user_id' => $employee->id,
            'new_role' => $request->role
        ], 'تم تحديث دور الموظف بنجاح.');
    }

    public function update(Request $request, string $id)
    {
        abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $employee = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return ApiResponse::success($employee, 'تم تحديث بيانات الموظف بنجاح.');
    }

    public function destroy(string $id)
    {
        abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $employee = User::findOrFail($id);
        $employee->delete();

        return ApiResponse::success(null, 'تم حذف الموظف بنجاح.');
    }
}

// namespace App\Http\Controllers\Api\Admin;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Http\Response;
// use App\Helpers\ApiResponse;
// use App\Models\WorkSpace;
// use Illuminate\Support\Facades\Validator;
// use App\Http\Requests\StoreEmployeeRequest;

// class EmployeeController extends Controller
// {

//     public function index($workspace_id)
//     {
//         // dd($workspace_id);
//         // abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');
//         $workspace = WorkSpace::findOrFail($workspace_id);
//        if(!$workspace){
//         return ApiResponse::error( 'خطأ في الطلب',  400, []);
//        }
//         $employees = User::where('workspace_id',$workspace_id)->with('roles')->get();

//         return ApiResponse::success($employees, 'تم جلب جميع الموظفين بنجاح.');
//     }


//     public function store(StoreEmployeeRequest $request, $workspace_id)
//     {
//         $user = Auth::user();
//         abort_if(!$user->hasRole(['admin', 'manager']), Response::HTTP_FORBIDDEN, '403 Forbidden');

//         if ($user->hasRole('manager')) {
//             $workspace_id = $user->workspace_id;
//         }

//         $newUser = User::create([
//             'name' => $request->name,
//             'email' => $request->email,
//             'password' => Hash::make($request->password),
//             'workspace_id' => $workspace_id,
//         ]);

//         $newUser->assignRole('employee');

//         return ApiResponse::success($newUser, 'تم إنشاء الموظف بنجاح.', 201);
//     }


//     public function show(string $id)
//     {
//         $user = Auth::user();
//         abort_if(!$user->hasRole(['admin', 'manager']), Response::HTTP_FORBIDDEN, '403 Forbidden');
//         $employee = User::role('employee')->findOrFail($id);
//         return ApiResponse::success($employee, 'تم جلب بيانات الموظف بنجاح.');
//     }
//     public function updatePermissions(Request $request, string $id)
//     {
//         $admin = Auth::user();
//         abort_if(!$admin->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

//         $validator = Validator::make($request->all(), [
//             'permissions' => 'required|array', // جعلها مصفوفة
//             'permissions.*' => 'string|exists:permissions,name', // التحقق لكل عنصر
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'خطأ في التحقق من البيانات',
//                 'errors' => $validator->errors()
//             ], 422);
//         }

//         $employee = User::findOrFail($id);

//         // تحديث الصلاحيات باستخدام syncPermissions()
//         $employee->syncPermissions($request->permissions);

//         return response()->json([
//             'status' => true,
//             'message' => 'تم تحديث صلاحيات الموظف بنجاح.',
//             'data' => [
//                 'user_id' => $employee->id,
//                 'new_permissions' => $request->permissions
//             ]
//         ], 200);
//     }
//         public function updateRole(Request $request, string $id)
//     {
//         $admin = Auth::user();
//         abort_if(!$admin->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

//         $validator = Validator::make($request->all(), [
//             'role' => 'required|string|exists:roles,name',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'خطأ في التحقق من البيانات',
//                 'errors' => $validator->errors()
//             ], 422);
//         }

//         $employee = User::findOrFail($id);

//         $employee->roles()->detach();

//         $employee->assignRole($request->role);

//         return response()->json([
//             'status' => true,
//             'message' => 'تم تحديث دور الموظف بنجاح.',
//             'data' => [
//                 'user_id' => $employee->id,
//                 'new_role' => $request->role
//             ]
//         ], 200);
//     }


//     public function update(Request $request, string $id)
//     {
//         abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

//         // $employee = User::role('employee')->findOrFail($id);

//         $validator = Validator::make($request->all(), [
//             'name' => 'required|string|max:255',
//             'email' => 'required|string|email|max:255|unique:users',
//             'password' => 'required|string|min:6',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'خطأ في التحقق من البيانات',
//                 'errors' => $validator->errors()
//             ]);
//         }
//         $employee = User::role($request->role)->findOrFail($id);

//         $data = $request->only(['name', 'email']);
//         if ($request->filled('password')) {
//             $data['password'] = Hash::make($request->password);
//         }

//         $employee->update($data);

//         return ApiResponse::success($employee, 'تم تحديث بيانات الموظف بنجاح.');
//     }

//     public function destroy(string $id)
//     {
//         abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

//         $employee = User::findOrFail($id);
//         $employee->delete();

//         return ApiResponse::success(null, 'تم حذف الموظف بنجاح.');
//     }
// }
