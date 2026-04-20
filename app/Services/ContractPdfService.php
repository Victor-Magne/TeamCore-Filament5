<?php

namespace App\Services;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContractPdfService
{
    /**
     * Generate a PDF for a single contract
     */
    public function generateSingleContractPdf(Contract $contract): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.contract-single', [
            'contract' => $contract,
        ])
            ->setPaper('a4')
            ->setOption('defaultFont', 'sans-serif');
    }

    /**
     * Generate a PDF for multiple contracts
     */
    public function generateMultipleContractsPdf(Collection|array $contracts): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.contracts-report', [
            'contracts' => collect($contracts),
        ])
            ->setPaper('a4')
            ->setOption('defaultFont', 'sans-serif');
    }

    /**
     * Download PDF for a single contract
     */
    public function downloadSingleContractPdf(Contract $contract)
    {
        $filename = sprintf(
            'contrato_%s_%s.pdf',
            Str::slug($contract->employee->first_name),
            now()->format('Y-m-d')
        );

        return $this->generateSingleContractPdf($contract)
            ->download($filename);
    }

    /**
     * Download PDF for multiple contracts
     */
    public function downloadMultipleContractsPdf(Collection|array $contracts)
    {
        $filename = sprintf('contratos_relatorio_%s.pdf', now()->format('Y-m-d'));

        return $this->generateMultipleContractsPdf($contracts)
            ->download($filename);
    }
}
