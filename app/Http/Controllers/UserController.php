<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon; // ✅ السطر ده مكانه هنا فوق خالص

class UserController extends Controller
{
    /**
     * عرض كل المستخدمين
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * عرض مستخدم واحد بالتفصيل
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found', 'id' => $id], 404);
        }

        return response()->json($user);
    }

    /**
     * تحديث بيانات مستخدم
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->has('type')) {
            $user->type = $request->type;
        }

        if ($request->has('password') && !empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * إنشاء مستخدم جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type ?? 'user',
        ]);

        return response()->json($user, 201);
    }

    /**
     * حذف مستخدم
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * جلب إحصائيات لوحة التحكم
     */
    public function getDashboardStats()
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalUsers'     => User::count(),
                    'adminsCount'    => User::where('type', 'admin')->count(),
                    'todayNewUsers'  => User::whereDate('created_at', Carbon::today())->count(),
                    'lastMonthUsers' => User::where('created_at', '>=', Carbon::now()->subMonth())->count(),
                    'activeUsers'    => User::count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
