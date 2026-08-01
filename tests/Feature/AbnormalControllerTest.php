<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class AbnormalControllerTest extends CIUnitTestCase
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
     * Test (1) Response 200 untuk halaman utama Abnormal
     */
    public function testIndexReturns200()
    {
        $result = $this->withSession([
            'id_user' => 1,
            'role'    => 'admin',
            'lokasi'  => 'MFG 1',
            'logged_in' => true
        ])->get('/abnormal');

        $result->assertStatus(200);
    }

    /**
     * Test (2) Response 200 untuk halaman Abnormal Overhaul
     */
    public function testOverhaulReturns200()
    {
        $result = $this->withSession([
            'id_user' => 1,
            'role'    => 'admin',
            'lokasi'  => 'MFG 1',
            'logged_in' => true
        ])->get('/abnormal/overhaul');

        $result->assertStatus(200);
    }

    /**
     * Test (3) Filter bulan berjalan pada index
     */
    public function testIndexWithMonthFilter()
    {
        $result = $this->withSession([
            'id_user' => 1,
            'role'    => 'admin',
            'logged_in' => true
        ])->get('/abnormal?bulan=2024-01');

        $result->assertStatus(200);
    }
}
