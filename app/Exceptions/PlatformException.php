<?php

namespace App\Exceptions;

use Exception;

class PlatformException extends Exception
{
    public static function failConnect($credential)
    {
        if (!is_null($credential) && $credential->valid !== true) {
            return ['input' => 'This credential : ' . $credential->name . ' is not yet valid.'];
        }

        return ['input' => 'The connection to ' . $credential->name . ' is not possible.'];
    }
}
