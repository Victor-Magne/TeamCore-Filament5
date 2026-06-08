<?php

/**
 * Árvore de unidades usada em todos os testes:
 *
 *  Direção Geral  (is_main_direction=true)
 *    ├── Direção A  (direction)
 *    │     ├── Dep. RH  (department)
 *    │     │     ├── Sec. Recrutamento  (section)
 *    │     │     │     ├── empChefeSec  [chefe_de_seccao, manager]
 *    │     │     │     └── empColabRec  [colaborador]
 *    │     │     ├── Sec. Formação  (section)
 *    │     │     │     └── empColabForm
 *    │     │     └── empChefeDept  [chefe_de_departamento, manager]
 *    │     ├── Dep. TI  (department)
 *    │     │     └── empColabTI
 *    │     └── empDiretor  [diretor, manager]
 *    ├── Direção B  (direction)  ← ramo isolado
 *    │     └── Dep. Finanças  (department)
 *    │           └── empDirecaoB
 *    └── empGeralDirecao  [diretor_geral, manager]
 */

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('Visibilidade Hierárquica', function () {
    beforeEach(function () {
        Role::findOrCreate('diretor_geral', 'web');
        Role::findOrCreate('diretor', 'web');
        Role::findOrCreate('chefe_de_departamento', 'web');
        Role::findOrCreate('chefe_de_seccao', 'web');
        Role::findOrCreate('colaborador', 'web');

        // Construir a árvore de unidades organizacionais
        $this->direcaoGeral = Unit::factory()->mainDirection()->create(['name' => 'Direção Geral']);

        $this->direcaoA = Unit::factory()->withParent($this->direcaoGeral)->create([
            'name' => 'Direção A',
            'type' => 'direction',
        ]);
        $this->depRH = Unit::factory()->withParent($this->direcaoA)->create([
            'name' => 'Dep. RH',
            'type' => 'department',
        ]);
        $this->secRecrutamento = Unit::factory()->withParent($this->depRH)->create([
            'name' => 'Sec. Recrutamento',
            'type' => 'section',
        ]);
        $this->secFormacao = Unit::factory()->withParent($this->depRH)->create([
            'name' => 'Sec. Formação',
            'type' => 'section',
        ]);
        $this->depTI = Unit::factory()->withParent($this->direcaoA)->create([
            'name' => 'Dep. TI',
            'type' => 'department',
        ]);

        // Ramo isolado — nenhum gestor da Direção A deve ver este ramo
        $this->direcaoB = Unit::factory()->withParent($this->direcaoGeral)->create([
            'name' => 'Direção B',
            'type' => 'direction',
        ]);
        $this->depFinancas = Unit::factory()->withParent($this->direcaoB)->create([
            'name' => 'Dep. Finanças',
            'type' => 'department',
        ]);

        // Garantir que _lft/_rgt estão corretos após toda a construção da árvore
        Unit::fixTree();

        // Criar funcionários e atribuí-los às unidades e como gestores
        $this->empGeralDirecao = Employee::factory()->create(['unit_id' => $this->direcaoGeral->id]);
        $this->direcaoGeral->update(['manager_id' => $this->empGeralDirecao->id]);

        $this->empDiretor = Employee::factory()->create(['unit_id' => $this->direcaoA->id]);
        $this->direcaoA->update(['manager_id' => $this->empDiretor->id]);

        $this->empChefeDept = Employee::factory()->create(['unit_id' => $this->depRH->id]);
        $this->depRH->update(['manager_id' => $this->empChefeDept->id]);

        $this->empChefeSec = Employee::factory()->create(['unit_id' => $this->secRecrutamento->id]);
        $this->secRecrutamento->update(['manager_id' => $this->empChefeSec->id]);

        $this->empColabRec = Employee::factory()->create(['unit_id' => $this->secRecrutamento->id]);
        $this->empColabForm = Employee::factory()->create(['unit_id' => $this->secFormacao->id]);
        $this->empColabTI = Employee::factory()->create(['unit_id' => $this->depTI->id]);
        $this->empDirecaoB = Employee::factory()->create(['unit_id' => $this->depFinancas->id]);

        // Atribuir roles (users são criados automaticamente pelo EmployeeObserver)
        $this->empGeralDirecao->user->assignRole('diretor_geral');
        $this->empDiretor->user->assignRole('diretor');
        $this->empChefeDept->user->assignRole('chefe_de_departamento');
        $this->empChefeSec->user->assignRole('chefe_de_seccao');
        $this->empColabRec->user->assignRole('colaborador');
    });

    // ──────────────────────────────────────────────────────────────────────────
    // DIRETOR GERAL — gere a Direção Geral (is_main_direction), vê tudo
    // ──────────────────────────────────────────────────────────────────────────

    it('diretor geral vê todos os funcionários da organização', function () {
        Auth::login($this->empGeralDirecao->user);

        $ids = EmployeeResource::getEloquentQuery()->pluck('id');

        expect($ids)
            ->toContain($this->empGeralDirecao->id)
            ->toContain($this->empDiretor->id)
            ->toContain($this->empChefeDept->id)
            ->toContain($this->empChefeSec->id)
            ->toContain($this->empColabRec->id)
            ->toContain($this->empColabForm->id)
            ->toContain($this->empColabTI->id)
            ->toContain($this->empDirecaoB->id);
    });

    // ──────────────────────────────────────────────────────────────────────────
    // DIRETOR — gere a Direção A; não deve ver Direção B nem a raiz
    // ──────────────────────────────────────────────────────────────────────────

    it('diretor vê os funcionários da sua direção e de todas as unidades abaixo', function () {
        Auth::login($this->empDiretor->user);

        $ids = EmployeeResource::getEloquentQuery()->pluck('id');

        expect($ids)
            ->toContain($this->empDiretor->id)           // si próprio (Direção A)
            ->toContain($this->empChefeDept->id)         // Dep. RH ⊂ Direção A
            ->toContain($this->empChefeSec->id)          // Sec. Recrutamento ⊂ Dep. RH
            ->toContain($this->empColabRec->id)          // Sec. Recrutamento ⊂ Dep. RH
            ->toContain($this->empColabForm->id)         // Sec. Formação ⊂ Dep. RH
            ->toContain($this->empColabTI->id)           // Dep. TI ⊂ Direção A
            ->not->toContain($this->empGeralDirecao->id) // Direção Geral (acima)
            ->not->toContain($this->empDirecaoB->id);    // Direção B (ramo irmão)
    });

    // ──────────────────────────────────────────────────────────────────────────
    // CHEFE DE DEPARTAMENTO — gere Dep. RH; não vê Dep. TI nem níveis acima
    // ──────────────────────────────────────────────────────────────────────────

    it('chefe de departamento vê os funcionários do seu departamento e das secções abaixo', function () {
        Auth::login($this->empChefeDept->user);

        $ids = EmployeeResource::getEloquentQuery()->pluck('id');

        expect($ids)
            ->toContain($this->empChefeDept->id)         // si próprio (Dep. RH)
            ->toContain($this->empChefeSec->id)          // Sec. Recrutamento ⊂ Dep. RH
            ->toContain($this->empColabRec->id)          // Sec. Recrutamento ⊂ Dep. RH
            ->toContain($this->empColabForm->id)         // Sec. Formação ⊂ Dep. RH
            ->not->toContain($this->empDiretor->id)      // Direção A (acima)
            ->not->toContain($this->empGeralDirecao->id) // Direção Geral (acima)
            ->not->toContain($this->empColabTI->id)      // Dep. TI (irmão do Dep. RH)
            ->not->toContain($this->empDirecaoB->id);    // Direção B (ramo irmão)
    });

    // ──────────────────────────────────────────────────────────────────────────
    // CHEFE DE SECÇÃO — gere Sec. Recrutamento; não vê Sec. Formação nem acima
    // ──────────────────────────────────────────────────────────────────────────

    it('chefe de secção vê apenas os colaboradores da sua secção', function () {
        Auth::login($this->empChefeSec->user);

        $ids = EmployeeResource::getEloquentQuery()->pluck('id');

        expect($ids)
            ->toContain($this->empChefeSec->id)          // si próprio (Sec. Recrutamento)
            ->toContain($this->empColabRec->id)          // colaborador na mesma secção
            ->not->toContain($this->empChefeDept->id)    // Dep. RH (acima)
            ->not->toContain($this->empColabForm->id)    // Sec. Formação (secção irmã)
            ->not->toContain($this->empColabTI->id)      // Dep. TI (ramo irmão)
            ->not->toContain($this->empDiretor->id)      // Direção A (acima)
            ->not->toContain($this->empGeralDirecao->id) // Direção Geral (raiz)
            ->not->toContain($this->empDirecaoB->id);    // Direção B (ramo irmão)
    });

    // ──────────────────────────────────────────────────────────────────────────
    // COLABORADOR — sem unidades geridas; vê apenas a si próprio
    // ──────────────────────────────────────────────────────────────────────────

    it('colaborador vê apenas os seus próprios registos', function () {
        Auth::login($this->empColabRec->user);

        $ids = EmployeeResource::getEloquentQuery()->pluck('id');

        expect($ids)
            ->toContain($this->empColabRec->id)
            ->not->toContain($this->empChefeSec->id)
            ->not->toContain($this->empChefeDept->id)
            ->not->toContain($this->empColabForm->id)
            ->not->toContain($this->empColabTI->id)
            ->not->toContain($this->empDiretor->id)
            ->not->toContain($this->empGeralDirecao->id)
            ->not->toContain($this->empDirecaoB->id);
    });
});
