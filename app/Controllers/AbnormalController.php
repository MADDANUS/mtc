<?php

namespace App\Controllers;

use App\Services\AbnormalService;

class AbnormalController extends BaseController
{
    public function __construct()
    {
        // Constructor is empty now as model is handled by service
    }

    public function pdf()
    {
        $service = new AbnormalService();
        $data = $service->pdf($this->request);
        $html = view('abnormal/pdf', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_' . str_replace(' ', '_', $data['kategoriFilter']) . '_' . str_replace(' ', '_', $data['lokasiFilter']) . '.pdf', ['Attachment' => true]);
    }

    public function pdfAllCategories()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllCategories($this->request);
        $html = view('abnormal/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Semua_Kategori_' . str_replace(' ', '_', $data['lokasiFilter']) . '_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function pdfAllSummary()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllSummary($this->request);
        $html = view('abnormal/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Ringkasan_Semua_Area_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function index()
    {
        $service = new AbnormalService();
        $data = $service->index($this->request);
        if (isset($data['is_summary']) && $data['is_summary']) {
            return view('abnormal/summary', $data);
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'html' => $data['html'] ?? '',
                'currentPage' => $data['currentPage'] ?? 1,
                'totalPages' => $data['totalPages'] ?? 1,
                'totalItems' => $data['totalItems'] ?? 0,
                'perPage' => $data['perPage'] ?? 15,
                'startNo' => $data['startNo'] ?? 1,
            ]);
        }
        
        return view('abnormal/index', $data);
    }

    public function update()
    {
        $service = new AbnormalService();
        $result = $service->update($this->request);
        if ($result['status']) {
            return redirect()->to('/abnormal')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function overhaul()
    {
        $service = new AbnormalService();
        $data = $service->overhaul($this->request);
        if (isset($data['is_summary']) && $data['is_summary']) {
            return view('abnormal/summary_overhaul', $data);
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'html' => $data['html'] ?? '',
                'currentPage' => $data['currentPage'] ?? 1,
                'totalPages' => $data['totalPages'] ?? 1,
                'totalItems' => $data['totalItems'] ?? 0,
                'perPage' => $data['perPage'] ?? 15,
                'startNo' => $data['startNo'] ?? 1,
            ]);
        }
        
        return view('abnormal/index_overhaul', $data);
    }

    public function pdfOverhaul()
    {
        $service = new AbnormalService();
        $data = $service->pdfOverhaul($this->request);
        $html = view('abnormal/pdf_overhaul', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Overhaul_' . str_replace(' ', '_', $data['lokasiFilter']) . '.pdf', ['Attachment' => true]);
    }

    public function pdfAllSummaryOverhaul()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllSummaryOverhaul($this->request);
        $html = view('abnormal/pdf_all_summary', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Overhaul_Ringkasan_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function updateOverhaul()
    {
        $service = new AbnormalService();
        $result = $service->updateOverhaul($this->request);
        if ($result['status']) {
            return redirect()->to('/abnormal/overhaul')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function uploadFotoPerbaikan()
    {
        $service = new AbnormalService();
        $result = $service->uploadFotoPerbaikan($this->request);
        return $this->response->setJSON($result);
    }

    public function deleteFotoPerbaikan()
    {
        $service = new AbnormalService();
        $result = $service->deleteFotoPerbaikan($this->request);
        return $this->response->setJSON($result);
    }
}