<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractPdfService;

class ContractPdfController extends Controller
{
    /**
     * Download a single contract as PDF
     */
    public function downloadSingle(Contract $contract)
    {
        return (new ContractPdfService)
            ->downloadSingleContractPdf($contract);
    }

    /**
     * Download all contracts as PDF report
     */
    public function downloadAll()
    {
        $contracts = Contract::all();

        return (new ContractPdfService)
            ->downloadMultipleContractsPdf($contracts);
    }

    /**
     * Download selected contracts as PDF report
     */
    public function downloadBulk()
    {
        $ids = explode(',', request('ids'));
        $contracts = Contract::whereIn('id', $ids)->get();

        return (new ContractPdfService)
            ->downloadMultipleContractsPdf($contracts);
    }
}
