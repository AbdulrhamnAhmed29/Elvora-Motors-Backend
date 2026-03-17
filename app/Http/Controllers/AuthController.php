<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HttpResponses;

    // جلب كل اليوزرز
    public function index()
    {
        return response()->json(User::all());
    }

    // جلب يوزر واحد
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'Not Found'], 404);
        return response()->json($user);
    }

    // إضافة يوزر جديد من لوحة التحكم (Store)
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type ?? 'user', // السطر ده اللي كان ناقص
        ]);
        return response()->json($user, 201);
    }

    // تحديث يوزر
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'type' => $request->type ?? $user->type, // السطر ده عشان يحدث النوع
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);
        return response()->json($user);
    }

    // مسح يوزر
    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }

    // --- تسجيل الدخول ---
    public function login(LoginRequest $request)
{
    // 1. التحقق من صحة الإيميل والباسورد
    if (!Auth::attempt($request->only(['email', 'password']))) {
        return $this->error('', 'بيانات الدخول غير صحيحة', 401);
    }

    // 2. نجيب بيانات اليوزر من الداتا بيز بعد ما اتأكدنا من الباسورد
    $user = User::where('email', $request->email)->first();

    // 3. (الزتونة هنا) نأكد إن النوع اللي باعتة في اللوجين هو نفسه اللي متسجل عندنا
    // لو اليوزر بيحاول يدخل كـ Admin وهو متسجل User، نرفض الدخول
    if ($request->has('type') && $user->type !== $request->type) {
        return $this->error('', 'عفواً، نوع الحساب غير مطابق لبياناتنا', 401);
    }

    // 4. لو كله تمام، نطلّع التوكن
    return $this->sucess([
        'user' => $user,
        'token' => $user->createToken('Api Token of ' . $user->name)->plainTextToken
    ]);
}

    // --- تسجيل حساب جديد (Register) ---
    public function register(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type ?? 'user', // السطر ده مهم جداً لموقعك
        ]);
        return $this->sucess([
            'user' => $user,
            'token' => $user->createToken('Api Token of' . $user->name)->plainTextToken
        ]);
    }
public function logout(Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ], 200);
}
}
