<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concern;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConcernController extends Controller
{
    public function index()
    {
        $concerns = Concern::latest()->paginate(15);

        return view('admin.concerns.index', compact('concerns'));
    }

    public function create()
    {
        return view('admin.concerns.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:concerns,slug',
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name_en']);
        $data['is_active'] = $request->boolean('is_active', true);

        Concern::create($data);

        return redirect()->route('admin.concerns.index')
            ->with('success', __('admin.concern_created'));
    }

    public function edit(Concern $concern)
    {
        return view('admin.concerns.edit', compact('concern'));
    }

    public function update(Request $request, Concern $concern)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:concerns,slug,' . $concern->id,
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name_en']);
        $data['is_active'] = $request->boolean('is_active');

        $concern->update($data);

        return redirect()->route('admin.concerns.index')
            ->with('success', __('admin.concern_updated'));
    }

    public function destroy(Concern $concern)
    {
        $concern->delete();

        return redirect()->route('admin.concerns.index')
            ->with('success', __('admin.concern_deleted'));
    }
}
