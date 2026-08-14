<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperOrAdminUID = $user && (
            in_array(trim($user->role_name ?? ''), ['Super Admin', 'Perencanaan UID', 'super admin', 'superadmin']) || 
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Perencanaan UID']))
        );

        $query = Activity::with('causer')->latest();

        if (!$isSuperOrAdminUID) {
            $query->where(function ($q) {
                $q->whereNull('causer_id')
                  ->orWhereHasMorph('causer', [\App\Models\User::class], function ($q2) {
                      $q2->whereNotIn('role_name', ['Super Admin', 'Perencanaan UID', 'super admin', 'superadmin']);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhereHasMorph('causer', [\App\Models\User::class], function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $logs = $query->paginate(15)->withQueryString();

        return view('audit-logs.index', compact('logs'));
    }
}
