<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetEmail;
use App\Mail\UserRegistrationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    // MAIL_MAILER=smtp
    // MAIL_HOST=mail.saraglobals.com
    // MAIL_PORT=465
    // MAIL_USERNAME=support@saraglobals.com
    // MAIL_PASSWORD=dPTTW.~e,6CC
    // MAIL_ENCRYPTION=tls
    // MAIL_FROM_ADDRESS=support@saraglobals.com
    // MAIL_FROM_NAME="SaraGlobal"

    public static function send($registeredBy, $receiverName, $receiverEmail, $role, $password)
    {
        $newMail = new \stdClass();
        $newMail->registeredBy = $registeredBy;
        $newMail->receiverName = $receiverName;
        $newMail->receiverEmail = $receiverEmail;
        $newMail->role = strtoupper($role);
        $newMail->password = $password;
 
        Mail::to($receiverEmail)->send(new UserRegistrationEmail($newMail));
    }

    public static function sendPasswordResetMail($email, $newPassword)
    {
        $newMail = new \stdClass();
        $newMail->email = $email;
        $newMail->password = $newPassword;
 
        Mail::to($email)->send(new PasswordResetEmail($newMail));
    }
}
