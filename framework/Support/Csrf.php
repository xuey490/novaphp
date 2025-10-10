<?php

namespace Framework\Support;

class Csrf
{
    const SESSION_KEY = '_token';

    public static function generate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY] = $token;
        return $token;
    }

    public static function validate($token)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return hash_equals($_SESSION[self::SESSION_KEY] ?? '', $token);
    }

    public static function tokenField()
    {
        return '<input type="hidden" name="_token" value="' . self::generate() . '">';
    }
}