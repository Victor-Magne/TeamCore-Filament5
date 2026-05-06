# Processamento Salarial - Documentação Técnica

Esta secção descreve o motor de cálculo financeiro da Aplicação TeamCore.

## 1. Registo Salarial (`Payroll`)
Representa o processamento de vencimento de um funcionário para um mês específico.

- **Modelo:** `app/Models/Payroll.php`
- **Campos Principais:**
  - `base_salary`: Vencimento base definido no contrato.
  - `hourly_rate`: Valor calculado por hora normal.
  - `extra_hours`: Total de minutos extra ganhos no banco de horas.
  - `extra_hours_amount`: Valor monetário das horas extra.
  - `deductions`: Valor monetário deduzido por horas em falta.
  - `total_net`: Salário final calculado (`base_salary` + `extra_hours_amount` - `deductions`).

## 2. Serviço de Cálculo (`GeneratePayrollService`)
O serviço `app/Services/Payroll/GeneratePayrollService.php` contém a lógica financeira central.

### Fórmulas Utilizadas:
1.  **Valor Hora Normal (`hourly_rate`):**
    `Salário Bruto / (Horas Diárias Contratuais × 22 Dias Úteis)`
2.  **Valor Horas Extra (`extra_hours_amount`):**
    `(Valor Hora × 1.5) × (Minutos Extra Registados no Período / 60)`
    *Nota: Aplica-se um coeficiente de 1.5 (50% de acréscimo). O cálculo baseia-se nos movimentos do banco de horas para o mês em questão.*
3.  **Deduções:**
    `Valor Hora × (Minutos em Falta Registados no Período / 60)`

## 3. Processo de Execução
O processamento é iniciado via `PayrollResource` através de uma **Header Action** ("Processar Salários").
- O sistema percorre todos os funcionários ativos (`date_dismissed` nulo).
- Invoca o `GeneratePayrollService` para cada um, criando ou atualizando o registo de `Payroll` para o mês/ano selecionado.
- Os totais de horas extra e faltas são obtidos somando os movimentos do `HourBankMovement` que ocorreram no mês alvo.

## 4. Estados do Processamento
Os registos de `Payroll` possuem um campo `status`:
- `pending`: Acabado de gerar, aguarda revisão.
- `processed`: Revisão concluída, pronto para pagamento.
- `paid`: Pagamento efetuado.

## 5. Auditoria
Como todas as entidades financeiras, o `Payroll` é auditado. Qualquer alteração manual num valor processado automaticamente é registada no log de atividade com os valores antigo e novo.

## 6. Validação Automatizada
As fórmulas e regras de processamento salarial desta secção estão validadas na suíte atual com `100` testes passados e `222` assertions (`php artisan test --compact`).
