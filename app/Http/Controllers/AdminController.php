<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalManagers = User::where('role', 'manager')->count();
        $totalNormalUsers = $totalUsers - $totalAdmins - $totalManagers;

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalManagers' => $totalManagers,
            'totalNormalUsers' => $totalNormalUsers,
        ]);
    }
}
