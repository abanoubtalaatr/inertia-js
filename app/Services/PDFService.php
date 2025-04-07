<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use PDF;

class PDFService
{
    /**
     * Generate contract PDF
     */
    public function generateContractPDF(Contract $contract)
    {
        try {

            $contract->load([
                'hotel',
                'services.service',
                'services.provider',

            ]);

            $pdf = PDF::loadView('pdfs.contract', ['contract' => $contract]);
            $pdf->autoScriptToLang = true;
            $pdf->autoArabic = true;
            $pdf->autoLangToFont = true;

            $filename = "Contract_{$contract->hotel->name}_{$contract->start_date->format('Y-m-d')}_{$contract->end_date->format('Y-m-d')}.pdf";
            $filePath = Storage::disk('local')->path("contracts/{$contract->contract_file}");
            // dd($contract->contract_file);
            if (! Storage::disk('local')->exists("contracts/{$contract->contract_file}") || $contract->contract_file == null) {

                $contractFile = "contract_{$contract->id}_".time().'.pdf';
                Storage::disk('local')->put("contracts/{$contractFile}", $pdf->output());
                $contract->update(['contract_file' => $contractFile]);

                $filePath = Storage::disk('local')->path("contracts/{$contractFile}");
            }

            return $pdf;

        } catch (\Exception $e) {
            \Log::error('Error generating PDF: '.$e->getMessage(), [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function generateContractPDFold(Contract $contract)
    {
        try {
            $contract->load([
                'hotel',
                'services.service',
                'services.provider',

            ]);

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-P',
                'default_font_size' => 12,
                'default_font' => 'arial',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 16,
                'margin_bottom' => 16,
                'margin_header' => 9,
                'margin_footer' => 9,
                'orientation' => 'P',
            ]);

            $mpdf->SetDirectionality('rtl');
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;

            $html = view('pdfs.contract', ['contract' => $contract])->render();

            $mpdf->WriteHTML($html);

            $filename = "contract_{$contract->id}_".time().'.pdf';
            $path = storage_path("app/contracts/{$filename}");
            $mpdf->Output($path, 'F');

            $contract->update(['contract_file' => $filename]);

            return $mpdf;

        } catch (\Exception $e) {
            \Log::error('Error generating PDF: '.$e->getMessage(), [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
