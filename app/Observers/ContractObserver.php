<?php

/**
 * Ficheiro do Observer ContractObserver.
 *
 * Este observer sincroniza dados entre o contrato e o perfil do funcionário.
 * Sempre que um contrato é criado ou actualizado, garante que o cargo (designation)
 * actual do funcionário reflecte o que está definido no seu contrato activo.
 */

namespace App\Observers;

use App\Models\Contract;

class ContractObserver
{
    /**
     * Manipula o evento "created" do Modelo Contract.
     *
     * @param  Contract  $contract  O contrato criado.
     */
    public function created(Contract $contract): void
    {
        // Se o contrato tiver uma designação, actualiza o perfil do funcionário
        if ($contract->employee && $contract->designation_id) {
            $contract->employee->update([
                'designation_id' => $contract->designation_id,
            ]);
        }
    }

    /**
     * Manipula o evento "updated" do Modelo Contract.
     *
     * @param  Contract  $contract  O contrato actualizado.
     */
    public function updated(Contract $contract): void
    {
        // Sincroniza a designação se esta tiver sido alterada no contrato
        if ($contract->employee && $contract->designation_id) {
            $contract->employee->update([
                'designation_id' => $contract->designation_id,
            ]);
        }
    }
}
