<?php

namespace App\Traits;

/**
 * Trait untuk mengkonsolidasi pola berulang pada CRUD operasi di Admin Controller.
 */
trait AdminCrudTrait
{
    /**
     * Redirect ke halaman sebelumnya dengan membawa input dan pesan error validasi.
     * Metode ini mengasumsikan controller pemanggil memiliki properti $validator
     * yang diinisialisasi oleh \CodeIgniter\Controller.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    protected function redirectValidationError()
    {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    /**
     * Redirect dengan pesan sukses.
     *
     * @param string $url Tujuan redirect
     * @param string $message Pesan sukses yang akan ditampilkan
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    protected function redirectSuccess(string $url, string $message)
    {
        return redirect()->to($url)->with('success', $message);
    }

    /**
     * Redirect dengan pesan error jika entitas/data tidak ditemukan.
     *
     * @param string $url Tujuan redirect
     * @param string $entityName Nama entitas (default: 'Data')
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    protected function redirectNotFound(string $url, string $entityName = 'Data')
    {
        return redirect()->to($url)->with('error', "{$entityName} tidak ditemukan.");
    }

    /**
     * Redirect dengan pesan error kustom (opsional untuk kegagalan operasi lain).
     *
     * @param string $url Tujuan redirect
     * @param string $message Pesan error yang akan ditampilkan
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    protected function redirectError(string $url, string $message)
    {
        return redirect()->to($url)->with('error', $message);
    }
}
