<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Traits\AdminCrudTrait;

class UserController extends BaseController
{
    use AdminCrudTrait;
    protected UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function index()
    {
        return view('admin/user/index', [
            'title'  => 'Master User',
            'daftar' => $this->model->orderBy('nama', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('admin/user/form', [
            'title' => 'Tambah User',
            'user'  => null,
        ]);
    }

    public function store()
    {
        $rules = $this->rules();
        $rules['username']   = 'required|max_length[50]|is_unique[users.username]';
        $rules['password']   = 'required|min_length[6]';

        if (! $this->validate($rules)) {
            return $this->redirectValidationError();
        }

        $this->model->insert([
            'nama'     => $this->request->getPost('nama'),
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'),
            'line'     => $this->request->getPost('line') ?: null,
        ]);

        return $this->redirectSuccess('/admin/user', 'User berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $user = $this->model->find($id);
        if (! $user) {
            return $this->redirectNotFound('/admin/user', 'User');
        }

        return view('admin/user/form', [
            'title' => 'Edit User',
            'user'  => $user,
        ]);
    }

    public function update(int $id)
    {
        $existing = $this->model->find($id);
        if (! $existing) {
            return $this->redirectNotFound('/admin/user', 'User');
        }

        $rules = $this->rules();
        $rules['username'] = "required|max_length[50]|is_unique[users.username,id,{$id}]";
        // password opsional saat edit (kosong = tidak diubah)
        if ($this->request->getPost('password') !== '') {
            $rules['password'] = 'min_length[6]';
        }

        if (! $this->validate($rules)) {
            return $this->redirectValidationError();
        }

        $data = [
            'nama'     => $this->request->getPost('nama'),
            'username' => $this->request->getPost('username'),
            'role'     => $this->request->getPost('role'),
            'line'     => $this->request->getPost('line') ?: null,
        ];

        if ($this->request->getPost('password') !== '') {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $this->model->update($id, $data);

        return $this->redirectSuccess('/admin/user', 'User berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ((int) $id === (int) session()->get('user_id')) {
            return $this->redirectError('/admin/user', 'Tidak bisa menghapus akun yang sedang login.');
        }

        if (! $this->model->find($id)) {
            return $this->redirectNotFound('/admin/user', 'User');
        }

        try {
            $this->model->delete($id);
            return $this->redirectSuccess('/admin/user', 'User berhasil dihapus.');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return $this->redirectError('/admin/user', 'User ini tidak bisa dihapus karena memiliki data transaksi atau riwayat pengecekan terkait.');
        }
    }

    public function export()
    {
        $users = $this->model->orderBy('nama', 'ASC')->findAll();
        
        $filename = 'users_export_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, ['Nama', 'Username', 'Role', 'Line', 'Password']);
        
        foreach ($users as $u) {
            fputcsv($output, [
                $u['nama'],
                $u['username'],
                $u['role'],
                $u['line'] ?? '',
                '' // Password dikosongkan saat ekspor demi keamanan
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function import()
    {
        $file = $this->request->getFile('file_csv');
        if (! $file || ! $file->isValid() || $file->getExtension() !== 'csv') {
            return redirect()->to('/admin/user')->with('error', 'Silakan pilih file CSV yang valid.');
        }
        
        $filePath = $file->getTempName();
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Lewati header row
            fgetcsv($handle);
            
            $successInsert = 0;
            $successUpdate = 0;
            $errors = [];
            $rowNum = 1;
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (count($row) < 3) {
                    $errors[] = "Baris {$rowNum}: Kolom kurang lengkap. Harus memuat Nama, Username, dan Role.";
                    continue;
                }
                
                $nama     = trim($row[0]);
                $username = trim($row[1]);
                $role     = strtolower(trim($row[2]));
                $line     = isset($row[3]) ? trim($row[3]) : '';
                $password = isset($row[4]) ? trim($row[4]) : '';
                
                if (empty($nama) || empty($username) || empty($role)) {
                    $errors[] = "Baris {$rowNum}: Kolom Nama, Username, dan Role tidak boleh kosong.";
                    continue;
                }
                
                if (! in_array($role, [Role::Magang->value, Role::Member->value, Role::Sheadprd->value, Role::Sheadmtc->value, Role::Admin->value, Role::Leader->value], true)) {
                    $errors[] = "Baris {$rowNum}: Role '{$role}' tidak valid. Harus Role::Magang->value, Role::Member->value, Role::Sheadprd->value, Role::Sheadmtc->value, Role::Admin->value, atau 'leader'.";
                    continue;
                }
                
                $existing = $this->model->where('username', $username)->first();
                
                if ($existing) {
                    $updateData = [
                        'nama'   => $nama,
                        'role'   => $role,
                        'line'   => empty($line) ? null : $line,
                    ];
                    if (! empty($password)) {
                        $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
                    }
                    $this->model->update($existing['id'], $updateData);
                    $successUpdate++;
                } else {
                    $passToSave = ! empty($password) ? $password : 'password123';
                    $this->model->insert([
                        'nama'     => $nama,
                        'username' => $username,
                        'role'     => $role,
                        'line'     => empty($line) ? null : $line,
                        'password' => password_hash($passToSave, PASSWORD_DEFAULT),
                    ]);
                    $successInsert++;
                }
            }
            
            fclose($handle);
            
            $msg = "Impor selesai. Ditambahkan: {$successInsert}, Diperbarui: {$successUpdate}.";
            if (! empty($errors)) {
                $msg .= " Beberapa baris dilewati:\n" . implode("\n", $errors);
                return redirect()->to('/admin/user')->with('error', $msg);
            }
            
            return redirect()->to('/admin/user')->with('success', $msg);
        }
        
        return redirect()->to('/admin/user')->with('error', 'Gagal membuka file CSV.');
    }

    private function rules(): array
    {
        return [
            'nama'   => 'required|max_length[100]',
            'role'   => 'required|in_list[magang,member,sheadprd,sheadmtc,admin,leader]',
            'line'   => 'permit_empty|in_list[Line 1,Line 2,Line 3,CG,Second]',
        ];
    }
}
