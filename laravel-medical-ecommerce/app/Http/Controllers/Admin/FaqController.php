<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::latest()->paginate(15);
        return view('admin.faqs.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_ar' => 'required|string',
            'question_en' => 'required|string',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'keywords' => 'nullable|string',
        ]);

        Faq::create($data);
        return back()->with('success', 'FAQ added successfully');
    }

    public function destroy($id)
    {
        Faq::findOrFail($id)->delete();
        return back()->with('success', 'FAQ deleted successfully');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'question_ar' => 'required|string|max:255',
            'question_en' => 'required|string|max:255',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'keywords' => 'nullable|string',
        ]);

        Faq::findOrFail($id)->update($data);

        return back()->with('success', 'FAQ updated successfully');
    }
}
