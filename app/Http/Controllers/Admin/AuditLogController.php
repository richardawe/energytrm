<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('type')) {
            $query->where('auditable_type', 'like', '%' . $request->type . '%');
        }

        $logs  = $query->paginate(40)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.audit.index', compact('logs', 'users'));
    }
}
