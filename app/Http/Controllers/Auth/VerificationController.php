<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    // Tampilkan pemberitahuan verifikasi
    public function show()
    {
        return view('Auth.verify-email');
    }

    // Proses verifikasi ketika user klik link di email
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect('/dashboard')->with('success', 'Email berhasil diverifikasi!');
    }

    // Kirim ulang email verifikasi
    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Link verifikasi telah dikirim ulang ke email Anda!');
    }
}
