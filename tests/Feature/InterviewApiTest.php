<?php

namespace Tests\Feature;

use App\Mail\AppointmentNotification;
use App\Models\PersonalTrait;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InterviewApiTest extends TestCase
{
    use RefreshDatabase;

    private $secret = 'super-secret-worker-key';

    protected function setUp(): void
    {
        parent::setUp();
        // Set the env/config WORKER_SECRET for the test environment
        Config::set('app.worker_secret', $this->secret);
        putenv("WORKER_SECRET={$this->secret}");
    }

    /**
     * Test signature middleware blocks requests without header.
     */
    public function test_signature_middleware_blocks_without_header(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401)
                 ->assertJsonFragment(['error' => 'Unauthorized. Missing security signature.']);
    }

    /**
     * Test signature middleware blocks request with invalid signature.
     */
    public function test_signature_middleware_blocks_invalid_signature(): void
    {
        $response = $this->withHeaders([
            'X-Worker-Signature' => 'wrong-signature-value'
        ])->getJson('/api/profile');

        $response->assertStatus(401)
                 ->assertJsonFragment(['error' => 'Unauthorized. Invalid signature.']);
    }

    /**
     * Test signature middleware blocks request if WORKER_SECRET is not configured (Fail Close).
     */
    public function test_signature_middleware_fail_close_when_secret_missing(): void
    {
        // Unset secret
        Config::set('app.worker_secret', null);
        putenv("WORKER_SECRET=");

        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret
        ])->getJson('/api/profile');

        $response->assertStatus(401)
                 ->assertJsonFragment(['error' => 'Unauthorized. Server security configuration missing.']);
    }

    /**
     * Test signature middleware allows requests with valid signature.
     */
    public function test_signature_middleware_allows_valid_signature(): void
    {
        // Seed a trait
        PersonalTrait::create([
            'category' => 'about_me',
            'content' => 'Test content'
        ]);

        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret
        ])->getJson('/api/profile');

        $response->assertStatus(200)
                 ->assertJsonStructure(['profile' => ['name', 'title', 'traits']]);
    }

    /**
     * Test project lookup endpoint with valid and invalid name, verifying SQL Injection safety.
     */
    public function test_project_endpoint_and_sqli_safety(): void
    {
        // Create test project
        Project::create([
            'name' => 'laravel-ticket',
            'title' => 'Test Project',
            'tech_stack' => 'Laravel 12',
            'challenge' => 'None',
            'solution' => 'None',
            'achievement' => '100%'
        ]);

        // 1. Valid lookup
        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret
        ])->getJson('/api/projects/laravel-ticket');

        $response->assertStatus(200)
                 ->assertJsonPath('project.title', 'Test Project');

        // 2. Lookup non-existent project returns 404
        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret
        ])->getJson('/api/projects/non-existent');

        $response->assertStatus(404);

        // 3. SQL Injection lookup attempt: checks if the ORM handles values safely via parameterized binding
        // If SQLi works, it might bypass or trigger an SQL syntax error, which shouldn't happen.
        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret
        ])->getJson("/api/projects/laravel-ticket' OR '1'='1");

        $response->assertStatus(404)
                 ->assertJsonFragment(['error' => 'Project not found.']);
    }

    /**
     * Test HR appointment validation blocks incorrect parameters.
     */
    public function test_hr_appointment_validation(): void
    {
        // Missing company_name and invalid date format
        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret
        ])->postJson('/api/appointments', [
            'hr_name' => 'Jane Doe',
            'interview_time' => 'not-a-date',
            'contact_info' => 'jane@example.com'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['company_name', 'interview_time']);
    }

    /**
     * Test HR appointment creates record and sends notification mail.
     */
    public function test_hr_appointment_creates_record_and_sends_mail(): void
    {
        Mail::fake();

        $appointmentData = [
            'hr_name' => 'John HR',
            'company_name' => 'Tech Corp',
            'interview_time' => now()->addDays(2)->toDateTimeString(),
            'contact_info' => 'john@techcorp.com'
        ];

        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret
        ])->postJson('/api/appointments', $appointmentData);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true);

        // Assert database record exists
        $this->assertDatabaseHas('interviews', [
            'hr_name' => 'John HR',
            'company_name' => 'Tech Corp',
            'contact_info' => 'john@techcorp.com'
        ]);

        // Assert mail was sent to s9231158@gmail.com
        Mail::assertSent(AppointmentNotification::class, function ($mail) {
            return $mail->hasTo('s9231158@gmail.com');
        });
    }

    /**
     * Test Gemini proxy route validation and integration.
     */
    public function test_gemini_proxy_behavior(): void
    {
        // 1. Without signature, should be blocked by middleware
        $response = $this->postJson('/api/gemini-proxy', [
            'model' => 'gemini-3.1-flash-lite',
            'contents' => [['role' => 'user', 'parts' => [['text' => 'Hello']]]]
        ]);
        $response->assertStatus(401);

        // 2. With signature but without key, should return 400
        Config::set('app.gemini_key', null);
        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret
        ])->postJson('/api/gemini-proxy', [
            'model' => 'gemini-3.1-flash-lite',
            'contents' => [['role' => 'user', 'parts' => [['text' => 'Hello']]]]
        ]);
        $response->assertStatus(400)
                 ->assertJsonFragment(['error' => 'Gemini API key is required.']);

        // 3. With signature and key, HTTP client calls are faked and proxies response
        \Illuminate\Support\Facades\Http::fake([
            'generativelanguage.googleapis.com/*' => \Illuminate\Support\Facades\Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Faked response']],
                        'role' => 'model'
                    ]
                ]]
            ], 200)
        ]);

        $response = $this->withHeaders([
            'X-Worker-Signature' => $this->secret,
            'X-Gemini-Key' => 'test-gemini-key'
        ])->postJson('/api/gemini-proxy', [
            'model' => 'gemini-3.1-flash-lite',
            'contents' => [['role' => 'user', 'parts' => [['text' => 'Hello']]]]
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('candidates.0.content.parts.0.text', 'Faked response');
    }
}
