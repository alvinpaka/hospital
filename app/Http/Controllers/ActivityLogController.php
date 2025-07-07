<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $logs = ActivityLog::with('user')->latest()->limit(100)->get();
        return response()->json($logs);
    }
} 