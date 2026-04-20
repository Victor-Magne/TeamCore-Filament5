<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractPdfService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContractPdfController extends Controller
{
    use AuthorizesRequests;

    /**
     * Download a single contract as PDF
     */
    public function downloadSingle(Contract $contract)
    {
        $this->authorize('view', $contract);

        return (new ContractPdfService)
            ->downloadSingleContractPdf($contract);
    }

    /**
     * Download all contracts as PDF report
     */
    public function downloadAll()
    {
        $this->authorize('viewAny', Contract::class);

        $contracts = Contract::all();

        return (new ContractPdfService)
            ->downloadMultipleContractsPdf($contracts);
    }

    /**
     * Download selected contracts as PDF report
     */
    public function downloadBulk()
    {
        $this->authorize('viewAny', Contract::class);

        $ids = array_filter(explode(',', request('ids', '')));

        if (empty($ids)) {
            abort(400, 'Nenhum contrato selecionado.');
        }

        $contracts = Contract::whereIn('id', $ids)->get();

        return (new ContractPdfService)
            ->downloadMultipleContractsPdf($contracts);
    }
}
