<?php
$controllerCode = file_get_contents("c:/xampp/htdocs/mtce/app/Controllers/RiwayatController.php");

$serviceFile = "c:/xampp/htdocs/mtce/app/Services/RiwayatService.php";
$serviceCode = file_get_contents($serviceFile);

// Ambil clean version of serviceCode from start to the end of deleteTransaksi (which is line 273 roughly)
$deletePos = strpos($serviceCode, "public function deleteTransaksi");
$deleteEnd = strpos($serviceCode, "}", $deletePos) + 1; // get the closing brace of deleteTransaksi
$cleanService = substr($serviceCode, 0, $deleteEnd);

// Extract update and approve from original controller (UTF-8 clean)
$startUpdate = strpos($controllerCode, "public function update(int \$id)");
$startDelete = strpos($controllerCode, "public function delete(int \$id)");
$startApprove = strpos($controllerCode, "public function approve(\$idTransaksi)");

$updateMethod = substr($controllerCode, $startUpdate, $startDelete - $startUpdate);
$approveMethod = substr($controllerCode, $startApprove, strlen($controllerCode) - $startApprove - 4);

$updateService = str_replace('public function update(int $id)', 'public function updateTransaksi(int $id, $request, $validation)', $updateMethod);
$updateService = str_replace('$this->request', '$request', $updateService);
$updateService = str_replace('$this->validate($rules)', '$validation->setRules($rules); if (!$validation->withRequest($request)->run()) return ["status" => false, "errors" => $validation->getErrors()]; return true', $updateService);
$updateService = preg_replace("/return redirect\(\)->back\(\)->withInput\(\)->with\('errors', \\\\Config\\\\Services::validation\(\)->getErrors\(\)\);/", "", $updateService);
$updateService = preg_replace("/return redirect\(\)->back\(\)->with\('error', (.*?)\);/", 'return ["status" => false, "message" => $1];', $updateService);
$updateService = preg_replace("/return redirect\(\)->to\(.*?\)->with\('success', (.*?)\);/", 'return ["status" => true, "message" => $1];', $updateService);
$updateService = preg_replace("/return redirect\(\)->back\(\)->withInput\(\)->with\('error', (.*?)\);/", 'return ["status" => false, "message" => $1];', $updateService);

$approveService = str_replace('public function approve($idTransaksi)', 'public function approveTransaksi($idTransaksi, $request)', $approveMethod);
$approveService = str_replace('$this->request', '$request', $approveService);
$approveService = preg_replace("/return redirect\(\)->back\(\)->with\('error', (.*?)\);/", 'return ["status" => false, "message" => $1];', $approveService);
$approveService = preg_replace("/return redirect\(\)->back\(\)->with\('success', (.*?)\);/", 'return ["status" => true, "message" => $1];', $approveService);

$finalCode = $cleanService . "\n\n" . $updateService . "\n" . $approveService . "\n}\n";

file_put_contents($serviceFile, $finalCode);
echo "RiwayatService successfully written completely fresh.\n";
