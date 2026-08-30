# Ringkasan Perbaikan Bug Approval & Riwayat

Semua perbaikan yang telah direncanakan telah berhasil dieksekusi. Berikut ini adalah ringkasan perubahan yang dilakukan:

## 1. Perbaikan Bug Checklist Control yang Hilang
- **File**: `CeklisKontrolModel.php` & `RiwayatMesinModel.php`
- **Tindakan**: Kueri SQL telah dimodifikasi. `INNER JOIN` diganti menjadi `LEFT JOIN` pada tabel `riwayat_mesin`, dan logika `COALESCE` ditambahkan untuk mengambil data dari `master_mesin` sebagai cadangan.
- **Hasil**: Sistem tidak lagi mengabaikan mesin yang belum memiliki riwayat bulan berjalan. Semua Checklist Control (termasuk peringatan tunggakan mesin, ataupun mesin "yatim piatu") kini akan terhitung dan tampil secara konsisten di layar Dashboard Approval Anda.

## 2. Perbaikan Navigasi Tombol "Kembali"
- **File**: `ApprovalController.php`
- **Tindakan**: Menambahkan injeksi header HTTP `Cache-Control: no-store, no-cache, must-revalidate` untuk mematikan perilaku *Back-Forward Cache (BFCache)* browser pada halaman Approval.
- **Hasil**: Ketika Anda masuk ke halaman detail dari satu dokumen (misal `riwayat/detail.php`), dan menekan tombol "Kembali ke Approval" (atau tombol back browser), halaman akan langsung dimuat ulang dari server secara otomatis. Hal ini memastikan Anda melihat seluruh daftar lengkap dokumen terbaru, bukan versi kadaluarsa (satu dokumen) yang diingat browser. Anda tidak perlu lagi repot menekan F5/Reload manual.

## 3. Penyesuaian Visibilitas Menu Riwayat
- **File**: `RiwayatController.php`
- **Tindakan**: Memperbaiki fungsi `buildSearchFilters()` terkait hak akses status untuk `Leader PRD` dan `SHead PRD`.
- **Hasil**: Leader PRD hanya akan melihat dokumen jika sudah mencapai status L1 atau lebih tinggi, sementara SHead PRD hanya akan melihat dokumen yang sudah mencapai status L2 atau lebih tinggi. Hal ini sesuai dengan permintaan Anda: *"riwayat hanya untuk dokumen yang sudah selesai (approved)"*.

> [!TIP]
> **Verifikasi**
> Silakan Anda *refresh* aplikasi (tekan F5 untuk memastikan file sistem termuat ulang), lalu coba operasikan menu Approval dan Riwayat sebagai Leader PRD / SHead PRD untuk melihat hasilnya. Coba juga buka salah satu laporan lalu tekan tombol **Kembali ke Approval** untuk melihat bahwa fitur *back* kini berjalan lancar tanpa perlu *reload* manual.

## 4. Perbaikan Visibilitas History (Overhaul & Checklist) untuk SHead MTC
**Masalah**: SHead MTC tidak bisa melihat laporan Overhaul (Inspection Report) maupun Checklist Report yang sudah ia setujui di menu History.
**Penyebab**: Fungsi `validateLeaderAccess` membatasi visibilitas history secara kaku hanya ke departemen yang terdapat pada *session* user. SHead MTC memiliki *session* departemen bernilai `-` (artinya semua area pabrik), sehingga saat filter departemen diaplikasikan, tidak ada satupun dokumen yang cocok.
**Solusi**:
- Menambahkan pengecualian eksplisit untuk role `SHead MTC` di dalam pengecekan `validateLeaderAccess()` di `RiwayatService.php`.
- Dengan begitu, SHead MTC dapat melihat laporan di *semua* departemen seperti seharusnya.

## 5. Perbaikan Visibilitas Tren Abnormalitas Bulanan (Role Magang)
**Masalah**: Pada halaman Ringkasan Abnormal Report, tabel ringkasan berhasil menampilkan seluruh laporan, namun grafik "Tren Abnormalitas Bulanan" menunjukkan *Tidak ada data* apabila diakses menggunakan role magang.
**Penyebab**: Ada ketidaksinkronan aturan antara Tabel dan Grafik:
- Tabel (Checklist) memanggil query tanpa memfilter berdasarkan ID Magang (sehingga menampilkan seluruh data kerusakan pabrik).
- Grafik menggunakan API `getChartData()` yang memaksakan filter `WHERE id_user = session('user_id')` jika rolenya adalah magang.
**Solusi**:
- Menghapus blok filter `id_user` dari `LaporanAbnormalModel::getChartData()` agar data yang diambil oleh grafik sepenuhnya sinkron dengan tabel.
- Sekarang Magang tetap bisa melihat tren kerusakan keseluruhan pabrik di grafik, tanpa harus pernah membuat laporan itu sendiri.

## 6. Pemilihan Kategori Dinamis untuk Overhaul MFG 1
**Masalah**: Jika Anda memilih Overhaul untuk area MFG 1, sistem secara *hardcode* dan otomatis langsung masuk ke form `Mesin CNC & Bar Feeder`, berbeda dengan MFG 2 yang menampilkan layar pilihan "Pilih Kategori Baru" dengan desain kotak. Hal ini menyulitkan penambahan kategori baru di MFG 1.
**Solusi**:
- Menghapus blok aturan *auto-routing* khusus MFG 1 di fungsi `ChecklistController::indexKategori()`.
- Memperbarui fungsi internal pencari kategori (`resolveCategoriesList()`) agar untuk semua departemen (baik MFG 1 maupun MFG 2), jika jenis pengecekannya adalah *Overhaul*, sistem akan melakukan `SELECT DISTINCT jenis` dari tabel `master_mesin` untuk area terkait secara dinamis.
- **[Bugfix Parameter Kosong]**: Karena nama jenis mesin di database `master_mesin` adalah "CNC", sistem secara dinamis membuat kategori bernama "CNC". Namun, parameter master di database dinamakan "Mesin CNC & Bar Feeder". Oleh karena itu, saya menambahkan pemetaan di `ParameterCheckModel` agar jika kategori "CNC" dipilih, parameter "Mesin CNC & Bar Feeder" tetap dimuat sempurna.
**Hasil**: Saat Anda masuk ke `Overhaul MFG 1`, layar tidak akan langsung melompat ke form pengisian. Sebaliknya, aplikasi akan menampilkan layar "Buat Pengecekan Baru" beserta daftar kotak pilihan kategori (*tile*) yang lebih ringkas ("CNC"), dan parameter akan termuat secara otomatis saat form dibuka.

## 7. Penambahan Tombol Riwayat pada Master Data Mesin
**Masalah**: Admin kesulitan melacak sejarah perpindahan departemen/line atau perubahan nomor seri dari masing-masing mesin.
**Solusi**:
- Menambahkan tombol "Riwayat Mesin" (ikon jam) di kolom Aksi pada halaman Master Data Mesin.
- Memperbaiki *bug* pada urutan *load Javascript* yang sempat menyebabkan tombol tidak bisa diklik. Tombol kini membuka sebuah *pop-up* (modal) secara mulus (lazy-load) menggunakan Bootstrap.
**Hasil**: Anda kini bisa melihat rekaman riwayat perubahan dari suatu mesin (Kolom Lama vs Kolom Baru beserta nama Admin pengubahnya) hanya dengan satu klik.

## 8. Perbaikan Tabel Snapshot Data Terhapus (Overhaul)
**Masalah**: Pada halaman Log Riwayat Dokumen (`/admin/audit-log`), tabel rincian data terhapus khusus untuk dokumen *Overhaul* berantakan/bergeser menyerupai tangga.
**Penyebab**: Logika pembentukan tabel (*rowspan* otomatis) berasumsi bahwa susunan parameter selalu berurutan (*grouped*) berdasarkan "BAGIAN CHECK" seperti pada Preventive. Pada Overhaul, datanya acak sehingga logika tersebut gagal dan terus-menerus mendorong sel ke kanan.
**Solusi**:
- Menambahkan skrip *Auto-Sort* (pengurutan) di `index.php` pada folder `audit_log`. Skrip ini akan menyusun ulang baris parameter berdasarkan abjad `BAGIAN CHECK` sebelum tabel digambar.
**Hasil**: Tabel snapshot data dokumen terhapus untuk Overhaul kini kembali rata dan rapi sempurna.
