# Gestão de Colaboradores e Contratos - Documentação Técnica

Esta secção descreve a lógica de negócio e as automações associadas ao ciclo de vida dos funcionários.

## 1. Funcionários (`Employee`)
O modelo `Employee` centraliza a informação pessoal e profissional do colaborador.

- **Modelo:** `app/Models/Employee.php`
- **Atributos Relevantes:**
  - `full_name`: Atributo dinâmico (accessor) que combina primeiro e último nome.
  - `vacation_balance`: Saldo de dias de férias acumulados.
  - `date_hired`: Data de contratação, usada como base para cálculos de antiguidade e banco de horas.
- **Relacionamentos:** Possui ligações com `User`, `Contract`, `Unit`, `Designation`, `Vacation`, `LeaveAndAbsence`, `HourBank`, `AttendanceLog`, `Absence` e `Payroll`.

## 2. Contratos (`Contract`)
Define as condições laborais específicas de um funcionário num período de tempo.

- **Modelo:** `app/Models/Contract.php`
- **Campos Principais:**
  - `type`: Tipo de contrato (ex: permanent, fixed_term, service_provision).
  - `salary`: Valor da remuneração mensal.
  - `daily_work_minutes`: Minutos de trabalho esperados por dia (normalmente 480 para 8h).
  - `expected_start_time`: Hora prevista de entrada para controlo de atrasos.
  - `lunch_duration_minutes`: Duração da pausa para almoço.
  - `status`: Estado do contrato (active, terminated, on_hold).

## 3. Automações (Observers)

### `EmployeeObserver`
Ao criar um registo de `Employee`, o sistema delega a lógica atómica para o `EmployeeOnboardingService`, que executa:
1.  **Criação de Utilizador:** Gera um `User` com o e-mail do funcionário, papel `employee` e obriga à troca de palavra-passe no primeiro acesso.
2.  **Criação de Contrato Inicial:** Gera um contrato por defeito (fixed_term) com o salário base da designação escolhida.
3.  **Criação de Banco de Horas:** Inicializa o `HourBank` cumulativo para o funcionário com saldo zero.

### `ContractObserver`
Sempre que um contrato é criado ou atualizado, o sistema sincroniza o campo `designation_id` no modelo `Employee`. Isto garante que o cargo atual do funcionário reflete sempre o seu contrato ativo.

## 4. Geração de PDF de Contrato
O sistema utiliza o `ContractPdfService` para gerar documentos PDF formatados.
- **Serviço:** `app/Services/ContractPdfService.php`.
- **Implementação:** Utiliza `barryvdh/laravel-dompdf` para renderizar uma view Blade específica para o formato oficial de contrato da empresa.
- **Acesso:** Disponível no `ContractResource` via `Action` de download.

## 5. Políticas de Segurança
`EmployeePolicy` e `ContractPolicy` garantem que:
- Funcionários apenas veem os seus próprios dados.
- Gestores de RH veem dados de funcionários da sua área de responsabilidade.
- Administradores têm acesso global.
