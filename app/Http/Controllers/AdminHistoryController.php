<?php

namespace App\Http\Controllers;

use App\Models\AdminHistory;
use App\Models\User;
use Illuminate\Http\Request;

class AdminHistoryController extends Controller
{
    public function index(Request $request)
    {
        $sort_search = $request->search;
        $user_id = $request->user_id;

        $histories = AdminHistory::with('user')
            ->when($user_id, function ($q, $id) {
                return $q->where('user_id', $id);
            })
            ->when($sort_search, function ($q, $search) {
                return $q->where('url', 'like', '%' . $search . '%')
                    ->orWhere('ip', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        $users = User::whereIn('user_type', ['admin', 'staff'])
            ->whereHas('adminHistories')
            ->orderBy('name')
            ->get();

        return view('backend.admin_history.index', compact('histories', 'sort_search', 'user_id', 'users'));
    }
}
