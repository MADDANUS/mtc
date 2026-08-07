<?php

if (!function_exists('format_tanggal_indo')) {
    /**
     * Format tanggal menjadi dd/mm/yyyy
     * Jika format panjang = true, menjadi dd Bulan yyyy
     */
    function format_tanggal_indo($dateStr, $panjang = false, $termasukJam = false)
    {
        if (empty($dateStr)) {
            return '-';
        }

        $time = strtotime($dateStr);
        if (!$time) {
            return '-';
        }

        if ($panjang) {
            $hari = date('d', $time);
            $bulan = format_bulan_indo(date('Y-m', $time), true);
            $tahun = date('Y', $time);
            $str = "{$hari} {$bulan} {$tahun}";
        } else {
            $str = date('d/m/Y', $time);
        }

        if ($termasukJam) {
            $str .= ' ' . date('H:i', $time);
        }

        return $str;
    }
}

if (!function_exists('format_bulan_indo')) {
    /**
     * Mengubah format 'Y-m' atau 'Y-m-d' menjadi 'Bulan Tahun' atau sekedar 'Bulan'
     */
    function format_bulan_indo($dateStr, $hanyaBulan = false)
    {
        if (empty($dateStr)) {
            return '-';
        }

        $time = strtotime($dateStr);
        if (!$time) {
            return '-';
        }

        $bulan_inggris = date('n', $time);
        
        $daftar_bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $bulan = $daftar_bulan[$bulan_inggris];

        if ($hanyaBulan) {
            return $bulan;
        }

        return $bulan . ' ' . date('Y', $time);
    }
}
