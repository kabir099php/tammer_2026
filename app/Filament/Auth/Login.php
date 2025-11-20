<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    /**
     * Override the heading (title) of the login form.
     */
    public function getHeading(): string|Htmlable
    {
        // Example of a custom heading
        return __('Vendor Portal Login');
    }

    /**
     * Override the subheading (optional text below the heading).
     */
    public function getSubHeading(): string|Htmlable|null
    {
        // Example of a custom subheading
        return __('Sign in to manage your vendor account and stock.');
    }

    // You can also override the form() method here to add, remove, or modify fields.
}