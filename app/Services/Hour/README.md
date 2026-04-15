# Banco de Horas - Sistema de Gestão

Este módulo gerencia o banco de horas dos funcionários, registando horas extras e descontando horas em caso de faltas.

## Conceitos

- **Jornada Diária Padrão**: 8 horas (480 minutos)
- **Horas Extras**: Todo tempo trabalhado acima de 8 horas é registado como hora extra no banco
- **Banco de Horas**: Saldo acumulado de horas extras por funcionário durante um mês
- **Saldo Negativo**: Quando o funcionário falta, o saldo fica negativo (deve horas)
- **Validação de Licenças**: Sistema verifica automaticamente se há licenças/férias registadas antes de descontar horas

## Estrutura de Dados

### HourBank (Banco de Horas)
- `employee_id`: Funcionário
- `month_year`: Referência do mês (YYYY-MM)
- `balance`: Saldo total em minutos (pode ser negativo)
- `extra_hours_added`: Horas extras adicionadas este mês
- `extra_hours_used`: Horas descontadas este mês
- `previous_balance`: Saldo do mês anterior (para referência)

### Absence (Registro de Faltas)
- `employee_id`: Funcionário
- `leave_and_absence_id`: Referência opcional à licença
- `absence_date`: Data da falta
- `hours_deducted`: Minutos descontados (480 = 1 dia completo)
- `deduction_type`: Tipo de dedução (falta injustificada, parcial, etc.)

## Configuração

Editar `config/hour_bank.php` para customizar:

- `validate_leaves_before_deduction`: Ativar/desativar validação (padrão: true)
- `justified_leave_types`: Licenças que NÃO descontam (sick_leave, parental, etc.)
- `unjustified_leave_types`: Faltas que DESCONTAM (unjustified)
- `daily_work_hours`: Horas/dia em minutos (padrão: 480)

Ou via `.env`:
```
HOUR_BANK_VALIDATE_LEAVES=true
```

## Como Usar

### 1. Adicionar Horas Extras Automaticamente

Quando um `AttendanceLog` é criado com horas trabalhadas > 8h, o sistema adiciona automaticamente as horas extras ao banco através do hook `saved()`.

```php
// Exemplo: Criar um registo de ponto
$attendance = AttendanceLog::create([
    'employee_id' => 1,
    'time_in' => '2026-04-15 09:00:00',
    'lunch_break_start' => '2026-04-15 12:30:00',
    'lunch_break_end' => '2026-04-15 13:30:00',
    'time_out' => '2026-04-15 18:00:00',
    // total_minutes = 8h 30m = 510 minutos
    // horas extras = 510 - 480 = 30 minutos
]);

// O banco de horas será atualizado automaticamente!
$hourBank = $employee->getCurrentHourBankBalance();
// balance = 30 minutos (0.5h)
```

### 2. Registar Uma Falta Injustificada

O sistema verifica automaticamente se há licenças ou férias registadas **antes de descontar**.

```php
use App\Services\Hour\DeductHourBankService;

$deductService = new DeductHourBankService();

// Falta de 1 dia completo
// Se validação está ativa E há leave/vacation aprovada: NÃO DESCONTA
// Se for falta injustificada: DESCONTA
$deductService->handle(
    employeeId: 1,
    absenceDate: now(),
    hoursToDeduct: 480, // 1 dia = 8 horas
    deductionType: 'unjustified_absence',
    reason: 'Falta injustificada'
);

// Falta parcial (meia tarde)
$deductService->handle(
    employeeId: 1,
    absenceDate: now(),
    hoursToDeduct: 240, // 4 horas
    deductionType: 'partial_absence',
    reason: 'Saída antecipada sem justificação'
);

// Forçar desconto mesmo com leave/vacation registada
// Usar quando necessário sobrepor a validação
$deductService->handle(
    employeeId: 1,
    absenceDate: now(),
    hoursToDeduct: 480,
    deductionType: 'unjustified_absence',
    reason: 'Falta não comunicada',
    forceDeduction: true // ⚠️ Força o desconto
);
```

### 3. Comportamento de Validação

**Quando validação está ATIVADA** (`validate_leaves_before_deduction = true`):

| Cenário | Ação |
|---------|------|
| Dia com **leave justificada** (sick, parental, marriage, bereavement) | ❌ NÃO desconta |
| Dia com **férias aprovadas** | ❌ NÃO desconta |
| Dia com **falta injustificada** | ✅ DESCONTA |
| Dia com **leave injustificada** | ✅ DESCONTA |
| Com `forceDeduction: true` | ✅ SEMPRE desconta (ignora validação) |

**Quando validação está DESATIVADA** (`validate_leaves_before_deduction = false`):
- ✅ Sempre desconta conforme solicitado

### 4. Registar Faltas em Período

Para múltiplos dias de falta:

```php
$deductService = new DeductHourBankService();

// Falta de 3 dias (segunda a quarta)
// Valida se há leaves/vacations em cada dia
$absences = $deductService->handlePeriod(
    employeeId: 1,
    startDate: now()->startOfWeek(),
    endDate: now()->startOfWeek()->addDays(2),
    deductionType: 'unjustified_absence',
    reason: 'Falta justificada por motivo pessoal'
);
// Desconta apenas dias úteis (segunda-sexta) sem leave/vacation
// Se quiser forçar: forceDeduction: true
```

### 5. Verificar Saldo do Banco de Horas

```php
$employee = Employee::find(1);

// Saldo do mês atual
$currentMonth = $employee->getCurrentHourBankBalance();
if ($currentMonth) {
    echo "Saldo atual: {$currentMonth->formatted_balance}"; // Ex: "1h 30m"
    echo "Horas extras: {$currentMonth->formatted_extra_hours_added}";
    echo "Horas usadas: {$currentMonth->formatted_extra_hours_used}";
}

// Saldo total acumulado (todos os meses)
$totalBalance = $employee->getTotalHourBankBalance();
echo "Saldo acumulado: {$totalBalance} minutos";
```

### 6. Listar Histórico de Faltas

```php
$employee = Employee::find(1);

// Todas as faltas
$absences = $employee->absences;

// Faltas de um período
$absences = $employee->absences()
    ->whereBetween('absence_date', [
        now()->startOfMonth(),
        now()->endOfMonth()
    ])
    ->get();
```

### 7. Gerenciar Validação de Licenças

#### Desativar Validação Globalmente

Editar `.env`:
```env
HOUR_BANK_VALIDATE_LEAVES=false
```

Agora o sistema sempre descontará conforme solicitado, sem verificar leaves/vacations.

#### Customizar Tipos de Licenças

Editar `config/hour_bank.php`:

```php
'justified_leave_types' => [
    'sick_leave',           // Baixa Médica
    'parental',             // Licença Parental
    'marriage',             // Casamento
    'bereavement',          // Falecimento
    'justified_absence',    // Falta Justificada
    'custom_leave',         // Seu tipo customizado
],

'unjustified_leave_types' => [
    'unjustified',
    'other_unjustified',
],
```

#### Forçar Desconto em Caso Específico

Quando validação está ativa mas precisa descontar mesmo com leave registada:

```php
$deductService = new DeductHourBankService();

$deductService->handle(
    employeeId: 1,
    absenceDate: now(),
    hoursToDeduct: 480,
    deductionType: 'unjustified_absence',
    reason: 'Falta não comunicada - desconto forçado',
    forceDeduction: true // ⚠️ Ignora validação de leaves/vacations
);
```

## Fluxo de Integração

### Com Filament Resource

Para integrar com a interface Filament, você pode:

1. **Criar Actions** para registar faltas manualmente
2. **Adicionar Widgets** para mostrar saldo atual
3. **Criar Tables/Forms** para gerenciar banco de horas

Exemplo de Action em um Resource:

```php
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;

Action::make('registerAbsence')
    ->form([
        DatePicker::make('absence_date')
            ->label('Data da Falta')
            ->required(),
        TextInput::make('hours_to_deduct')
            ->label('Horas a Descontar')
            ->numeric()
            ->default(480)
            ->suffix('minutos'),
        Textarea::make('reason')
            ->label('Motivo'),
    ])
    ->action(function (array $data, Employee $record) {
        $service = new DeductHourBankService();
        $service->handle(
            $record->id,
            Carbon::parse($data['absence_date']),
            $data['hours_to_deduct'],
            'unjustified_absence',
            $data['reason']
        );
    });
```

## Notas Importantes

- **Saldo Negativo**: Indica que o funcionário deve horas à empresa
- **Período Único**: O banco é organizado por mês
- **Dias Úteis**: Ao registar faltas em período, apenas dias úteis (seg-sex) são contados
- **Auditoria**: Toda alteração fica registada em `Absence` para referência futura

## Próximas Funcionalidades (Sugestões)

- [ ] Aprovação de horas extras by manager
- [ ] Relatórios de banco de horas por mês
- [ ] Exportação Excel do histórico
- [ ] Notificações quando saldo fica negativo
- [ ] Resgate de horas (convertendo em dias de folga)
