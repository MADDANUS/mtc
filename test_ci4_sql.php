<?php
require 'vendor/autoload.php';

// Create a minimal fake connection
class FakeConnection {
    public $DBPrefix = '';
    public $strictOn = false;
    public function escape($str) { return "'$str'"; }
    public function escapeIdentifiers($item) { return $item; }
}

$db = new FakeConnection();
$builder = new \CodeIgniter\Database\BaseBuilder('transaksi_check', $db);
$builder->groupStart()
        ->where('1=0')
        ->orGroupStart()
            ->where('status', 'Approved L1')
        ->groupEnd()
        ->orGroupStart()
            ->where('status', 'Approved L2')
        ->groupEnd()
        ->groupEnd();

echo $builder->getCompiledSelect() . "\n";

$builder2 = new \CodeIgniter\Database\BaseBuilder('approval_bulanan', $db);
$builder2->groupStart()
         ->orGroupStart()
             ->where('status', 'Approved L1')
         ->groupEnd()
         ->orGroupStart()
             ->where('status', 'Approved L2')
         ->groupEnd()
         ->groupEnd();

echo $builder2->getCompiledSelect() . "\n";
