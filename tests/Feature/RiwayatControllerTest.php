<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class RiwayatControllerTest extends CIUnitTestCase
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
     * Test (1) Response 200 untuk lokasi valid
     */
    public function testLokasiValidReturns200()
    {
        // Admin bisa akses lokasi valid
        $result = $this->withSession([
            'id_user' => 1,
            'role'    => 'admin',
            'lokasi'  => 'MFG 1',
            'logged_in' => true
        ])->get('/riwayat/lokasi/mfg1');

        $result->assertStatus(200);
    }

    /**
     * Test (2) Redirect untuk leader yang akses lokasi bukan miliknya
     */
    public function testLeaderAccessingOtherLokasiRedirectsToDashboard()
    {
        // Leader MFG 1 mencoba akses riwayat MFG 2
        $result = $this->withSession([
            'id_user' => 2,
            'role'    => 'leader',
            'lokasi'  => 'MFG 1', // Lokasi leader
            'logged_in' => true
        ])->get('/riwayat/lokasi/mfg2');

        // Harus redirect ke dashboard
        $result->assertRedirectTo('/dashboard');
        
        // Assert session has error
        $result->assertSessionHas('error', 'Akses ditolak. Anda hanya dapat mengakses riwayat lokasi MFG 1');
    }

    /**
     * Test (3) Filter status berjalan benar
     */
    public function testStatusFilterWorksCorrectly()
    {
        // Jika role admin meminta status=all
        $resultAdmin = $this->withSession([
            'id_user' => 1,
            'role'    => 'admin',
            'logged_in' => true
        ])->get('/riwayat/lokasi/mfg1?status=all');

        $resultAdmin->assertStatus(200);

        // Jika role leader mengakses halaman tanpa parameter status (default)
        $resultLeader = $this->withSession([
            'id_user' => 2,
            'role'    => 'leader',
            'lokasi'  => 'MFG 1',
            'logged_in' => true
        ])->get('/riwayat/lokasi/mfg1');

        $resultLeader->assertStatus(200);
        
        // Jika role sheadprd mengakses dengan parameter status tertentu
        $resultShead = $this->withSession([
            'id_user' => 3,
            'role'    => 'sheadprd',
            'logged_in' => true
        ])->get('/riwayat/lokasi/mfg1?status=Approved');

        $resultShead->assertStatus(200);
    }
}
