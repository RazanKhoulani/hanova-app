<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Notification as AppNotification;
use Illuminate\Support\Facades\Log;
use JsonException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Throwable;

class FirebaseMessagingService
{
    private Messaging|false|null $messaging = null;

    public function sendNotification(AppNotification $notification): void
    {
        try {
            $messaging = $this->messaging();
            if ($messaging === false) {
                return;
            }

            $query = DeviceToken::query();
            if ($notification->user_id !== null) {
                $query->where('user_id', $notification->user_id);
            }

            $data = $this->stringifyData([
                'notification_id' => $notification->id,
                'type' => $notification->type,
                ...($notification->data ?? []),
            ]);

            $message = CloudMessage::new()
                ->withNotification(FirebaseNotification::create(
                    $notification->title,
                    $notification->body,
                ))
                ->withData($data);

            $query->pluck('token')->chunk(500)->each(function ($tokens) use ($messaging, $message) {
                $report = $messaging->sendMulticast($message, $tokens->all());
                $invalidTokens = array_values(array_unique([
                    ...$report->invalidTokens(),
                    ...$report->unknownTokens(),
                ]));

                if ($invalidTokens !== []) {
                    DeviceToken::query()->whereIn('token', $invalidTokens)->delete();
                }
            });
        } catch (Throwable $exception) {
            Log::warning('Firebase notification delivery failed.', [
                'notification_id' => $notification->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function messaging(): Messaging|false
    {
        if ($this->messaging !== null) {
            return $this->messaging;
        }

        $credentials = config('services.firebase.credentials_base64');
        $credentialsPath = config('services.firebase.credentials_path');

        if (is_string($credentials) && $credentials !== '') {
            $decoded = base64_decode($this->normalizeBase64Credentials($credentials), true);
            if ($decoded === false) {
                Log::warning('FIREBASE_CREDENTIALS_BASE64 is not valid base64.');

                return $this->messaging = false;
            }

            try {
                $serviceAccount = json_decode($decoded, true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                Log::warning('Firebase service account JSON is invalid.', [
                    'error' => $exception->getMessage(),
                ]);

                return $this->messaging = false;
            }
        } elseif (is_string($credentialsPath) && $credentialsPath !== '') {
            $serviceAccount = $credentialsPath;
        } else {
            Log::notice('Firebase messaging is disabled because credentials are not configured.');

            return $this->messaging = false;
        }

        $factory = (new Factory)->withServiceAccount($serviceAccount);
        $projectId = config('services.firebase.project_id');
        if (is_string($projectId) && $projectId !== '') {
            $factory = $factory->withProjectId($projectId);
        }

        return $this->messaging = $factory->createMessaging();
    }

    /**
     * Railway variables can be pasted with a UTF-8 BOM or line wrapping.
     * Neither is part of the Base64 payload, so remove both before decoding.
     */
    protected function normalizeBase64Credentials(string $credentials): string
    {
        $withoutBom = preg_replace('/^\x{FEFF}/u', '', $credentials) ?? $credentials;

        return preg_replace('/\s+/u', '', $withoutBom) ?? $withoutBom;
    }

    /**
     * Firebase data messages only accept string values.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function stringifyData(array $data): array
    {
        return collect($data)
            ->mapWithKeys(function ($value, $key) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif (! is_scalar($value) && $value !== null) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                return [(string) $key => (string) ($value ?? '')];
            })
            ->all();
    }
}
