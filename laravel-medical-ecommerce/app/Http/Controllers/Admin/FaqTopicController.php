<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqTopicController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name_en']);

        FaqTopic::create($data);

        return back()->with('success', __('admin.topic_added_successfully'));
    }

    public function update(Request $request, FaqTopic $faqTopic)
    {
        $faqTopic->update($this->validated($request));

        return back()->with('success', __('admin.topic_updated_successfully'));
    }

    public function destroy(FaqTopic $faqTopic)
    {
        if ($faqTopic->faqs()->exists()) {
            return back()->with('error', __('admin.topic_has_questions'));
        }

        $faqTopic->delete();

        return back()->with('success', __('admin.topic_deleted_successfully'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:1000'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'topic';
        $slug = $base;
        $suffix = 2;

        while (FaqTopic::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
