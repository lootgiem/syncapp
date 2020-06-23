<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;

class CredentialException extends Exception
{
    public static function badRequest()
    {
        return ValidationException::withMessages(['input' => 'Please fill in all the required fields related to the credential authentication.']);
    }

    public static function wrongCredential()
    {
        return ValidationException::withMessages(['input' => 'We are not able to get you connected to this platform with these credentials.']);
    }
}
