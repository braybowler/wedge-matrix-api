<?php

namespace Feature\Mail;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_mail_has_correct_subject(): void
    {
        $user = User::factory()->create();

        $mail = new WelcomeMail($user);

        $mail->assertHasSubject('Welcome to Wedge Matrix');
    }

    public function test_welcome_mail_contains_expected_content(): void
    {
        $user = User::factory()->create();

        $mail = new WelcomeMail($user);

        $mail->assertSeeInHtml('Welcome to Wedge Matrix');
        $mail->assertSeeInHtml('tracking your wedge yardages');
    }
}
