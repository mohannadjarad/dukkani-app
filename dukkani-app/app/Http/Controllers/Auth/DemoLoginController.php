<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        abort_unless(config('app.demo_mode'), 404);

        $user = User::where('email', 'demo@dukkani.jo')->firstOrFail();

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'أهلاً بك في العرض التجريبي لمتجر أمل بيكنز.');
    }
}
