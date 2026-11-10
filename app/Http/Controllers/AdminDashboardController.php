<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $sessions = ExamSession::withCount(['questions', 'attempts'])->latest()->get();

        return view('admin.dashboard', compact('sessions'));
    }
}
