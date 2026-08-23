<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'question_ar' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question_ar')],
            'question_en' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question_en')],
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'keywords' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);
        $data['keywords'] = $this->normalizeKeywords($data['keywords'] ?? null);

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
        $faq = Faq::findOrFail($id);
        $data = $request->validate([
            'question_ar' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question_ar')->ignore($faq->id)],
            'question_en' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question_en')->ignore($faq->id)],
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'keywords' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);
        $data['keywords'] = $this->normalizeKeywords($data['keywords'] ?? null);

        $faq->update($data);

        return back()->with('success', 'FAQ updated successfully');
    }

    private function normalizeKeywords(?string $keywords): ?string
    {
        if ($keywords === null || trim($keywords) === '') {
            return null;
        }

        $items = preg_split('/[,،\r\n]+/u', $keywords, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $items = collect($items)
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique(fn (string $keyword) => mb_strtolower($keyword))
            ->values()
            ->all();

        return $items === [] ? null : implode(', ', $items);
    }
}
