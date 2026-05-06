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
Gere o saldo acumulado de horas extras e défices de forma cumulativa. Ao contrário de sistemas mensais tradicionais, o TeamCore utiliza um único registo de saldo por funcionário que evolui ao longo do tempo.

- **Modelo:** `app/Models/HourBank.php`
- **Atributos:**
  - `balance`: Saldo total acumulado em minutos.
  - `extra_hours_added`: Total histórico de minutos ganhos.
  - `extra_hours_used`: Total histórico de minutos utilizados ou descontados.
- **Movimentos (`HourBankMovement`):** Cada alteração ao saldo é registada nesta tabela polimórfica (`source_type`, `source_id`), permitindo uma auditoria completa e o cálculo de deltas durante atualizações para evitar erros de saldo.

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
Centraliza a lógica de atualização do saldo. Utiliza um sistema de deltas para garantir que edições em registos passados de presença ou ausência atualizam o saldo de forma incremental e precisa, sem necessidade de recalcular todo o histórico.

## 5. Comandos de Consola e Manutenção
- `app:check-daily-attendance`: Comando agendado que verifica retrospectivamente (por defeito, o dia anterior) se os funcionários ativos realizaram picagens. Em caso de ausência sem licença aprovada, regista automaticamente uma falta injustificada.
- `app:sync-hour-bank`: Comando de manutenção que remove movimentos órfãos e recalcula o saldo do banco de horas com base na soma real de todos os movimentos válidos, garantindo a integridade do sistema.

## 6. Interface de Check-in (`AttendanceCheckIn`)
Página Livewire otimizada para picagens rápidas.
- Gere estados dinamicamente: Entrada -> Almoço (Início) -> Almoço (Fim) -> Saída.
- Captura o timestamp do servidor para evitar fraudes de horário no cliente.
