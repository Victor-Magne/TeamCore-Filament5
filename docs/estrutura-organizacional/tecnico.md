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
- **Relacionamentos:**
  - `parent`: Unidade de nível superior.
  - `children`: Unidades subordinadas.
  - `manager`: Relacionamento com `Employee`.
  - `employees`: Funcionários alocados a esta unidade.

## 2. Cargos (`Designation`)
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
