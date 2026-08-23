<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class FaqBotService
{
    private ?Collection $activeFaqs = null;

    public function bootstrap(string $lang = 'ar', array $context = []): array
    {
        $lang = $this->language($lang);
        $productName = trim((string) ($context['product_name'] ?? ''));

        if ($productName !== '') {
            $answer = $lang === 'en'
                ? "I can help you with {$productName}. Choose a question below or type your own question."
                : "فيني ساعدك بخصوص {$productName}. اختاري سؤال من الأسئلة أو اكتبي سؤالك مباشرة.";
        } else {
            $answer = $lang === 'en'
                ? 'Welcome. Choose a question below or type your question, and I will answer from the clinic knowledge base.'
                : 'أهلًا بكِ. اختاري سؤالًا من الأسئلة أو اكتبي سؤالك، وسأجيبك من قاعدة معلومات العيادة.';
        }

        return [
            'answer' => $answer,
            'options' => $this->faqOptions($lang),
        ];
    }

    public function findAnswer(string $query, string $lang = 'ar', array $context = []): array
    {
        $lang = $this->language($lang);
        $query = trim($query);

        if ($query === '' || $this->isStartQuery($query)) {
            return $this->bootstrap($lang, $context);
        }

        $faq = $this->findMatchingFaq($query);

        if ($faq) {
            $excludedQuestions = array_merge(
                $context['asked_questions'] ?? [],
                [$faq->question_ar, $faq->question_en]
            );

            return [
                'answer' => $lang === 'en' ? $faq->answer_en : $faq->answer_ar,
                'options' => $this->faqOptions($lang, $excludedQuestions),
                'faq_id' => $faq->id,
            ];
        }

        if (trim((string) ($context['product_name'] ?? '')) !== '') {
            return $this->productFallback($lang, $context, $query);
        }

        return [
            'answer' => $lang === 'en'
                ? 'I could not find an exact answer in the clinic knowledge base. Try one of the available questions or book a consultation for a personalized answer.'
                : 'لم أجد جوابًا مطابقًا ضمن قاعدة معلومات العيادة. جرّبي أحد الأسئلة المتاحة أو احجزي استشارة للحصول على جواب مخصص لحالتك.',
            'options' => $this->faqOptions($lang, $context['asked_questions'] ?? []),
        ];
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

    private function faqOptions(string $lang, array $excludedQuestions = []): array
    {
        $excluded = collect($excludedQuestions)
            ->filter(fn ($question) => is_string($question) && trim($question) !== '')
            ->map(fn (string $question) => $this->normalize($question))
            ->all();

        $options = $this->faqs()
            ->map(fn (Faq $faq) => $lang === 'en' ? $faq->question_en : $faq->question_ar)
            ->filter(fn ($question) => is_string($question) && trim($question) !== '')
            ->reject(fn (string $question) => in_array($this->normalize($question), $excluded, true))
            ->unique(fn (string $question) => $this->normalize($question))
            ->values()
            ->all();

        $options[] = $lang === 'en' ? 'Book Consultation' : 'احجزي استشارة';

        return $options;
    }

    private function faqs(): Collection
    {
        return $this->activeFaqs ??= Faq::query()
            ->where('is_active', true)
            ->latest()
            ->get();
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
            'ابدأ',
            'القائمه',
        ], true);
    }

    private function productFallback(string $lang, array $context, string $query): array
    {
        $productName = trim((string) $context['product_name']);
        $description = trim((string) ($context['product_description'] ?? ''));

        if ($lang === 'en') {
            $answer = "Your question about {$productName} was not found in the clinic knowledge base.";
            if ($description !== '') {
                $answer .= " Product information: {$description}";
            }
            $answer .= ' Choose one of the available questions or book a consultation for personalized advice.';
        } else {
            $answer = "لم أجد جوابًا مطابقًا لسؤالك عن {$productName} ضمن قاعدة معلومات العيادة.";
            if ($description !== '') {
                $answer .= " معلومات المنتج: {$description}";
            }
            $answer .= ' اختاري أحد الأسئلة المتاحة أو احجزي استشارة لنصيحة مخصصة.';
        }

        return [
            'answer' => $answer,
            'options' => $this->faqOptions(
                $lang,
                array_merge($context['asked_questions'] ?? [], [$query])
            ),
        ];
    }
}
