<?php

namespace Feature\Mail;

use App\Mail\AccountDeletionMail;
use Tests\TestCase;

class AccountDeletionMailTest extends TestCase
{
    public function test_account_deletion_mail_has_correct_subject(): void
    {
        $mail = new AccountDeletionMail('test@example.com');

        $mail->assertHasSubject('Your Wedge Matrix Account Has Been Deleted');
    }

    public function test_account_deletion_mail_contains_expected_content(): void
    {
        $mail = new AccountDeletionMail('test@example.com');

        $mail->assertSeeInHtml('Account Deleted');
        $mail->assertSeeInHtml('permanently deleted');
    }
}
