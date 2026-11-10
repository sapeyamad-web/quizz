<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use Illuminate\Http\Request;

class ExamLandingController extends Controller
{
    public function __invoke(Request $request)
    {
        $sessions = ExamSession::where('status', 'published')
            ->withCount('questions')
            ->latest()
            ->get();

        return view('home', compact('sessions'));
    }
}
