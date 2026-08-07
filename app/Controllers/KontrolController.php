<?php

namespace App\Controllers;

use App\Services\KontrolService;

class KontrolController extends BaseController
{
    public function updateCell()
    {
        $service = new KontrolService();
        $result = $service->updateCell($this->request);
        return $this->response->setJSON($result);
    }

    public function pdf()
    {
        $service = new KontrolService();
        $data = $service->pdf($this->request);
        $html = view('kontrol/pdf', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Checklist_Control_' . str_replace(' ', '_', $data['kategori']) . '_' . str_replace(' ', '_', $data['lokasi']) . '.pdf', ['Attachment' => 0]);
    }

    public function pdfAllCategories()
    {
        $service = new KontrolService();
        $data = $service->pdfAllCategories($this->request);
        $html = view('kontrol/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Checklist_Control_Semua_Kategori_' . str_replace(' ', '_', $data['lokasi']) . '_' . $data['bulan'] . '.pdf', ['Attachment' => true]);
    }

    public function pdfAllSummary()
    {
        $service = new KontrolService();
        $data = $service->pdfAllSummary($this->request);
        $html = view('kontrol/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Checklist_Control_Ringkasan_Semua_Area_' . $data['bulan'] . '.pdf', ['Attachment' => true]);
    }

    public function index()
    {
        $service = new KontrolService();
        $data = $service->index($this->request);
        if (isset($data['is_summary']) && $data['is_summary']) {
            return view('kontrol/summary', $data);
        }
        return view('kontrol/index', $data);
    }

    public function approveBulanan()
    {
        $service = new KontrolService();
        $result = $service->approveBulanan($this->request);
        if ($result['status']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function deleteApprovalBulanan()
    {
        $service = new KontrolService();
        $result = $service->deleteApprovalBulanan($this->request);
        if ($result['status']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }
}
