<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Patient;
use App\Models\PatientMedicalFact;
use Illuminate\Support\Facades\Schema;

class PatientMedicalFactExtractor
{
    public function extractFromMessage(Message $message): void
    {
        if (!Schema::hasTable('patient_medical_facts')) {
            return;
        }

        $message->loadMissing('conversation.user');
        if ((int) $message->sender_id !== (int) $message->conversation->user_id) {
            return;
        }

        $patient = Patient::firstOrCreate(
            ['user_id' => $message->conversation->user_id],
            [
                'name' => $message->conversation->user?->name ?? 'Patient',
                'phone' => $message->conversation->user?->phone ?? '',
            ]
        );

        $body = trim((string) $message->body);
        if ($body === '') {
            return;
        }

        foreach ($this->matchedFacts($body) as $key => $confidence) {
            PatientMedicalFact::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'source_message_id' => $message->id,
                    'key' => $key,
                ],
                [
                    'user_id' => $patient->user_id,
                    'value' => $body,
                    'confidence' => $confidence,
                    'status' => 'suggested',
                ]
            );
        }
    }

    private function matchedFacts(string $body): array
    {
        $normalized = mb_strtolower($body);
        $rules = [
            'allergy' => ['حساسية', 'تحسس', 'allergy', 'allergic'],
            'hormonal_issue' => ['هرمون', 'هرمونات', 'تكيس', 'hormone', 'pcos'],
            'pregnancy' => ['حامل', 'حمل', 'pregnant', 'pregnancy'],
            'medication' => ['دواء', 'دوا', 'علاج', 'حبوب', 'medication', 'medicine', 'dose'],
            'chronic_condition' => ['سكري', 'ضغط', 'غدة', 'thyroid', 'diabetes', 'hypertension'],
            'skin_reaction' => ['حكة', 'احمرار', 'تهيج', 'حرقان', 'itch', 'redness', 'irritation'],
        ];

        $matches = [];
        foreach ($rules as $key => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, mb_strtolower($keyword))) {
                    $matches[$key] = 0.70;
                    break;
                }
            }
        }

        return $matches;
    }
}
