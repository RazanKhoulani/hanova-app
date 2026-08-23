<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Services\FaqService;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    protected FaqService $faqService;

    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    /**
     * Display a listing of FAQs.
     */
    public function index()
    {
        $faqs = $this->faqService->getActiveFaqs();

        return FaqResource::collection($faqs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'faq_topic_id' => 'nullable|integer|exists:faq_topics,id',
            'question_ar' => 'required|string|max:255',
            'question_en' => 'required|string|max:255',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'keywords' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $faq = $this->faqService->createFaq($validated);

        return new FaqResource($faq);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'faq_topic_id' => 'nullable|integer|exists:faq_topics,id',
            'question_ar' => 'sometimes|string|max:255',
            'question_en' => 'sometimes|string|max:255',
            'answer_ar' => 'sometimes|string',
            'answer_en' => 'sometimes|string',
            'keywords' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $faq = $this->faqService->updateFaq($id, $validated);

        return new FaqResource($faq);
    }

    public function destroy($id)
    {
        $this->faqService->deleteFaq($id);

        return response()->json(['message' => 'FAQ deleted successfully'], 204);
    }
}
