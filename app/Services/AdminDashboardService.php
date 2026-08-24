<?php

namespace App\Services;
use Illuminate\Support\Facades\Cache;

use App\Models\User;

class AdminDashboardService
{
    public function getStats(): array
    {
        return Cache::remember(
            'admin.dashboard.stats',
            300,
            function () {
                $totalUsers = User::count();
                $totalAdmins = User::where('role', 'admin')->count();
                $totalManagers = User::where('role', 'manager')->count();

                return [
                    'totalUsers' => $totalUsers,
                    'totalAdmins' => $totalAdmins,
                    'totalManagers' => $totalManagers,
                    'totalNormalUsers' => $totalUsers - $totalAdmins - $totalManagers,
                ];
            }
        );
    }

    public function clearStatsCache(): void
    {
        Cache::forget('admin.dashboard.stats');
    }
   
}