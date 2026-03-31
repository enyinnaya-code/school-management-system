<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->get('per_page', 50), [25, 50, 100, 200])
            ? (int) $request->get('per_page', 50)
            : 50;

        $query = DB::table('activity_log')
            ->orderBy('created_at', 'desc');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('action')) {
            $query->where('description', 'like', $request->action . '%');
        }

        // Snapshot causer IDs before paginating
        $causerIds = (clone $query)->pluck('causer_id')->unique()->filter();
        $causers   = User::whereIn('id', $causerIds)->get()->keyBy('id');

        $logs = $query->paginate($perPage)->withQueryString();

        $logs->through(function ($log) {
            $log->properties = json_decode($log->properties ?? '{}');
            return $log;
        });

        return view('activity_log.index', compact('logs', 'causers'));
    }

    public function clear()
    {
        DB::table('activity_log')->delete();
        return back()->with('success', 'Activity log cleared successfully.');
    }
}