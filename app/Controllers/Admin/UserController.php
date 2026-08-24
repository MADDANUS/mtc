<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\LineModel;
use App\Enums\Role;
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
            'daftar' => $this->model
                ->orderBy("FIELD(role, 'admin', 'sheadmtc', 'sheadprd', 'leader', 'leader mtc', 'member', 'magang')")
                ->orderBy('nama', 'ASC')
                ->findAll(),
        ]);
    }

    public function create()
    {
        $lineModel = new LineModel();
        return view('admin/user/form', [
            'title'        => 'Tambah User',
            'user'         => null,
            'linesGrouped' => $lineModel->getLinesGroupedByDepartemen(),
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

        $rolePost = $this->request->getPost('role');
        $roleStr = is_array($rolePost) ? implode(',', $rolePost) : ($rolePost ?: 'magang');
        
        $noAssignmentRoles = ['admin', 'member', 'magang', 'leader mtc'];
        
        // If ALL selected roles are in noAssignmentRoles, then no assignment needed
        $requiresAssignment = false;
        $rolesArray = is_array($rolePost) ? $rolePost : explode(',', $roleStr);
        foreach ($rolesArray as $r) {
            if (!in_array(trim($r), $noAssignmentRoles)) {
                $requiresAssignment = true;
                break;
            }
        }

        if (!$requiresAssignment) {
            $planStr = '-';
            $departemen = '-';
            $lineStr = '-';
        } else {
            $planPost = $this->request->getPost('plant');
            $planStr = is_array($planPost) ? implode(', ', $planPost) : ($planPost ?: '-');
            
            $linePost = $this->request->getPost('line');
            $lineStr = is_array($linePost) ? implode(', ', $linePost) : ($linePost ?: '-');
            
            $departemenPost = $this->request->getPost('departemen');
            $departemen = is_array($departemenPost) ? implode(', ', $departemenPost) : ($departemenPost ?: '-');
        }

        $this->model->insert([
            'nama'       => $this->request->getPost('nama'),
            'username'   => $this->request->getPost('username'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'       => $roleStr,
            'plant'       => $planStr,
            'departemen' => $departemen,
            'line'       => $lineStr,
        ]);

        $idUser = $this->model->getInsertID();

        $logAktivitasModel = new \App\Models\LogAktivitasUserModel();
        $logAktivitasModel->insert([
            'id_user_target' => $idUser,
            'nama_user'      => $this->request->getPost('nama'),
            'aksi'           => 'CREATE',
            'keterangan'     => 'Menambahkan user baru',
            'detail'         => json_encode(['data_baru' => $this->model->find($idUser)]),
            'dilakukan_oleh' => session()->get('user_id'),
            'created_at'     => date('Y-m-d H:i:s')
        ]);

        return $this->redirectSuccess('/admin/user', 'User berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $user = $this->model->find($id);
        if (! $user) {
            return $this->redirectNotFound('/admin/user', 'User');
        }

        $lineModel = new LineModel();
        return view('admin/user/form', [
            'title'        => 'Edit User',
            'user'         => $user,
            'linesGrouped' => $lineModel->getLinesGroupedByDepartemen(),
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

        $rolePost = $this->request->getPost('role');
        $roleStr = is_array($rolePost) ? implode(',', $rolePost) : ($rolePost ?: 'magang');
        
        $noAssignmentRoles = ['admin', 'member', 'magang', 'leader mtc'];

        // If ALL selected roles are in noAssignmentRoles, then no assignment needed
        $requiresAssignment = false;
        $rolesArray = is_array($rolePost) ? $rolePost : explode(',', $roleStr);
        foreach ($rolesArray as $r) {
            if (!in_array(trim($r), $noAssignmentRoles)) {
                $requiresAssignment = true;
                break;
            }
        }

        if (!$requiresAssignment) {
            $planStr = '-';
            $departemen = '-';
            $lineStr = '-';
        } else {
            $planPost = $this->request->getPost('plant');
            $planStr = is_array($planPost) ? implode(', ', $planPost) : ($planPost ?: '-');
            
            $linePost = $this->request->getPost('line');
            $lineStr = is_array($linePost) ? implode(', ', $linePost) : ($linePost ?: '-');
            
            $departemenPost = $this->request->getPost('departemen');
            $departemen = is_array($departemenPost) ? implode(', ', $departemenPost) : ($departemenPost ?: '-');
        }

        $data = [
            'nama'       => $this->request->getPost('nama'),
            'username'   => $this->request->getPost('username'),
            'role'       => $roleStr,
            'plant'       => $planStr,
            'departemen' => $departemen,
            'line'       => $lineStr,
        ];

        if ($this->request->getPost('password') !== '') {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $this->model->update($id, $data);

        $logAktivitasModel = new \App\Models\LogAktivitasUserModel();
        $changedFields = [];
        foreach ($data as $key => $val) {
            $oldVal = trim((string)($existing[$key] ?? ''));
            $newVal = trim((string)$val);
            if ($key !== 'password' && $oldVal !== $newVal) {
                $changedFields[$key] = ['lama' => $oldVal, 'baru' => $newVal];
            }
        }

        if (isset($data['password'])) {
            $changedFields['password'] = ['lama' => '***', 'baru' => '*** (Diubah)'];
        }
        
        if (!empty($changedFields)) {
            $logAktivitasModel->insert([
                'id_user_target' => $id,
                'nama_user'      => $data['nama'],
                'aksi'           => 'UPDATE',
                'keterangan'     => 'Mengubah data user',
                'detail'         => json_encode(['perubahan' => $changedFields]),
                'dilakukan_oleh' => session()->get('user_id'),
                'created_at'     => date('Y-m-d H:i:s')
            ]);
        }

        return $this->redirectSuccess('/admin/user', 'User berhasil diperbarui.');
    }

    public function toggleActive(int $id)
    {
        $user = $this->model->find($id);
        if (! $user) {
            return $this->redirectNotFound('/admin/user', 'User');
        }
        
        if ((int) $id === (int) session()->get('user_id')) {
            return $this->redirectError('/admin/user', 'Tidak bisa menonaktifkan akun yang sedang digunakan.');
        }

        $newStatus = (isset($user['is_active']) && (int)$user['is_active'] === 1) ? 0 : 1;
        $this->model->update($id, ['is_active' => $newStatus]);

        $statusText = $newStatus === 1 ? 'diaktifkan' : 'dinonaktifkan';
        return $this->redirectSuccess('/admin/user', "User berhasil $statusText.");
    }

    public function delete(int $id)
    {
        if (!has_role(\App\Enums\Role::Admin->value)) {
            return $this->redirectError('/admin/user', 'Hanya admin yang dapat menghapus user.');
        }

        if ((int) $id === (int) session()->get('user_id')) {
            return $this->redirectError('/admin/user', 'Tidak bisa menghapus akun yang sedang login.');
        }

        $user = $this->model->find($id);
        if (! $user) {
            return $this->redirectNotFound('/admin/user', 'User');
        }

        $alasan = $this->request->getPost('alasan');
        if (empty($alasan)) {
            return $this->redirectError('/admin/user', 'Alasan penghapusan harus diisi.');
        }

        $logAktivitasModel = new \App\Models\LogAktivitasUserModel();
        $logAktivitasData = [
            'id_user_target' => $id,
            'nama_user'      => $user['nama'],
            'aksi'           => 'DELETE',
            'keterangan'     => $alasan,
            'detail'         => json_encode(['data_sebelum' => $user]),
            'dilakukan_oleh' => session()->get('user_id'),
            'created_at'     => date('Y-m-d H:i:s')
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $logAktivitasModel->insert($logAktivitasData);
        $this->model->delete($id, true); // Hard delete

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->redirectError('/admin/user', 'Gagal menghapus user.');
        }

        return $this->redirectSuccess('/admin/user', 'User berhasil dihapus permanen beserta histori alasannya.');
    }

    public function deleteBatch()
    {
        if (!has_role(\App\Enums\Role::Admin->value)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Hanya admin yang dapat menghapus user secara massal.']);
        }

        $json = $this->request->getJSON(true) ?? [];
        $ids = $json['ids'] ?? [];
        $alasan = $json['alasan'] ?? '';

        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada user yang dipilih.']);
        }
        if (empty($alasan)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Alasan penghapusan massal harus diisi.']);
        }

        $logAktivitasModel = new \App\Models\LogAktivitasUserModel();
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        $count = 0;
        foreach ($ids as $id) {
            if ((int) $id === (int) session()->get('user_id')) {
                continue; // Skip the currently logged in user
            }

            $user = $this->model->find($id);
            if ($user) {
                $logAktivitasModel->insert([
                    'id_user_target' => $id,
                    'nama_user'      => $user['nama'],
                    'aksi'           => 'DELETE',
                    'keterangan'     => $alasan,
                    'detail'         => json_encode(['data_sebelum' => $user]),
                    'dilakukan_oleh' => session()->get('user_id'),
                    'created_at'     => date('Y-m-d H:i:s')
                ]);
                $this->model->delete($id, true);
                $count++;
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus user secara massal.']);
        }

        return $this->response->setJSON(['status' => true, 'message' => "$count user berhasil dihapus permanen."]);
    }

    public function export()
    {
        $users = $this->model
            ->orderBy("FIELD(role, 'admin', 'sheadmtc', 'sheadprd', 'leader', 'leader mtc', 'member', 'magang')")
            ->orderBy('nama', 'ASC')
            ->findAll();
        
        $filename = 'users_export_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, ['Nama', 'Username', 'Role', 'plant', 'Departemen', 'Line', 'Password']);
        
        foreach ($users as $u) {
            fputcsv($output, [
                $u['nama'],
                $u['username'],
                $u['role'],
                $u['plant'] ?? '',
                $u['departemen'] ?? '',
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
                
                $nama       = trim($row[0]);
                $username   = trim($row[1]);
                $role       = strtolower(trim($row[2]));
                $plant       = isset($row[3]) ? trim($row[3]) : '';
                $departemen = isset($row[4]) ? trim($row[4]) : '';
                $line       = isset($row[5]) ? trim($row[5]) : '';
                $password   = isset($row[6]) ? trim($row[6]) : '';
                
                if (empty($nama) || empty($username) || empty($role)) {
                    $errors[] = "Baris {$rowNum}: Kolom Nama, Username, dan Role tidak boleh kosong.";
                    continue;
                }
                
                if (! in_array($role, [Role::Magang->value, Role::Member->value, Role::Sheadprd->value, Role::Sheadmtc->value, Role::Admin->value, Role::Leader->value, Role::LeaderMember->value], true)) {
                    $errors[] = "Baris {$rowNum}: Role '{$role}' tidak valid. Harus Role::Magang->value, Role::Member->value, Role::Sheadprd->value, Role::Sheadmtc->value, Role::Admin->value, 'leader', atau 'leader mtc'.";
                    continue;
                }
                
                $existing = $this->model->where('username', $username)->first();
                
                if ($existing) {
                    $updateData = [
                        'nama'       => $nama,
                        'role'       => $role,
                        'plant'       => empty($plant) ? null : $plant,
                        'departemen' => empty($departemen) ? null : $departemen,
                        'line'       => empty($line) ? null : $line,
                    ];
                    if (! empty($password)) {
                        $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
                    }
                    $this->model->update($existing['id'], $updateData);
                    $successUpdate++;
                } else {
                    $passToSave = ! empty($password) ? $password : 'password123';
                    $this->model->insert([
                        'nama'       => $nama,
                        'username'   => $username,
                        'role'       => $role,
                        'plant'       => empty($plant) ? null : $plant,
                        'departemen' => empty($departemen) ? null : $departemen,
                        'line'       => empty($line) ? null : $line,
                        'password'   => password_hash($passToSave, PASSWORD_DEFAULT),
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
        // Ambil daftar line yang valid dari database secara dinamis
        $lineModel = new LineModel();
        $validLines = implode(',', $lineModel->getAllLineNames());

        return [
            'nama'   => 'required|max_length[100]',
            'role'   => 'required',
            'role.*' => 'in_list[magang,member,sheadprd,sheadmtc,admin,leader,leader mtc]',
            'line.*' => 'permit_empty|in_list[' . $validLines . ']',
        ];
    }
}
