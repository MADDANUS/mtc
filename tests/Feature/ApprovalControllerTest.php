<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class ApprovalControllerTest extends CIUnitTestCase
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
     * Test (1) Response 200 untuk halaman utama Approval
     */
    public function testIndexReturns200()
    {
        $result = $this->withSession([
            'id_user' => 1,
            'role'    => 'admin',
            'lokasi'  => 'MFG 1',
            'logged_in' => true
        ])->get('/approval');

        $result->assertStatus(200);
    }

    /**
     * Test (2) Leader hanya bisa melihat approval miliknya atau sesuai lokasinya
     */
    public function testLeaderAccessIndex()
    {
        $result = $this->withSession([
            'id_user' => 2,
            'role'    => 'leader',
            'lokasi'  => 'MFG 1',
            'logged_in' => true
        ])->get('/approval');

        // Seharusnya tidak di-redirect karena Approval otomatis mengambil lokasi session
        $result->assertStatus(200);
    }

    /**
     * Test (3) Pengujian dengan parameter filter
     */
    public function testIndexWithFilters()
    {
        $result = $this->withSession([
            'id_user' => 3,
            'role'    => 'sheadprd',
            'logged_in' => true
        ])->get('/approval?lokasi=MFG+2&status=Approved+L1');

        $result->assertStatus(200);
    }
}
