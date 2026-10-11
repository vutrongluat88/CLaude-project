<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Headline numbers for the dashboard cards.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_users' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
            ],
        ]);
    }
}
