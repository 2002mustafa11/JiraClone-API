<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkSpace;
use Illuminate\Support\Facades\Auth;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Response;
use App\Http\traits\UploadFile;
use App\Models\Project;


class WorkSpaceController extends Controller
{
    use UploadFile;
    public function index()
    {
        $user = Auth::user();

        // abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (!$user->hasRole(['employee','manager'])){

            $workspaces = $this->getworkSpaces();
        }else{
            $workspaces = WorkSpace::where('id', $user->workspace_id)->with('employees')->get();

        }
        // dd($workspaces);
        // if (!$workspaces ||  Auth::user()->name =='SuperAdmin') {
        //     return ApiResponse::success(WorkSpace::get(),'تم جلب جميع المساحات بنجاح.');
        // }else {
        //     return ApiResponse::error('ليس لديك الصلاحية للوصول إلى هذا المورد.', 403);
        // }

        return ApiResponse::success($workspaces, 'تم جلب جميع المساحات بنجاح.');
    }

    public function store(Request $request)
    {
        // abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image',
            // |max:2848
        ]);
        // dd('');
        $admin = Auth::user();
        if (!$admin) {
            return ApiResponse::error('يجب تسجيل الدخول.', 401);
        }
        if ($request->hasFile('image')) {
            $image = $request->image;
            $filename=$this->UploadImage($image,'workspace/images');
        }else{
            $filename = rand(1,5).'.jpg';
        }


        $workspace = WorkSpace::create([
            'name' => $request->name,
            'admin_id' => $admin->id,
            'image' => $filename,
        ]);

        return ApiResponse::success($workspace, 'تم إنشاء WorkSpace بنجاح.', 201);
    }

    public function show($id)
    {
        $user = Auth::user();

        $workspace = WorkSpace::with(['projects', 'employees'])->findOrFail($id);

        // if ($user->hasRole('employee')) {
        //     $projects = Project::where('workspace_id', $workspace->id)
        //         ->whereHas('tasks', function ($query) use ($user) {
        //             $query->where('employee_id', $user->id);
        //         })
        //         ->with(['tasks' => function ($query) use ($user) {
        //             $query->where('employee_id', $user->id);
        //         }])
        //         ->get();

        //     return ApiResponse::success([
        //         'workspace' => $workspace,
        //         'projects' => $projects
        //     ], 'تم جلب WorkSpace والمشاريع والمهام الخاصة بالموظف بنجاح.');
        // }

        return ApiResponse::success( $workspace, 'تم جلب WorkSpace بنجاح.');
    }


    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',
        ]);


        $workspace = WorkSpace::findOrFail($id);

        $filename = $workspace->image;

        if ($request->hasFile('image')) {
            if ($workspace->image) {
                $this->DeleteImage($workspace->image, 'workspace/images');
            }
            $filename = $this->UploadImage($request->image, 'workspace/images');
        }elseif(!$filename){
            $filename = rand(1, 5).'.jpg';
        }

        $workspace->update([
            'name' => $request->name ?? $workspace->name,
            'image' => $filename,
        ]);

        return ApiResponse::success($workspace, 'تم تحديث WorkSpace بنجاح.');
    }


    public function destroy($id)
    {
        $workspace = WorkSpace::findOrFail($id);
        $workspace->delete();
        if ($workspace->image) {
            $this->DeleteImage($workspace->image,'workspace/images');

        }
        return ApiResponse::success(null, 'تم حذف WorkSpace بنجاح.');
    }

    private function getworkSpaces()
    {

        return WorkSpace::where('admin_id', Auth::id())->with('employees','projects')->get();
    }
}
