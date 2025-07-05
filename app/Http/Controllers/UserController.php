<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    function register(Request $req)
    {
        // Cek apakah email sudah ada di database
        $existingUser = User::where('email', $req->input('email'))->first();
        
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terdaftar'
            ], 400);
        }

        $user = new User;
        $user->nama = $req->input('nama');
        $user->email = $req->input('email');
        $user->password = Hash::make($req->input('password'));
        $user->phone = $req->input('phone');
        $user->address = $req->input('address');
        $user->status = $req->input('status');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user' => $user
        ], 201);
    }
    // UserController.php

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'image' => 'required|string'
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['error' => 'Pengguna tidak ditemukan'], 404);
        }
    
        // Simpan base64 string langsung ke database
        $user->pictures = $request->image;
        $user->save();
    
        return response()->json([
            'success' => true, 
            'image_url' => $user->pictures
        ]);
    }

   
    public function login(Request $req)
    {
        $req->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $req->email)->first();

        if (!$user || !Hash::check($req->password, $user->password)) {
            return response()->json(["Error" => "Maaf, email atau password tidak cocok"], 401);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'status' => $user->status,
            'token' => $token,
            'user' => $user
        ], 200);
    }

    public function deleteUser($id)
    {
        try {
            // Cari user berdasarkan ID
            $user = User::find($id);

            // Periksa apakah user ada
            if (!$user) {
                return response()->json(["Error" => "Pengguna tidak ditemukan"], 404);
            }

            // Hapus user
            $user->delete();

            // Kembalikan response sukses
            return response()->json(["Message" => "Pengguna berhasil dihapus"], 200);
        } catch (\Exception $e) {
            // Cek apakah error disebabkan oleh foreign key constraint
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                return response()->json([
                    "Error" => "Tidak dapat menghapus pengguna",
                    "reason" => "Pengguna memiliki riwayat transaksi"
                ], 422);
            }
            
            return response()->json([
                "Error" => "Terjadi kesalahan saat menghapus pengguna",
                "message" => $e->getMessage()
            ], 500);
        }
    }
    public function index()
    {
        // Fetch all users from the database
        $users = User::all();

        // Return users as JSON response
        return response()->json($users);
    }
    public function getoneuser($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(["Error" => "Pengguna tidak ditemukan"], 404);
        }

        return response()->json($user);
    }
    public function getpicturebyemail($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(["Error" => "Pengguna tidak ditemukan"], 404);
        }
        return response()->json([
            'picture' => $user->pictures
        ]);
    }

    public function updateUserProfile(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(["Error" => "Pengguna tidak ditemukan"], 404);
        }

        // Hanya memperbarui field yang dikirim dalam request
        if ($request->has('nama')) {
            $user->nama = $request->nama;
        }
        if ($request->has('address')) {
            $user->address = $request->address; 
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => $user
        ], 200);
    }

    public function updateUserStatus(Request $request, $email)
    {
        try {
            $user = User::where('email', $email)->first();

            // if (!$user) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'User tidak ditemukan'
            //     ], 404);
            // }

            // $validatedData = $request->validate([
            //     'status' => 'required|integer|in:0,1'
            // ]);

            // $user->status = $validatedData['status'];
            // $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Status user berhasil diperbarui',
                'user' => $user
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating pengguna status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui status pengguna',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // PERBAIKAN FUNCTION FORGOT PASSWORD
public function forgotPassword(Request $request, $email){
    try {
        // Validasi email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Format email tidak valid'
            ], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        // Generate OTP 6 digit
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Set expired time (15 menit)
        $otpExpired = now()->addMinutes(10);
        
        // Update OTP di database - WAJIB DIAKTIFKAN!
        $user->otp = $otp;
        $user->otp_expired_at = $otpExpired; // pastikan kolom ini ada di database
        $user->save(); // PENTING: jangan dikomentari!

        // Data untuk email template
        $mailData = [
            'otp' => $otp,
            'email' => $email,
            'user_name' => $user->name ?? 'User',
            'expired_time' => '10 menit'
        ];

        // Simpan OTP ke database
        // $user->save();

        // Kirim email OTP menggunakan Mail facade
        try {
            Mail::send('emails.otp', $mailData, function($message) use ($email) {
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Nunis Warung & Koffie'))
                        ->to($email)
                        ->subject('Kode OTP Reset Password - Nunis Warung & Koffie');
            });

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda',
                'email' => $email
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error sending OTP: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim OTP. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

    } catch (\Exception $e) {
        \Log::error('Error sending OTP: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengirim OTP. Silakan coba lagi.',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}


// PERBAIKAN FUNCTION VERIFY OTP
public function verifyOTP(Request $request)
{
    try {
        // Validasi input
        $validatedData = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        // Check OTP
        if (!$user->otp || $user->otp !== $validatedData['otp']) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid'
            ], 400);
        }

        // Check expired (jika ada kolom otp_expired_at)
        if ($user->otp_expired_at && $user->otp_expired_at < now()) {
            // Clear expired OTP
            $user->otp = null;
            $user->otp_expired_at = null;
            $user->save();
            
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah expired. Silakan minta OTP baru.'
            ], 400);
        }

        // OTP valid, clear dari database
        $user->otp = null;
        $user->otp_expired_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil diverifikasi',
            'email' => $user->email
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Error verifying OTP: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat verifikasi OTP',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}
public function resetPassword(Request $request)
{
    try {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        // Update password
        $user->password = Hash::make($validatedData['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset'
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Error resetting password: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mereset password',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}


    
}