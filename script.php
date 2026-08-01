<?php
$serviceFile = "c:/xampp/htdocs/mtce/app/Services/RiwayatService.php";
$serviceCode = file_get_contents($serviceFile);

// Ambil kode dari 0 s/d akhir dari deleteTransaksi
$deletePos = strpos($serviceCode, "public function deleteTransaksi");
$deleteEnd = strpos($serviceCode, "}", $deletePos) + 1; // get the closing brace of deleteTransaksi
$cleanService = substr($serviceCode, 0, $deleteEnd);

// Sekarang ambil update dan approve dari backup
$backup = file_get_contents("c:/xampp/htdocs/mtce/app/Controllers/RiwayatController_backup.php");

$startUpdate = strpos($backup, "public function update(int \$id)");
$startDelete = strpos($backup, "public function delete(int \$id)");
$startApprove = strpos($backup, "public function approve(\$idTransaksi)");

$updateMethod = substr($backup, $startUpdate, $startDelete - $startUpdate);
$approveMethod = substr($backup, $startApprove, strlen($backup) - $startApprove - 4);

$updateService = str_replace('public function update(int $id)', 'public function updateTransaksi(int $id, $request, $validation)', $updateMethod);
$updateService = str_replace('$this->request', '$request', $updateService);
$updateService = str_replace('$this->validate($rules)', '$validation->setRules($rules); if (!$validation->withRequest($request)->run()) return ["status" => false, "errors" => $validation->getErrors()]; return true', $updateService);
$updateService = str_replace("return redirect()->back()->withInput()->with('errors', \Config\Services::validation()->getErrors());", "", $updateService);
$updateService = preg_replace("/return redirect\(\)->back\(\)->withInput\(\)->with\('error', (.*?)\);/", 'return ["status" => false, "message" => $1];', $updateService);
$updateService = preg_replace("/return redirect\(\)->to\(.*?\)->with\('success', (.*?)\);/", 'return ["status" => true, "message" => $1];', $updateService);
$updateService = preg_replace("/return redirect\(\)->back\(\)->with\('error', (.*?)\);/", 'return ["status" => false, "message" => $1];', $updateService);
$updateService = preg_replace("/return redirect\(\)->back\(\)->with\('success', (.*?)\);/", 'return ["status" => true, "message" => $1];', $updateService);

$approveService = str_replace('public function approve($idTransaksi)', 'public function approveTransaksi($idTransaksi, $request)', $approveMethod);
$approveService = str_replace('$this->request', '$request', $approveService);
$approveService = preg_replace("/return redirect\(\)->back\(\)->with\('error', (.*?)\);/", 'return ["status" => false, "message" => $1];', $approveService);
$approveService = preg_replace("/return redirect\(\)->back\(\)->with\('success', (.*?)\);/", 'return ["status" => true, "message" => $1];', $approveService);

$finalCode = $cleanService . "\n\n" . $updateService . "\n" . $approveService . "\n}\n";

file_put_contents($serviceFile, $finalCode);
echo "Rebuilt completely.\n";
