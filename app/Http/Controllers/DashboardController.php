<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Router;
use App\Models\User;
use App\Models\UserPppoe;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            // Super Admin Dashboard
            $stats = [
                'total_routers' => Router::count(),
                'active_routers' => Router::where('status', 'active')->count(),
                'total_users' => User::where('id', '!=', $user->id)->count(),
                'total_pppoe_secrets' => UserPppoe::count(),
            ];
            
            $recent_pppoe = UserPppoe::with(['user', 'router'])
                ->latest()
                ->take(5)
                ->get();
                
            return view('dashboard.super_admin', compact('stats', 'recent_pppoe'));
        } else {
            // Regular Admin Dashboard
            $userRouters = $user->routers()->where('status', 'active')->get();
            $routerIds = $userRouters->pluck('id');
            
            $stats = [
                'accessible_routers' => $userRouters->count(),
                'my_pppoe_secrets' => UserPppoe::where('user_id', $user->id)->count(),
            ];
            
            $recent_pppoe = UserPppoe::with(['router'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
                
            return view('dashboard.admin', compact('stats', 'recent_pppoe', 'userRouters'));
        }
    }
}
