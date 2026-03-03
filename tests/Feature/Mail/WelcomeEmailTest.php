<?php

namespace Feature\Mail;

use App\Mail\WelcomeEmail;
use Tests\TestCase;

class WelcomeEmailTest extends TestCase
{
    public function test_has_correct_subject_line(): void
    {
        $mail = new WelcomeEmail;

        $mail->assertHasSubject('Welcome to Wedge Matrix!');
    }

    public function test_renders_markdown_content(): void
    {
        $mail = new WelcomeEmail;

        $rendered = $mail->render();

        $this->assertStringContainsString('Welcome to Wedge Matrix', $rendered);
        $this->assertStringContainsString('Happy pin hunting', $rendered);
    }
}
