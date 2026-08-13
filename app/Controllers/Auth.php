<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function loginForm()
    {
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->where('username', $username)->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        if ((int) $user['is_active'] === 0) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.');
        }

        session()->set([
            'user_id'   => $user['id'],
            'nama'      => $user['nama'],
            'role'      => $user['role'],
            'line'      => $user['line'],
            'logged_in' => true,
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->remove(['user_id', 'nama', 'role', 'line', 'logged_in']);
        return redirect()->to('/login')->with('success', 'Berhasil memutuskan koneksi. Selamat beristirahat!');
    }
}
