<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    /**
     * Arahkan pengguna ke "rumah" sesuai perannya.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->isStaff()
            ? redirect()->route('backoffice.dashboard')
            : redirect()->route('home');
    }
}
