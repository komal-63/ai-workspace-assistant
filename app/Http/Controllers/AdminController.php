<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Services\AdminDashboardService;

class AdminController extends Controller
{
     public function __construct(
        private AdminDashboardService $dashboardService
    ) {
    }
    
    public function dashboard()
    {
        $stats = $this->dashboardService->getStats();

        return view('admin.dashboard', $stats);
    }
    public function users()
    {
        Gate::authorize('viewAny', User::class);
        $users=User::all();
        return view('admin.users', compact('users'));
    }

    public function showUser(User $user)
    {
        Gate::authorize('view', $user);

        return view('admin.user-details', compact('user'));
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $request->validate([
            'role' => ['required', 'in:admin,manager,user'],
        ]);

        $user->update([
            'role' => $request->role,
        ]);
        $this->dashboardService->clearStatsCache();
        return redirect()
            ->route('admin.users')
            ->with('success', 'User role updated successfully.');
    }

    public function deleteUser(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();
        $this->dashboardService->clearStatsCache();
        return redirect()
            ->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }
}
