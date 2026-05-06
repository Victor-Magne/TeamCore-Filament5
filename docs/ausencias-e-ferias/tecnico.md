# Gestão de Férias e Ausências - Documentação Técnica

Esta secção descreve como o sistema gere os períodos de descanso e as ausências justificadas dos colaboradores.

## 1. Gestão de Férias (`Vacation`)
Gere os pedidos de férias anuais e o respetivo saldo.

- **Modelo:** `app/Models/Vacation.php`
- **Lógica de Saldo:**
  - O sistema calcula automaticamente os dias gozados (`days_taken`) com base nas datas de início e fim.
  - **Aprovação:** Ao passar o estado para `approved`, o sistema debita automaticamente os dias do `vacation_balance` do funcionário.
  - **Cancelamento/Rejeição:** Se um pedido aprovado for rejeitado ou eliminado, o sistema restaura os dias ao saldo do funcionário.
- **Year Reference:** Cada pedido é associado a um ano fiscal (por defeito, o ano da data de início).

## 2. Licenças e Ausências (`LeaveAndAbsence`)
Gere ausências justificadas (doença, parentalidade, casamento, etc.).

- **Modelo:** `app/Models/LeaveAndAbsence.php` (Tabela: `leaves_and_absences`)
- **Atributos Principais:**
  - `type`: Categoria da licença (ex: sick_leave, marriage, justified_absence).
  - `is_paid`: Define se a licença é remunerada.
  - `justification_doc`: Caminho para o ficheiro de comprovativo (upload).
- **Observer (`LeaveAndAbsenceObserver`):**
  - Quando uma licença é aprovada:
    - Se **não for paga**, o sistema cria automaticamente registos de `Absence` para os dias correspondentes, descontando-os do banco de horas.
    - Se **for paga**, o sistema remove qualquer `Absence` automática (atrasos ou faltas) que tenha sido gerada pelo sistema de ponto para esses dias via eliminação de instâncias (para gatilhar o `AbsenceObserver`).
    - Despoleta o recalculo incremental do banco de horas.

## 3. Fluxo de Aprovação e Conflitos
- **Aprovação:** O campo `approved_by` é preenchido automaticamente com o ID do utilizador autenticado no momento da aprovação.
- **Conflito de Interesses:** As políticas (`VacationPolicy` e `LeaveAndAbsencePolicy`) e o frontend bloqueiam a capacidade de um utilizador aprovar os seus próprios pedidos, a menos que possuam a permissão específica `Approve:OwnVacation` / `Approve:OwnLeaveAndAbsence`.

## 4. Integração com o Ponto
O `DeductHourBankService` verifica sempre se existe uma férias ou licença aprovada antes de gerar faltas automáticas por falta de picagem de ponto. Isto evita penalizações indevidas quando o colaborador está legitimamente ausente.

## 5. Auditoria
Todas as alterações de estado (pendente -> aprovado/rejeitado) são registadas com o respetivo motivo de rejeição, se aplicável, no log de atividade.
