<?php

namespace App\Models;

use CodeIgniter\Model;

class ParameterCheckModel extends Model
{
    protected $table         = 'master_parameter_check';
    protected $primaryKey    = 'id_parameter';
    protected $allowedFields = [
        'lokasi', 'jenis_check', 'kategori', 'section_check',
        'bagian_check', 'point_check', 'standard_check', 'urutan',
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * Ambil semua baris parameter untuk kombinasi lokasi + jenis_check,
     * lalu tambahkan info rowspan untuk kolom BAGIAN CHECK dan POINT CHECK
     * supaya tabel di View bisa persis meniru layout form kertas
     * (BAGIAN CHECK dan POINT CHECK yang sama digabung vertikal).
     */
    public function getFormRows(string $lokasi, string $jenisCheck, ?string $kategori = null): array
    {
        $builder = $this->where('lokasi', $lokasi)
                        ->where('jenis_check', $jenisCheck);

        if ($kategori !== null) {
            if (strtolower($kategori) === 'mesin cnc & bar feeder') {
                $builder->whereIn('kategori', ['Mesin CNC', 'Bar Feeder CNC', 'Mesin CNC & Bar Feeder', 'MESIN CNC & BAR FEEDER']);
            } else {
                $builder->where('kategori', $kategori);
            }
        }

        $rows = $builder->orderBy('kategori', 'DESC')
                        ->orderBy('urutan', 'ASC')
                        ->orderBy('id_parameter', 'ASC')
                        ->findAll();

        // Pisahkan teks yang memiliki '|' menjadi bagian_check dan sub_item_check
        foreach ($rows as &$row) {
            if (strpos($row['bagian_check'], '|') !== false) {
                $parts = explode('|', $row['bagian_check'], 2);
                $row['bagian_check']   = trim($parts[0]);
                $row['sub_item_check'] = trim($parts[1]);
            } else {
                $row['sub_item_check'] = null;
            }
        }
        unset($row);

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

            // standard_check rowspan (only span if standard check is not empty)
            if (!empty($rows[$i]['standard_check'])) {
                if ($i > 0 && $rows[$i - 1]['section_check'] === $sec && $rows[$i]['standard_check'] === $rows[$i - 1]['standard_check']) {
                    $rows[$i]['show_standard'] = false;
                } else {
                    $span = 1;
                    for ($j = $i + 1; $j < $total; $j++) {
                        if ($rows[$j]['section_check'] === $sec && $rows[$j]['standard_check'] === $rows[$i]['standard_check']) {
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

    public function getKombinasiKategori(): array
    {
        return $this->select('lokasi, jenis_check, kategori')
                    ->groupBy('lokasi, jenis_check, kategori')
                    ->findAll();
    }

    public function getParamsByKombinasi(string $lokasi, string $jenisCheck, string $kategori): array
    {
        return $this->where('lokasi', $lokasi)
                    ->where('jenis_check', $jenisCheck)
                    ->where('kategori', $kategori)
                    ->orderBy('urutan', 'ASC')
                    ->orderBy('id_parameter', 'ASC')
                    ->findAll();
    }

    public function updateUrutan(int $idParameter, int $urutan): void
    {
        $this->update($idParameter, ['urutan' => $urutan]);
    }
}
