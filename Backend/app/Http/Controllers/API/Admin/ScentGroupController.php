<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScentGroupRequest;
use App\Models\ScentGroup;
use Illuminate\Http\Request;

class ScentGroupController extends Controller
{

    // GET //  http://localhost:8000/api/scent-groups
    public function index()
    {
        $scentGroups = ScentGroup::orderBy('id', 'desc')->paginate(15);
        return response()->json($scentGroups, 200);
    }


    public function store(ScentGroupRequest $request)
    {
        $data = $request->validated();
        $scentGroup = ScentGroup::create($data);
        return response()->json([
            'message' => 'Thêm Scent Group thành công',
            'scent_group' => $scentGroup,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $scentGroup = ScentGroup::findOrFail($id);
        return response()->json($scentGroup, 200);
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $scentGroup = ScentGroup::findOrFail($id);
         $scentGroup->delete();
         return response()->json([
             'message' => 'Xóa Scent Group thành công',
         ], 200);
    }
}
