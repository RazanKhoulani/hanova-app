<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqTopic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::with('topic')
            ->orderBy('faq_topic_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);
        $topics = FaqTopic::withCount('faqs')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.faqs.index', compact('faqs', 'topics'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'faq_topic_id' => ['required', 'integer', 'exists:faq_topics,id'],
            'question_ar' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question_ar')],
            'question_en' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question_en')],
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'keywords' => 'nullable|string',
            'is_active' => 'required|boolean',
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
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
            'faq_topic_id' => ['required', 'integer', 'exists:faq_topics,id'],
            'question_ar' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question_ar')->ignore($faq->id)],
            'question_en' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question_en')->ignore($faq->id)],
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'keywords' => 'nullable|string',
            'is_active' => 'required|boolean',
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
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
