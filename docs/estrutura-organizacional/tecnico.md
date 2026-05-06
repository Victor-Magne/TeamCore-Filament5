# Estrutura Organizacional - Documentação Técnica

Esta secção detalha como a hierarquia da empresa, cargos e localizações estão estruturados na base de dados e lógica da aplicação.

## 1. Unidades Organizacionais (`Unit`)
As unidades organizacionais definem a estrutura da empresa (Direções, Departamentos, Secções).

- **Modelo:** `app/Models/Unit.php` (Tabela: `organizational_units`)
- **Campos Principais:**
  - `name`: Nome da unidade.
  - `type`: Tipo de unidade (ex: direction, department, section).
  - `parent_id`: Chave estrangeira para a unidade pai (auto-relacionamento), permitindo uma hierarquia infinita.
  - `manager_id`: Funcionário responsável pela unidade.
  - `is_main_direction`: Booleano para identificar a unidade de topo.
- **Métodos Auxiliares:**
  - `getAllDescendantIds()`: Obtém recursivamente todos os IDs das sub-unidades.
  - `isGeneralDirection()`: Verifica se é a unidade de topo da organização.
- **Relacionamentos:**
  - `parent`: Unidade de nível superior.
  - `children`: Unidades subordinadas.
  - `manager`: Relacionamento com `Employee`.
  - `managers`: Relacionamento muitos-para-muitos (via `unit_manager`) para suporte a múltiplos gestores.
  - `employees`: Funcionários alocados a esta unidade.

## 2. Visibilidade Hierárquica e Atribuição Composta
A visibilidade de dados na aplicação é gerida centralmente através de Traits que aplicam filtros automáticos às queries e verificações de acesso.

### Lógica de Query (`HasHierarchicalQuery`)
Implementada em `app/Traits/HasHierarchicalQuery.php`, esta trait altera o `getEloquentQuery` para:
- **Direção Geral**: Se um utilizador gere a Unidade marcada como `is_main_direction`, vê todos os registos.
- **Gestão Recursiva**: Managers vêem os seus próprios dados e os de todos os funcionários alocados às unidades que gerem, incluindo todas as sub-unidades (descendentes).
- **Atribuição Composta**: Um funcionário pode gerir múltiplas unidades através da relação direta (`manager_id` na `Unit`) ou da tabela pivot `unit_manager`. O sistema unifica estas permissões.

### Lógica de Policy (`HasHierarchicalPolicy`)
Localizada em `app/Traits/HasHierarchicalPolicy.php`, garante que o acesso direto a registos (via ID) respeita a mesma hierarquia organizacional.

## 3. Cargos (`Designation`)
Define os títulos profissionais e os salários base associados.

- **Modelo:** `app/Models/Designation.php`
- **Campos Principais:**
  - `name`: Nome do cargo (ex: Engenheiro de Software).
  - `level`: Nível de senioridade (ex: junior, pleno, senior, lead).
  - `base_salary`: Salário base de referência para este cargo.
- **Lógica Associada:** O salário base da designação é utilizado como valor por defeito no processamento salarial caso o funcionário não tenha um contrato ativo com salário específico.

## 3. Localização (Países, Estados e Cidades)
Sistema de localização em cascata para preenchimento de dados de morada dos funcionários.

- **Modelos:** `Country`, `State`, `City`.
- **Hierarquia:** `Country` -> tem muitos `States` -> cada um tem muitas `Cities`.
- **Uso:** Implementado nos formulários de `Employee` com seletores dependentes (reactive) para garantir integridade geográfica.

## 4. Políticas de Autorização
- `UnitPolicy`: Controla quem pode criar ou editar a estrutura da empresa. Normalmente restrito ao papel `ADMIN`.
- `DesignationPolicy`: Gere os cargos e níveis salariais.
- `CountryPolicy`, `StatePolicy`, `CityPolicy`: Geralmente geridos apenas por `ADMIN` para manter a base de dados de localizações limpa.

## 5. Auditoria
Todas estas entidades implementam a trait `LogsActivity`, registando qualquer alteração na estrutura da empresa no `ActivityLog`.

## 6. Validação Automatizada
As regras descritas nesta secção são cobertas pela suíte automatizada com estado atual de `100` testes passados e `222` assertions (`php artisan test --compact`).
