<?php

namespace Feature\Mail;

use App\Mail\AccountDeletionMail;
use Tests\TestCase;

class AccountDeletionMailTest extends TestCase
{
    public function test_has_correct_subject_line(): void
    {
        $mail = new AccountDeletionMail;

        $mail->assertHasSubject('Your Wedge Matrix Account Has Been Deleted');
    }

    public function test_renders_markdown_content(): void
    {
        $mail = new AccountDeletionMail;

        $rendered = $mail->render();

        $this->assertStringContainsString('Account Deleted', $rendered);
        $this->assertStringContainsString('permanently deleted', $rendered);
    }
}
