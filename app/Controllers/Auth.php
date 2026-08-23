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
            'user_id'    => $user['id'],
            'nama'       => $user['nama'],
            'role'       => $user['role'],
            'line'       => $user['line'],
            'departemen' => $user['departemen'] ?? null,
            'plant'      => $user['plant'] ?? null,
            'logged_in'  => true,
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->remove(['user_id', 'nama', 'role', 'line', 'logged_in']);
        return redirect()->to('/login')->with('success', 'Berhasil memutuskan koneksi. Selamat beristirahat!');
    }

    public function gantiPasswordForm()
    {
        $data = [
            'title' => 'Ganti Password',
        ];
        return view('auth/ganti_password', $data);
    }

    public function updatePassword()
    {
        $passwordLama = $this->request->getPost('password_lama');
        $passwordBaru = $this->request->getPost('password_baru');
        $konfirmasiPassword = $this->request->getPost('konfirmasi_password');

        if (strlen($passwordBaru) < 6) {
            return redirect()->back()->with('error', 'Password baru minimal harus 6 karakter.');
        }

        if ($passwordBaru !== $konfirmasiPassword) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak cocok dengan password baru.');
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user || !password_verify($passwordLama, $user['password'])) {
            return redirect()->back()->with('error', 'Password lama yang Anda masukkan salah.');
        }

        $userModel->update($userId, [
            'password' => password_hash($passwordBaru, PASSWORD_DEFAULT),
        ]);

        return redirect()->back()->with('success', 'Password Anda berhasil diperbarui.');
    }
}
