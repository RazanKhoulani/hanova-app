<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_public_landing_page_uses_hanova_branding(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Hanova | Beauty, Clinic & Care')
            ->assertSee('Open Hanova Dashboard')
            ->assertDontSee('Laravel Logo');
    }

    public function test_the_admin_login_uses_hanova_branding(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Hanova')
            ->assertSee('Tajawal-Regular.ttf');
    }
}
