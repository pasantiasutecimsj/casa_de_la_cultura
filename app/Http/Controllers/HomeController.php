<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): RedirectResponse|Response
    {
        return Auth::check()
            ? redirect()->route('dashboard')
            : Inertia::render('Welcome');
    }
}
