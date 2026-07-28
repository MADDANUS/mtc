<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiCheckDetailModel extends Model
{
    protected $table         = 'transaksi_check_detail';
    protected $primaryKey    = 'id_detail';
    protected $allowedFields = ['id_transaksi', 'id_parameter', 'hasil_check', 'ulasan', 'foto_abnormal', 'foto_abnormal_2'];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * Semua hasil centangan untuk satu transaksi, dijoin dengan info
     * parameter (kategori/bagian/point/standard), diurutkan sesuai
     * urutan form kertas aslinya.
     */
    public function getDetailByTransaksi(int $idTransaksi): array
    {
        return $this->select('transaksi_check_detail.*, master_parameter_check.kategori, master_parameter_check.section_check, master_parameter_check.bagian_check, master_parameter_check.sub_item_check, master_parameter_check.point_check, master_parameter_check.standard_check, master_parameter_check.urutan')
                    ->join('master_parameter_check', 'master_parameter_check.id_parameter = transaksi_check_detail.id_parameter')
                    ->where('transaksi_check_detail.id_transaksi', $idTransaksi)
                    ->orderBy('master_parameter_check.urutan', 'ASC')
                    ->findAll();
    }

    /**
     * Hitung rowspan untuk rendering view (Preventive / Overhaul).
     */
    public function calculateRowspans(array $rows, string $jenisCheck): array
    {
        if (strtolower($jenisCheck) !== 'overhaul') {
            // Existing logic for Preventive
            $total = count($rows);

            // Hitung rowspan BAGIAN CHECK
            for ($i = 0; $i < $total; $i++) {
                if ($i > 0 && $rows[$i]['bagian_check'] === $rows[$i - 1]['bagian_check']) {
                    $rows[$i]['show_bagian'] = false;
                } else {
                    $span = 1;
                    for ($j = $i + 1; $j < $total; $j++) {
                        if ($rows[$j]['bagian_check'] === $rows[$i]['bagian_check']) {
                            $span++;
                        } else {
                            break;
                        }
                    }
                    $rows[$i]['show_bagian']    = true;
                    $rows[$i]['bagian_rowspan'] = $span;
                }
            }

            // Hitung rowspan POINT CHECK
            for ($i = 0; $i < $total; $i++) {
                if (
                    $i > 0
                    && $rows[$i]['point_check'] === $rows[$i - 1]['point_check']
                    && $rows[$i]['bagian_check'] === $rows[$i - 1]['bagian_check']
                ) {
                    $rows[$i]['show_point'] = false;
                } else {
                    $span = 1;
                    for ($j = $i + 1; $j < $total; $j++) {
                        if (
                            $rows[$j]['point_check'] === $rows[$i]['point_check']
                            && $rows[$j]['bagian_check'] === $rows[$i]['bagian_check']
                        ) {
                            $span++;
                        } else {
                            break;
                        }
                    }
                    $rows[$i]['show_point']    = true;
                    $rows[$i]['point_rowspan'] = $span;
                }
            }

            return $rows;
        }

        // Logic for Overhaul
        $total = count($rows);

        // Pre-process parts with | (pipe) to separate bagian_check and sub_item_check
        for ($i = 0; $i < $total; $i++) {
            if (!empty($rows[$i]['bagian_check']) && strpos($rows[$i]['bagian_check'], '|') !== false) {
                $parts = explode('|', $rows[$i]['bagian_check']);
                $rows[$i]['bagian_check'] = trim($parts[0]);
                if (empty($rows[$i]['sub_item_check'])) {
                    $rows[$i]['sub_item_check'] = trim($parts[1]);
                }
            }
        }

        // Inject virtual categories for 'Mesin CNC & Bar Feeder' so the view can paginate them
        $isCnc = true;
        for ($i = 0; $i < $total; $i++) {
            if (strtolower($rows[$i]['kategori'] ?? '') === 'mesin cnc & bar feeder') {
                if (strtoupper($rows[$i]['section_check'] ?? '') === 'EQUIPMENT CHECK') {
                    $isCnc = false;
                }
                
                if ($isCnc) {
                    $rows[$i]['kategori'] = 'Mesin CNC';
                } else {
                    $rows[$i]['kategori'] = 'Bar Feeder CNC';
                }
            }
        }

        $sectionCounter = 0;
        $currentSection = null;
        $itemLetterSuffixIndex = 0;
        $prevBagianCheck = null;
        $currentCategory = null;

        // Pre-populate dynamic numbering and flags
        for ($i = 0; $i < $total; $i++) {
            $cat = $rows[$i]['kategori'] ?? null;
            if ($cat !== $currentCategory) {
                // Reset counters when category changes
                $sectionCounter = 0;
                $currentSection = null;
                $itemLetterSuffixIndex = 0;
                $prevBagianCheck = null;
                $currentCategory = $cat;
            }

            $sec = $rows[$i]['section_check'];
            if (!empty($sec)) {
                // Section start detection
                if ($sec !== $currentSection) {
                    $sectionCounter++;
                    $currentSection = $sec;
                    $itemLetterSuffixIndex = 0; // start with 'a'
                    $prevBagianCheck = $rows[$i]['bagian_check'];
                    $rows[$i]['is_section_start'] = true;
                    
                    if ($currentCategory === 'Bar Feeder CNC') {
                        $rows[$i]['dynamic_section_header'] = "1.{$sectionCounter} {$sec}";
                    } else {
                        $rows[$i]['dynamic_section_header'] = "{$sectionCounter}. {$sec}";
                    }
                } else {
                    $rows[$i]['is_section_start'] = false;
                    $rows[$i]['dynamic_section_header'] = null;
                    // If bagian_check changes within same section, increment letter index
                    if ($rows[$i]['bagian_check'] !== $prevBagianCheck) {
                        $itemLetterSuffixIndex++;
                        $prevBagianCheck = $rows[$i]['bagian_check'];
                    }
                }
                
                // Convert index to lowercase letter (0 -> 'a', 1 -> 'b'...)
                $letter = chr(97 + $itemLetterSuffixIndex);
                
                if ($currentCategory === 'Bar Feeder CNC') {
                    $rows[$i]['dynamic_no'] = "1.{$sectionCounter}{$letter}";
                } else {
                    $rows[$i]['dynamic_no'] = "{$sectionCounter}{$letter}";
                }
            } else {
                $rows[$i]['is_section_start'] = false;
                $rows[$i]['dynamic_section_header'] = null;
                $rows[$i]['dynamic_no'] = "•";
            }
            
            // Set defaults for rowspans
            $rows[$i]['show_no'] = true;
            $rows[$i]['no_rowspan'] = 1;
            $rows[$i]['show_bagian'] = true;
            $rows[$i]['bagian_rowspan'] = 1;
            $rows[$i]['show_point'] = true;
            $rows[$i]['point_rowspan'] = 1;
            $rows[$i]['show_standard'] = true;
            $rows[$i]['standard_rowspan'] = 1;
        }

        for ($i = 0; $i < $total; $i++) {
            $sec = $rows[$i]['section_check'];
            if (empty($sec)) {
                // Bottom rows (no section) do not merge
                continue;
            }

            // no_urut rowspan (grouped by dynamic_no)
            if ($i > 0 && $rows[$i - 1]['section_check'] === $sec && $rows[$i]['dynamic_no'] === $rows[$i - 1]['dynamic_no']) {
                $rows[$i]['show_no'] = false;
            } else {
                $span = 1;
                for ($j = $i + 1; $j < $total; $j++) {
                    if ($rows[$j]['section_check'] === $sec && $rows[$j]['dynamic_no'] === $rows[$i]['dynamic_no']) {
                        $span++;
                    } else {
                        break;
                    }
                }
                $rows[$i]['show_no'] = true;
                $rows[$i]['no_rowspan'] = $span;
            }

            // bagian_check rowspan (ITEM CHECK)
            if ($i > 0 && $rows[$i - 1]['section_check'] === $sec && $rows[$i]['dynamic_no'] === $rows[$i - 1]['dynamic_no'] && $rows[$i]['bagian_check'] === $rows[$i - 1]['bagian_check']) {
                $rows[$i]['show_bagian'] = false;
            } else {
                $span = 1;
                for ($j = $i + 1; $j < $total; $j++) {
                    if ($rows[$j]['section_check'] === $sec && $rows[$j]['dynamic_no'] === $rows[$i]['dynamic_no'] && $rows[$j]['bagian_check'] === $rows[$i]['bagian_check']) {
                        $span++;
                    } else {
                        break;
                    }
                }
                $rows[$i]['show_bagian'] = true;
                $rows[$i]['bagian_rowspan'] = $span;
            }

            // point_check rowspan
            if ($i > 0 && $rows[$i - 1]['section_check'] === $sec && $rows[$i]['dynamic_no'] === $rows[$i - 1]['dynamic_no'] && $rows[$i]['point_check'] === $rows[$i - 1]['point_check']) {
                $rows[$i]['show_point'] = false;
            } else {
                $span = 1;
                for ($j = $i + 1; $j < $total; $j++) {
                    if ($rows[$j]['section_check'] === $sec && $rows[$j]['dynamic_no'] === $rows[$i]['dynamic_no'] && $rows[$j]['point_check'] === $rows[$i]['point_check']) {
                        $span++;
                    } else {
                        break;
                    }
                }
                $rows[$i]['show_point'] = true;
                $rows[$i]['point_rowspan'] = $span;
            }

            // standard_check rowspan (only span if standard check is not empty or '-')
            if (!empty($rows[$i]['standard_check']) && $rows[$i]['standard_check'] !== '-') {
                if ($i > 0 && $rows[$i - 1]['section_check'] === $sec && $rows[$i]['dynamic_no'] === $rows[$i - 1]['dynamic_no'] && $rows[$i]['standard_check'] === $rows[$i - 1]['standard_check']) {
                    $rows[$i]['show_standard'] = false;
                } else {
                    $span = 1;
                    for ($j = $i + 1; $j < $total; $j++) {
                        if ($rows[$j]['section_check'] === $sec && $rows[$j]['dynamic_no'] === $rows[$i]['dynamic_no'] && $rows[$j]['standard_check'] === $rows[$i]['standard_check']) {
                            $span++;
                        } else {
                            break;
                        }
                    }
                    $rows[$i]['show_standard'] = true;
                    $rows[$i]['standard_rowspan'] = $span;
                }
            }
        }

        return $rows;
    }
}
