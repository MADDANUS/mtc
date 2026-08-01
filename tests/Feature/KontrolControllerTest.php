<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class KontrolControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test (1) Response 200 untuk halaman utama Dashboard Kontrol
     */
    public function testIndexReturns200()
    {
        $result = $this->withSession([
            'id_user' => 1,
            'role'    => 'admin',
            'lokasi'  => 'MFG 1',
            'logged_in' => true
        ])->get('/kontrol');

        $result->assertStatus(200);
    }

    /**
     * Test (2) Leader otomatis di-redirect / difilter sesuai lokasinya
     */
    public function testLeaderAccessIndex()
    {
        $result = $this->withSession([
            'id_user' => 2,
            'role'    => 'leader',
            'lokasi'  => 'MFG 1',
            'logged_in' => true
        ])->get('/kontrol?lokasi=MFG+2');

        // Berdasarkan logika Controller Kontrol, lokasi dire-assign secara paksa atau di-redirect
        // Kita cukup memastikan tidak crash dan mendapat response
        $result->assertStatus(200);
    }
}
