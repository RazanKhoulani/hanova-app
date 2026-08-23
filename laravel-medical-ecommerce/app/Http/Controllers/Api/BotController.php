<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotConversation;
use App\Models\Patient;
use App\Models\User;
use App\Services\FaqBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotController extends Controller
{
    protected FaqBotService $botService;

    public function __construct(FaqBotService $botService)
    {
        $this->botService = $botService;
    }

    public function bootstrap(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'nullable|string|max:255',
            'product_description' => 'nullable|string|max:2000',
        ]);
        $lang = $this->language($request);

        return response()->json([
            'success' => true,
            'data' => $this->botService->bootstrap($lang, $validated),
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string',
            'text' => 'nullable|string',
            'lang' => 'nullable|in:ar,en',
            'option_type' => 'nullable|in:topic,faq,topics,book_consultation',
            'option_id' => 'nullable|integer|min:1',
            'context' => 'nullable|array',
            'context.product_name' => 'nullable|string|max:255',
            'context.product_description' => 'nullable|string|max:2000',
            'context.conversation_id' => 'nullable|integer|exists:bot_conversations,id',
            'context.asked_questions' => 'nullable|array',
            'context.asked_questions.*' => 'string|max:500',
        ]);

        $lang = $this->language($request);
        $query = $request->input('query', $request->input('text', ''));

        if (! $query) {
            return response()->json([
                'success' => false,
                'message' => 'Please type your question.',
            ], 422);
        }

        $context = $request->input('context', []);
        $user = $this->optionalAuthenticatedUser();
        $conversation = $user ? $this->resolveConversation($user, $context, $lang) : null;

        if ($conversation) {
            $conversation->messages()->create([
                'sender' => 'user',
                'body' => $query,
                'metadata' => [
                    'lang' => $lang,
                    'context' => $this->persistableContext($context),
                ],
            ]);

            $context['asked_questions'] = $this->askedQuestionsFor($conversation, $context);
        }

        $result = $this->botService->findAnswer(
            $query,
            $lang,
            $context,
            $request->input('option_type'),
            $request->filled('option_id') ? $request->integer('option_id') : null
        );

        if ($result) {
            if ($conversation) {
                $botMessage = $conversation->messages()->create([
                    'sender' => 'bot',
                    'body' => (string) ($result['answer'] ?? ''),
                    'options' => $result['options'] ?? [],
                    'metadata' => array_filter([
                        'lang' => $lang,
                        'view' => $result['view'] ?? null,
                        'topic_id' => $result['topic_id'] ?? null,
                        'faq_id' => $result['faq_id'] ?? null,
                        'option_items' => $result['option_items'] ?? [],
                    ], fn ($value) => $value !== null),
                ]);

                $conversation->forceFill([
                    'locale' => $lang,
                    'context' => $this->mergedConversationContext($conversation, $context),
                ])->save();

                $result['conversation_id'] = $conversation->id;
                $result['message_id'] = $botMessage->id;
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Sorry, I could not find an exact answer. You can continue with support chat.',
        ]);
    }

    public function conversation(Request $request)
    {
        $request->validate([
            'product_name' => 'nullable|string|max:255',
            'product_description' => 'nullable|string|max:2000',
        ]);

        $lang = $this->language($request);

        $conversation = $this->resolveConversation(
            $request->user(),
            $request->only(['product_name', 'product_description']),
            $lang
        );

        return response()->json([
            'success' => true,
            'data' => $this->conversationPayload($conversation),
        ]);
    }

    private function optionalAuthenticatedUser(): ?User
    {
        return Auth::guard('sanctum')->user();
    }

    private function language(Request $request): string
    {
        $lang = $request->input('lang', $request->header('Accept-Language', 'ar'));

        return str_starts_with((string) $lang, 'en') ? 'en' : 'ar';
    }

    private function resolveConversation(User $user, array $context, string $lang): BotConversation
    {
        $conversationId = $context['conversation_id'] ?? null;

        if ($conversationId) {
            $conversation = BotConversation::where('user_id', $user->id)
                ->where('id', $conversationId)
                ->first();

            if ($conversation) {
                return $conversation;
            }
        }

        $conversation = BotConversation::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $patient = Patient::where('user_id', $user->id)->latest()->first();

        return BotConversation::create([
            'user_id' => $user->id,
            'patient_id' => $patient?->id,
            'locale' => $lang,
            'status' => 'active',
            'context' => $this->persistableContext($context),
        ]);
    }

    private function askedQuestionsFor(BotConversation $conversation, array $context): array
    {
        $persistedQuestions = $conversation->messages()
            ->where('sender', 'user')
            ->pluck('body')
            ->all();

        return collect($context['asked_questions'] ?? [])
            ->merge($persistedQuestions)
            ->filter(fn ($question) => is_string($question) && trim($question) !== '')
            ->map(fn (string $question) => trim($question))
            ->unique()
            ->values()
            ->all();
    }

    private function persistableContext(array $context): array
    {
        return collect($context)
            ->only(['product_name', 'product_description'])
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value) => trim($value))
            ->all();
    }

    private function mergedConversationContext(BotConversation $conversation, array $context): array
    {
        return array_filter(
            array_merge($conversation->context ?? [], $this->persistableContext($context)),
            fn ($value) => $value !== null && $value !== ''
        );
    }

    private function conversationPayload(BotConversation $conversation): array
    {
        $messages = $conversation->messages()->oldest()->get();
        $lastBotMessageId = $messages
            ->filter(fn ($message) => $message->sender === 'bot')
            ->last()?->id;

        return [
            'id' => $conversation->id,
            'messages' => $messages->map(function ($message) use ($lastBotMessageId) {
                $showOptions = $message->sender === 'bot' && $message->id === $lastBotMessageId;
                $metadata = $message->metadata ?? [];

                return [
                    'id' => $message->id,
                    'text' => $message->body,
                    'body' => $message->body,
                    'sender' => $message->sender,
                    'is_me' => $message->sender === 'user',
                    'options' => $showOptions ? ($message->options ?? []) : [],
                    'option_items' => $showOptions ? ($metadata['option_items'] ?? []) : [],
                    'view' => $metadata['view'] ?? null,
                    'topic_id' => $metadata['topic_id'] ?? null,
                    'faq_id' => $metadata['faq_id'] ?? null,
                    'created_at' => $message->created_at?->toISOString(),
                ];
            })->values(),
        ];
    }
}
