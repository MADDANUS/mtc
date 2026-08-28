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
        $q = $this->request->getGet('q');
        $role = $this->request->getGet('role');
        $plant = $this->request->getGet('plant');

        $builder = $this->model;
        
        if (!empty($q)) {
            $builder->groupStart()
                ->like('nama', $q)
                ->orLike('username', $q)
                ->groupEnd();
        }
        
        if (!empty($role) && $role !== 'all') {
            $builder->like('role', $role);
        }
        
        if (!empty($plant) && $plant !== 'all') {
            $builder->like('plant', $plant);
        }

        $daftar = $builder
                ->orderBy("FIELD(LOWER(role), 'admin', 'sheadmtc', 'sheadprd,sheadmtc', 'sheadprd', 'leader', 'leader mtc', 'member', 'magang') = 0", "", false)
                ->orderBy("FIELD(LOWER(role), 'admin', 'sheadmtc', 'sheadprd,sheadmtc', 'sheadprd', 'leader', 'leader mtc', 'member', 'magang')", "", false)
                ->orderBy('plant', 'ASC')
                ->orderBy('departemen', 'ASC')
                ->orderBy('line', 'ASC')
                ->orderBy('nama', 'ASC')
                ->findAll();

        return view('admin/user/index', [
            'title'  => 'Master User',
            'daftar' => $daftar,
            'filters' => [
                'q' => $q,
                'role' => $role,
                'plant' => $plant
            ]
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
        
        if (has_role('leader mtc') && str_contains(strtolower($roleStr), 'admin')) {
            return $this->redirectError('/admin/user', 'Leader MTC tidak dapat memberikan role Admin kepada user baru.');
        }
        
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
            $planStr    = '-';
            $departemen = '-';
            $lineStr    = '-';
        } else {
            $linePost = $this->request->getPost('line');
            $explicitPlants = is_array($this->request->getPost('plant')) ? $this->request->getPost('plant') : [];
            $explicitDepts  = is_array($this->request->getPost('departemen')) ? $this->request->getPost('departemen') : [];
            
            $lineNames  = [];
            $plantNames = $explicitPlants;
            $deptNames  = $explicitDepts;
            
            if (is_array($linePost) && !empty($linePost)) {
                // Parse compound keys: "Plant 1::MFG 1::Line 1"
                foreach ($linePost as $compound) {
                    $parts = explode('::', $compound);
                    if (count($parts) === 3) {
                        $plantNames[] = trim($parts[0]);
                        $deptNames[]  = trim($parts[1]);
                        $lineNames[]  = trim($parts[2]);
                    } else {
                        $lineNames[] = trim($compound);
                    }
                }
            }
            
            $lineStr    = empty($lineNames) ? '-' : implode(', ', array_unique($lineNames));
            $planStr    = empty($plantNames) ? '-' : implode(', ', array_unique($plantNames));
            $departemen = empty($deptNames) ? '-' : implode(', ', array_unique($deptNames));
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

        if (has_role('leader mtc') && str_contains(strtolower($user['role']), 'admin')) {
            return $this->redirectError('/admin/user', 'Leader MTC tidak dapat mengedit akun Admin.');
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

        if (has_role('leader mtc') && str_contains(strtolower($existing['role']), 'admin')) {
            return $this->redirectError('/admin/user', 'Leader MTC tidak dapat mengupdate akun Admin.');
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
        
        if (has_role('leader mtc') && str_contains(strtolower($roleStr), 'admin')) {
            return $this->redirectError('/admin/user', 'Leader MTC tidak dapat memberikan role Admin.');
        }
        
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
            $planStr    = '-';
            $departemen = '-';
            $lineStr    = '-';
        } else {
            $linePost = $this->request->getPost('line');
            $explicitPlants = is_array($this->request->getPost('plant')) ? $this->request->getPost('plant') : [];
            $explicitDepts  = is_array($this->request->getPost('departemen')) ? $this->request->getPost('departemen') : [];
            
            $lineNames  = [];
            $plantNames = $explicitPlants;
            $deptNames  = $explicitDepts;
            
            if (is_array($linePost) && !empty($linePost)) {
                // Parse compound keys: "Plant 1::MFG 1::Line 1"
                foreach ($linePost as $compound) {
                    $parts = explode('::', $compound);
                    if (count($parts) === 3) {
                        $plantNames[] = trim($parts[0]);
                        $deptNames[]  = trim($parts[1]);
                        $lineNames[]  = trim($parts[2]);
                    } else {
                        $lineNames[] = trim($compound);
                    }
                }
            }
            
            $lineStr    = empty($lineNames) ? '-' : implode(', ', array_unique($lineNames));
            $planStr    = empty($plantNames) ? '-' : implode(', ', array_unique($plantNames));
            $departemen = empty($deptNames) ? '-' : implode(', ', array_unique($deptNames));
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
        
        if (has_role('leader mtc') && str_contains(strtolower($user['role']), 'admin')) {
            return $this->redirectError('/admin/user', 'Leader MTC tidak dapat mengubah status aktif akun Admin.');
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
        if (!has_any_role(['admin', 'leader mtc'])) {
            return $this->redirectError('/admin/user', 'Anda tidak memiliki akses untuk menghapus user.');
        }

        if ((int) $id === (int) session()->get('user_id')) {
            return $this->redirectError('/admin/user', 'Tidak bisa menghapus akun yang sedang login.');
        }

        $user = $this->model->find($id);
        if (! $user) {
            return $this->redirectNotFound('/admin/user', 'User');
        }

        if (has_role('leader mtc') && str_contains(strtolower($user['role']), 'admin')) {
            return $this->redirectError('/admin/user', 'Leader MTC tidak dapat menghapus akun Admin.');
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
        if (!has_any_role(['admin', 'leader mtc'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus user secara massal.']);
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
                if (has_role('leader mtc') && str_contains(strtolower($user['role']), 'admin')) {
                    continue; // Skip admin deletion for leader mtc
                }
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

    public function template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'Username');
        $sheet->setCellValue('C1', 'Role');
        $sheet->setCellValue('D1', 'Plant');
        $sheet->setCellValue('E1', 'Departemen');
        $sheet->setCellValue('F1', 'Line');
        $sheet->setCellValue('G1', 'Password');
        
        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0070C0']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'template_user.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function import()
    {
        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to('/admin/user')->with('error', 'Silakan pilih file Excel yang valid.');
        }
        
        $extension = $file->getExtension();
        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to('/admin/user')->with('error', 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv');
        }
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            
            $successInsert = 0;
            $successUpdate = 0;
            $errors = [];
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $nama       = trim($sheet->getCell('A' . $row)->getValue() ?? '');
                $username   = trim($sheet->getCell('B' . $row)->getValue() ?? '');
                $role       = strtolower(trim($sheet->getCell('C' . $row)->getValue() ?? ''));
                $plant       = trim($sheet->getCell('D' . $row)->getValue() ?? '');
                $departemen = trim($sheet->getCell('E' . $row)->getValue() ?? '');
                $line       = trim($sheet->getCell('F' . $row)->getValue() ?? '');
                $password   = trim($sheet->getCell('G' . $row)->getValue() ?? '');
                
                if (empty($nama) && empty($username) && empty($role)) {
                    continue; // Skip empty rows
                }
                
                if (empty($nama) || empty($username) || empty($role)) {
                    $errors[] = "Baris {$row}: Kolom Nama, Username, dan Role tidak boleh kosong.";
                    continue;
                }
                
                if (! in_array($role, [Role::Magang->value, Role::Member->value, Role::Sheadprd->value, Role::Sheadmtc->value, Role::Admin->value, Role::Leader->value, Role::LeaderMember->value], true)) {
                    $errors[] = "Baris {$row}: Role '{$role}' tidak valid. Harus Role::Magang->value, Role::Member->value, Role::Sheadprd->value, Role::Sheadmtc->value, Role::Admin->value, 'leader', atau 'leader mtc'.";
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
            
            $msg = "Impor selesai. Ditambahkan: {$successInsert}, Diperbarui: {$successUpdate}.";
            if (! empty($errors)) {
                $msg .= " Beberapa baris dilewati:\n" . implode("\n", $errors);
                return redirect()->to('/admin/user')->with('error', $msg);
            }
            
            return redirect()->to('/admin/user')->with('success', $msg);
            
        } catch (\Exception $e) {
            return redirect()->to('/admin/user')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    private function rules(): array
    {
        return [
            'nama'   => 'required|max_length[100]',
            'role'   => 'required',
            'role.*' => 'in_list[magang,member,sheadprd,sheadmtc,admin,leader,leader mtc]',
            // line.* tidak divalidasi in_list karena nilai berupa compound key "plant::dept::line"
        ];
    }
}
