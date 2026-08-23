<?php

namespace Tests\Unit;

use App\Services\FirebaseMessagingService;
use PHPUnit\Framework\TestCase;

class FirebaseMessagingServiceTest extends TestCase
{
    public function test_it_normalizes_a_bom_prefixed_and_wrapped_base64_credential(): void
    {
        $service = new class extends FirebaseMessagingService {
            public function normalize(string $credentials): string
            {
                return $this->normalizeBase64Credentials($credentials);
            }
        };

        $credentials = base64_encode('{"type":"service_account"}');

        $this->assertSame(
            $credentials,
            $service->normalize("\u{FEFF}\n{$credentials}\r\n"),
        );
    }
}
