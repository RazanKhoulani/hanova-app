<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QadmousLocation;
use Illuminate\Http\Request;

class QadmousLocationController extends Controller
{
    public function index()
    {
        return view('admin.qadmous-locations.index', [
            'locations' => QadmousLocation::query()
                ->orderBy('sort_order')->orderBy('governorate_ar')->orderBy('branch_ar')->paginate(30),
        ]);
    }

    public function store(Request $request)
    {
        QadmousLocation::create($this->validatedData($request));
        return back()->with('success', __('admin.qadmous_location_created'));
    }

    public function update(Request $request, QadmousLocation $qadmous_location)
    {
        $qadmous_location->update($this->validatedData($request));
        return back()->with('success', __('admin.qadmous_location_updated'));
    }

    public function destroy(QadmousLocation $qadmous_location)
    {
        $qadmous_location->delete();
        return back()->with('success', __('admin.qadmous_location_deleted'));
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'governorate_ar' => 'required|string|max:100',
            'governorate_en' => 'nullable|string|max:100',
            'branch_ar' => 'required|string|max:150',
            'branch_en' => 'nullable|string|max:150',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        return $data;
    }
}
