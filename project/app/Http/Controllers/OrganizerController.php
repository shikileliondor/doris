<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrganizerController extends Controller
{
    /**
     * Show every question to the organizer, or ask for her code first.
     */
    public function show(Request $request): Response
    {
        if (! $request->session()->get('organizer')) {
            return Inertia::render('organizer/unlock');
        }

        return Inertia::render('organizer/questions', [
            'questions' => Question::query()
                ->latest('id')
                ->get(['id', 'body', 'created_at']),
        ]);
    }

    /**
     * Unlock the questions page for this browser when the code matches.
     */
    public function unlock(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code = (string) config('app.organizer_code');

        if ($code === '' || ! hash_equals($code, $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => "Ce code n'est pas le bon.",
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('organizer', true);

        return to_route('organizer.show');
    }
}
