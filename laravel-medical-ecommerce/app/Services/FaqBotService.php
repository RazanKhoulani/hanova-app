<?php

namespace App\Services;

use App\Models\Faq;
use App\Models\FaqTopic;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class FaqBotService
{
    private ?Collection $activeFaqs = null;

    private ?Collection $activeTopics = null;

    public function bootstrap(string $lang = 'ar', array $context = []): array
    {
        $lang = $this->language($lang);
        $productName = trim((string) ($context['product_name'] ?? ''));
        $productDescription = trim((string) ($context['product_description'] ?? ''));

        if ($productName !== '') {
            $answer = $lang === 'en'
                ? "Product: {$productName}".($productDescription !== '' ? "\n\n{$productDescription}" : '')."\n\nChoose a relevant consultation topic or book a consultation for personalized advice."
                : "المنتج: {$productName}".($productDescription !== '' ? "\n\n{$productDescription}" : '')."\n\nاختاري موضوع الاستشارة المناسب أو احجزي استشارة لنصيحة مخصصة.";
        } else {
            $answer = $lang === 'en'
                ? 'Choose the consultation topic that best matches your concern, then I will show you its questions.'
                : 'اختاري موضوع الاستشارة الأقرب لمشكلتك، وبعدها سأعرض لكِ الأسئلة الخاصة بهذا الموضوع.';
        }

        return $this->payload(
            $answer,
            $this->withBooking($this->topicItems($lang), $lang),
            ['view' => 'topics']
        );
    }

    public function findAnswer(
        string $query,
        string $lang = 'ar',
        array $context = [],
        ?string $optionType = null,
        ?int $optionId = null
    ): array {
        $lang = $this->language($lang);
        $query = trim($query);

        if ($optionType === 'topics' || $query === '' || $this->isStartQuery($query) || $this->isTopicsQuery($query)) {
            return $this->bootstrap($lang, $context);
        }

        if ($optionType === 'topic' && $optionId) {
            $topic = $this->topics()->firstWhere('id', $optionId);

            return $topic
                ? $this->topicPayload($topic, $lang)
                : $this->notFoundPayload($lang, $context);
        }

        if ($optionType === 'faq' && $optionId) {
            $faq = $this->faqs()->firstWhere('id', $optionId);

            return $faq
                ? $this->faqPayload($faq, $lang, $context)
                : $this->notFoundPayload($lang, $context);
        }

        $topic = $this->findMatchingTopic($query);
        if ($topic) {
            return $this->topicPayload($topic, $lang);
        }

        $faq = $this->findMatchingFaq($query);
        if ($faq) {
            return $this->faqPayload($faq, $lang, $context);
        }

        if (trim((string) ($context['product_name'] ?? '')) !== '') {
            return $this->productFallback($lang, $context);
        }

        return $this->notFoundPayload($lang, $context);
    }

    private function topicPayload(FaqTopic $topic, string $lang): array
    {
        $name = $this->topicName($topic, $lang);
        $description = trim((string) ($lang === 'en' ? $topic->description_en : $topic->description_ar));
        $answer = $lang === 'en'
            ? "Choose a question about {$name}."
            : "اختاري السؤال الذي يهمك ضمن موضوع {$name}.";

        if ($description !== '') {
            $answer .= "\n{$description}";
        }

        return $this->payload(
            $answer,
            $this->withNavigation($this->faqItems($topic, $lang), $lang),
            [
                'view' => 'questions',
                'topic_id' => $topic->id,
            ]
        );
    }

    private function faqPayload(Faq $faq, string $lang, array $context): array
    {
        $topic = $faq->topic;
        $excludedQuestions = array_merge(
            $context['asked_questions'] ?? [],
            [$faq->question_ar, $faq->question_en]
        );

        $items = $topic
            ? $this->faqItems($topic, $lang, $excludedQuestions)
            : [];

        return $this->payload(
            $lang === 'en' ? $faq->answer_en : $faq->answer_ar,
            $this->withNavigation($items, $lang),
            [
                'view' => 'questions',
                'topic_id' => $topic?->id,
                'faq_id' => $faq->id,
            ]
        );
    }

    private function notFoundPayload(string $lang, array $context): array
    {
        return $this->payload(
            $lang === 'en'
                ? 'I could not find an exact answer in the clinic knowledge base. Choose a consultation topic or book a consultation for a personalized answer.'
                : 'لم أجد جوابًا مطابقًا ضمن قاعدة معلومات العيادة. اختاري موضوع الاستشارة المناسب أو احجزي استشارة للحصول على جواب مخصص لحالتك.',
            $this->withBooking($this->topicItems($lang), $lang),
            ['view' => 'topics']
        );
    }

    private function payload(string $answer, array $items, array $meta = []): array
    {
        return array_merge([
            'answer' => $answer,
            'options' => collect($items)->pluck('label')->values()->all(),
            'option_items' => array_values($items),
        ], $meta);
    }

    private function topicItems(string $lang): array
    {
        return $this->topics()
            ->map(fn (FaqTopic $topic) => [
                'type' => 'topic',
                'id' => $topic->id,
                'label' => $this->topicName($topic, $lang),
            ])
            ->values()
            ->all();
    }

    private function faqItems(FaqTopic $topic, string $lang, array $excludedQuestions = []): array
    {
        $excluded = collect($excludedQuestions)
            ->filter(fn ($question) => is_string($question) && trim($question) !== '')
            ->map(fn (string $question) => $this->normalize($question))
            ->all();

        return $this->faqs()
            ->where('faq_topic_id', $topic->id)
            ->reject(function (Faq $faq) use ($excluded) {
                return in_array($this->normalize($faq->question_ar), $excluded, true)
                    || in_array($this->normalize($faq->question_en), $excluded, true);
            })
            ->map(fn (Faq $faq) => [
                'type' => 'faq',
                'id' => $faq->id,
                'topic_id' => $topic->id,
                'label' => $lang === 'en' ? $faq->question_en : $faq->question_ar,
            ])
            ->values()
            ->all();
    }

    private function withNavigation(array $items, string $lang): array
    {
        $items[] = [
            'type' => 'topics',
            'id' => null,
            'label' => $lang === 'en' ? 'Back to consultation topics' : 'العودة لمواضيع الاستشارة',
        ];

        return $this->withBooking($items, $lang);
    }

    private function withBooking(array $items, string $lang): array
    {
        $items[] = [
            'type' => 'book_consultation',
            'id' => null,
            'label' => $lang === 'en' ? 'Book Consultation' : 'احجزي استشارة',
        ];

        return $items;
    }

    private function findMatchingTopic(string $query): ?FaqTopic
    {
        $normalizedQuery = $this->normalize($query);

        return $this->topics()->first(function (FaqTopic $topic) use ($normalizedQuery) {
            return in_array($normalizedQuery, [
                $this->normalize($topic->name_ar),
                $this->normalize($topic->name_en),
                $this->normalize($topic->slug),
            ], true);
        });
    }

    private function findMatchingFaq(string $query): ?Faq
    {
        $normalizedQuery = $this->normalize($query);

        if ($normalizedQuery === '') {
            return null;
        }

        $bestMatch = $this->faqs()
            ->map(fn (Faq $faq) => [
                'faq' => $faq,
                'score' => $this->matchScore($faq, $normalizedQuery),
            ])
            ->filter(fn (array $candidate) => $candidate['score'] > 0)
            ->sortByDesc('score')
            ->first();

        return $bestMatch['faq'] ?? null;
    }

    private function matchScore(Faq $faq, string $query): int
    {
        $score = 0;

        foreach ([$faq->question_ar, $faq->question_en] as $question) {
            $normalizedQuestion = $this->normalize((string) $question);

            if ($normalizedQuestion === '') {
                continue;
            }

            if ($query === $normalizedQuestion) {
                $score = max($score, 1000);
            } elseif (mb_strlen($normalizedQuestion) >= 4 && str_contains($query, $normalizedQuestion)) {
                $score = max($score, 850 + min(mb_strlen($normalizedQuestion), 100));
            } elseif (mb_strlen($query) >= 4 && str_contains($normalizedQuestion, $query)) {
                $score = max($score, 700 + min(mb_strlen($query), 100));
            }
        }

        foreach ($this->keywords($faq) as $keyword) {
            $normalizedKeyword = $this->normalize($keyword);

            if ($normalizedKeyword === '') {
                continue;
            }

            if ($query === $normalizedKeyword) {
                $score = max($score, 950);
            } elseif (mb_strlen($normalizedKeyword) >= 3 && str_contains($query, $normalizedKeyword)) {
                $score = max($score, 800 + min(mb_strlen($normalizedKeyword), 100));
            } elseif (mb_strlen($query) >= 4 && str_contains($normalizedKeyword, $query)) {
                $score = max($score, 650 + min(mb_strlen($query), 100));
            }
        }

        return $score;
    }

    private function topics(): Collection
    {
        return $this->activeTopics ??= FaqTopic::query()
            ->where('is_active', true)
            ->whereHas('faqs', fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function faqs(): Collection
    {
        return $this->activeFaqs ??= Faq::query()
            ->where('is_active', true)
            ->whereHas('topic', fn ($query) => $query->where('is_active', true))
            ->with('topic')
            ->orderBy('faq_topic_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function topicName(FaqTopic $topic, string $lang): string
    {
        return $lang === 'en' ? $topic->name_en : $topic->name_ar;
    }

    private function keywords(Faq $faq): array
    {
        return preg_split('/[,،\r\n]+/u', (string) $faq->keywords, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = str_replace(['أ', 'إ', 'آ', 'ٱ'], 'ا', $value);
        $value = preg_replace('/\p{Mn}+/u', '', $value) ?? $value;
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value) ?? $value;

        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }

    private function language(string $lang): string
    {
        return str_starts_with($lang, 'en') ? 'en' : 'ar';
    }

    private function isStartQuery(string $query): bool
    {
        return in_array($this->normalize($query), [
            'start',
            'menu',
            'hello',
            'hi',
            'مرحبا',
            'اهلا',
            'ابدا',
            'القائمه',
        ], true);
    }

    private function isTopicsQuery(string $query): bool
    {
        return in_array($this->normalize($query), [
            'topics',
            'back to consultation topics',
            'العوده لمواضيع الاستشاره',
            'مواضيع الاستشاره',
        ], true);
    }

    private function productFallback(string $lang, array $context): array
    {
        $productName = trim((string) $context['product_name']);
        $description = trim((string) ($context['product_description'] ?? ''));

        if ($lang === 'en') {
            $answer = "Your question about {$productName} was not found in the clinic knowledge base.";
            if ($description !== '') {
                $answer .= " Product information: {$description}";
            }
            $answer .= ' Choose a consultation topic or book a consultation for personalized advice.';
        } else {
            $answer = "لم أجد جوابًا مطابقًا لسؤالك عن {$productName} ضمن قاعدة معلومات العيادة.";
            if ($description !== '') {
                $answer .= " معلومات المنتج: {$description}";
            }
            $answer .= ' اختاري موضوع الاستشارة أو احجزي استشارة لنصيحة مخصصة.';
        }

        return $this->payload(
            $answer,
            $this->withBooking($this->topicItems($lang), $lang),
            ['view' => 'topics']
        );
    }
}
