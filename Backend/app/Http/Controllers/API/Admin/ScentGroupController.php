<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScentGroupRequest;
use App\Models\ScentGroup;
use Illuminate\Http\Request;

class ScentGroupController extends Controller
{
    // GET // http://localhost:8000/api/admin/scent-groups
    public function index()
    {
        $scentGroups = ScentGroup::orderBy('id', 'desc')->get();
        return response()->json($scentGroups, 200);
    }

    // GET // http://localhost:8000/api/admin/scent-groups/trashed
    public function trashed()
    {
        $trashed = ScentGroup::onlyTrashed()->orderBy('id', 'desc')->get();
        return response()->json($trashed, 200);
    }

    // POST // http://localhost:8000/api/admin/scent-groups
    public function store(ScentGroupRequest $request)
    {
        $data = $request->validated();
        $scentGroup = ScentGroup::create($data);

        return response()->json([
            'message' => 'Thêm Scent Group thành công',
            'scent_group' => $scentGroup,
        ], 201);
    }

    // GET // http://localhost:8000/api/admin/scent-groups/{id}
    public function show(string $id)
    {
        $scentGroup = ScentGroup::findOrFail($id);
        return response()->json($scentGroup, 200);
    }

    // PUT // http://localhost:8000/api/admin/scent-groups/{id}
    public function update(ScentGroupRequest $request, string $id)
    {
        $scentGroup = ScentGroup::findOrFail($id);
        $data = $request->validated();
        $scentGroup->update($data);

        return response()->json([
            'message' => 'Cập nhật Scent Group thành công',
            'scent_group' => $scentGroup,
        ], 200);
    }

    // DELETE (soft) // http://localhost:8000/api/admin/scent-groups/{id}
    public function destroy(string $id)
    {
        $scentGroup = ScentGroup::findOrFail($id);
        $scentGroup->delete();

        return response()->json([
            'message' => 'Xóa mềm Scent Group thành công',
        ], 200);
    }

    // PUT // http://localhost:8000/api/admin/scent-groups/{id}/restore
    public function restore(string $id)
    {
        $scentGroup = ScentGroup::onlyTrashed()->findOrFail($id);
        $scentGroup->restore();

        return response()->json([
            'message' => 'Khôi phục Scent Group thành công',
        ], 200);
    }

    // DELETE (force) // http://localhost:8000/api/admin/scent-groups/{id}/force
    public function forceDelete(string $id)
    {
        $scentGroup = ScentGroup::onlyTrashed()->findOrFail($id);
        $scentGroup->forceDelete();

        return response()->json([
            'message' => 'Xóa vĩnh viễn Scent Group thành công',
        ], 200);
    }
}
