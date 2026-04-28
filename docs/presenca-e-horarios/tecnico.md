# Presença e Banco de Horas - Documentação Técnica

Esta secção detalha o motor de cálculo de tempos, gestão de banco de horas e o sistema de picagens.

## 1. Registos de Presença (`AttendanceLog`)
Armazena as picagens diárias dos funcionários.

- **Modelo:** `app/Models/AttendanceLog.php`
- **Campos de Tempo:** `time_in`, `lunch_break_start`, `lunch_break_end`, `time_out`.
- **Cálculo Automático:** O método `calculateTotalMinutes()` calcula o tempo trabalhado excluindo a pausa de almoço.
  - Se o funcionário não picar a pausa de almoço, o sistema desconta automaticamente a duração prevista no seu contrato.
  - Se a pausa efetuada for inferior à prevista no contrato, o sistema desconta o valor contratual (mínimo obrigatório).

## 2. Banco de Horas (`HourBank`)
Gere o saldo acumulado de horas extras e défices mensalmente.

- **Modelo:** `app/Models/HourBank.php`
- **Atributos:**
  - `balance`: Saldo final do mês (`previous_balance` + `extra_hours_added` - `extra_hours_used`).
  - `extra_hours_added`: Horas trabalhadas além do horário contratual.
  - `extra_hours_used`: Horas deduzidas por atrasos, faltas ou licenças não remuneradas.
- **Propagação:** O saldo final de um mês é automaticamente propagado como `previous_balance` do mês seguinte via `HourBankService::propagate()`.

## 3. Ausências e Deduções (`Absence`)
Entidade de auditoria que justifica por que razão foram deduzidas horas do banco.

- **Modelo:** `app/Models/Absence.php`
- **Tipos de Dedução (`deduction_type`):**
  - `unjustified_absence`: Falta total.
  - `partial_absence`: Atraso ou saída antecipada.
  - `other`: Motivos diversos.

## 4. Serviços de Lógica de Negócio

### `DeductHourBankService`
Gere as regras punitivas e de tolerância:
- **Tolerância:** Atrasos até 15 minutos são ignorados.
- **Atraso (Atraso < 1h):** Deduz o tempo exato de atraso.
- **Falta por Atraso (Atraso > 1h):** Converte o dia numa falta total (deduz `daily_work_minutes`).
- **Regra dos 3 Atrasos:** Se um funcionário tiver 3 atrasos consecutivos (dias úteis), o sistema converte o 3.º atraso numa falta total e remove os 2 anteriores para efeitos de histórico.

### `HourBankService`
Centraliza o recalculo do saldo. É despoletado por Observers (`AttendanceLogObserver`, `AbsenceObserver`) sempre que há alterações nos dados base.

## 5. Interface de Check-in (`AttendanceCheckIn`)
Página Livewire otimizada para picagens rápidas.
- Gere estados dinamicamente: Entrada -> Almoço (Início) -> Almoço (Fim) -> Saída.
- Captura o timestamp do servidor para evitar fraudes de horário no cliente.
