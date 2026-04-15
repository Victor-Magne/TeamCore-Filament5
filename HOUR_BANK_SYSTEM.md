# 🏦 Sistema de Banco de Horas - Implementação Completa

## 📋 Arquivos Criados

### Migrations
1. **`database/migrations/2026_04_15_082226_create_hour_banks_table.php`**
   - Tabela para armazenar saldo de horas por funcionário/mês
   - Campos: balance, extra_hours_added, extra_hours_used, previous_balance

2. **`database/migrations/2026_04_15_082237_create_absences_table.php`**
   - Tabela para registar ausências/faltas
   - Campos: employee_id, absence_date, hours_deducted, deduction_type

### Models
1. **`app/Models/HourBank.php`**
   - Relacionamentos com Employee
   - Métodos formatados para visualização (formatted_balance, formatted_extra_hours_added, etc.)

2. **`app/Models/Absence.php`**
   - Relacionamentos com Employee e LeaveAndAbsence
   - Método formatado para horas descontadas

### Services
1. **`app/Services/Hour/CalculateExtraHoursService.php`**
   - Calcula horas extras quando total_minutes > 480 minutos
   - Cria/atualiza HourBank automaticamente
   - Carrega saldo do mês anterior

2. **`app/Services/Hour/DeductHourBankService.php`**
   - Desconta horas quando funcionário falta
   - ✨ **Novo**: Valida licenças/férias antes de descontar (ativável/desativável)
   - Suporta descontos simples e em período
   - Apenas conta dias úteis (seg-sex)
   - Parâmetro `forceDeduction` para sobrepor validação

### Config
1. **`config/hour_bank.php`** (NOVO)
   - Ativar/desativar validação de licenças
   - Customizar tipos de licenças justificadas
   - Configurar jornada diária padrão

### Widgets Filament
1. **`app/Filament/Widgets/HourBankStatsWidget.php`**
   - Widget para dashboard mostrando:
     - Saldo atual (mês)
     - Horas extras adicionadas
     - Horas descontadas
     - Saldo acumulado

### Documentação
1. **`app/Services/Hour/README.md`**
   - Guia completo de uso
   - Exemplos de código
   - Use cases

## 🔧 Mudanças em Arquivos Existentes

### `app/Models/AttendanceLog.php`
- ✅ Adicionado hook `saved()` que chama `CalculateExtraHoursService`
- ✅ Horas extras são calculadas automaticamente

### `app/Models/Employee.php`
- ✅ Adicionado relacionamento `hourBanks()`
- ✅ Adicionado relacionamento `absences()`
- ✅ Adicionado método `getCurrentHourBankBalance()`
- ✅ Adicionado método `getTotalHourBankBalance()`

## 🚀 Próximas Etapas

### 1. Configurar (Opcional)

Editar `config/hour_bank.php` para customizar:

```php
// Ativar/desativar validação de licenças
'validate_leaves_before_deduction' => true,

// Licenças que NÃO descontam horas
'justified_leave_types' => [
    'sick_leave',       // Baixa Médica
    'parental',         // Licença Parental
    'marriage',         // Casamento
    'bereavement',      // Falecimento
    'justified_absence', // Falta Justificada
],

// Faltas que DESCONTAM horas
'unjustified_leave_types' => [
    'unjustified', // Falta Injustificada
],
```

Ou via `.env`:
```
HOUR_BANK_VALIDATE_LEAVES=true
```

### 2. Executar Migrations
```bash
php artisan migrate
```

### 3. Testar o Sistema (Opcional)
```bash
# Exemplo em Tinker
php artisan tinker

# Criar um funcionário e um registo de ponto com horas extras
$employee = \App\Models\Employee::first();
$attendance = \App\Models\AttendanceLog::create([
    'employee_id' => $employee->id,
    'time_in' => now('09:00'),
    'lunch_break_start' => now('12:30'),
    'lunch_break_end' => now('13:30'),
    'time_out' => now('18:30'), // 9h de trabalho = 1h extra
]);

// Verificar banco de horas
$hourBank = $employee->getCurrentHourBankBalance();
echo $hourBank->formatted_balance; // "1h 0m"
```

### 4. Integração com Filament

#### Opção A: Adicionar Widget ao Dashboard
No seu dashboard ou Resource, adicione o `HourBankStatsWidget`:

```php
// Em app/Filament/Pages/Dashboard.php ou equivalente
protected function getHeaderWidgets(): array
{
    return [
        \App\Filament\Widgets\HourBankStatsWidget::class,
    ];
}
```

#### Opção B: Criar Resource para Gerencial de Banco de Horas
```bash
php artisan make:filament-resource HourBank
```

## ✨ Validação de Licenças e Férias (NOVO)

O sistema agora **verifica automaticamente** se existe uma licença ou férias registada **antes de descontar horas do banco**.

### Como Funciona?

| Scenario | Validação Ativa | Validação Desativa |
|----------|---|---|
| Dia com **leave justificada** (sick, parental, etc) | ❌ NÃO desconta | ✅ Desconta |
| Dia com **férias aprovadas** | ❌ NÃO desconta | ✅ Desconta |
| Dia com **falta injustificada** | ✅ DESCONTA | ✅ Desconta |
| Com `forceDeduction: true` | ✅ DESCONTA (ignora validação) | ✅ Desconta |

### Exemplo

```php
use App\Services\Hour\DeductHourBankService;

$deductService = new DeductHourBankService();
$employee = Employee::find(1);

// 📅 Cenário: Funcionário tem férias aprovadas para 2026-04-20
// O sistema detecta e NÃO desconta

$deductService->handle(
    employeeId: 1,
    absenceDate: Carbon::parse('2026-04-20'),
    hoursToDeduct: 480,
    deductionType: 'unjustified_absence',
    reason: 'Falta' // Mas não desconta porque tem férias!
);
// Resultado: ✅ Absence registada, ❌ HourBank não alterado

// Para forçar o desconto (em caso de erro):
$deductService->handle(
    employeeId: 1,
    absenceDate: Carbon::parse('2026-04-20'),
    hoursToDeduct: 480,
    deductionType: 'unjustified_absence',
    reason: 'Desconto corretivo',
    forceDeduction: true // ⚠️ Força desconto mesmo com férias
);
// Resultado: ✅ Absence registada, ✅ HourBank descontado
```

### Customizar Comportamento

**Desativar globalmente**:
```env
HOUR_BANK_VALIDATE_LEAVES=false
```

**Ajustar tipos de licenças justificadas** em `config/hour_bank.php`:
```php
'justified_leave_types' => [
    'sick_leave',
    'parental',
    'marriage',
    'bereavement',
    'justified_absence',
    'seu_tipo_customizado',
],
```

## 📊 Fluxo de Funcionamento

```
┌─────────────────────────┐
│  Criar AttendanceLog    │
│ (entrada, saída, etc)   │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────┐
│ Hook saved() dispara    │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────┐
│ Calcular total_minutes  │
│ (entrada - saída - almoço)
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────┐
│ CalculateExtraHours:    │
│ total > 480 min?        │
└──────────┬──────────────┘
      ┌────┴────┐
      │          │
      ▼          ▼
   YES          NO
      │          │
      ▼          ▼
  Atualizar   Registar
  HourBank    sem mudança
      │
      ▼
┌─────────────────────────┐
│ Saldo atualizado! ✅    │
└─────────────────────────┘
```

```
┌─────────────────────────┐
│   Registar Falta        │
│  (DeductHourBankService)│
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────┐
│ Descontar 480 minutos   │
│ (8h = 1 dia)            │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────┐
│ Criar registo Absence   │
│ + Atualizar HourBank    │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────┐
│ Saldo fica negativo ❌  │
│ (funcionário deve horas)│
└─────────────────────────┘
```

## 🎯 Casos de Uso

### Caso 1: Funcionário Trabalha 10h
```
- Entrada: 09:00
- Saída Almoço: 12:30
- Volta Almoço: 13:30
- Saída: 19:00

Total = 10h - 1h almoço = 9h = 540 minutos
Horas extras = 540 - 480 = 60 minutos (1h)

✅ HourBank.balance = +60 minutos
```

### Caso 2: Funcionário Falta Manhã Inteira
```
- Descontar: 240 minutos (4 horas = meia jornada)
- DeductHourBankService::handle(
    employeeId: 1,
    hoursToDeduct: 240,
    deductionType: 'partial_absence'
  )

❌ HourBank.balance = -240 minutos (deve)
```

### Caso 3: Funcionário de Férias 3 Dias
```
- handlePeriod(
    startDate: segunda,
    endDate: quarta,
    deductionType: 'justified_leave'
  )

Desconta: 3 dias × 480 min = 1.440 minutos (3 dias)
❌ HourBank.balance diminui 1.440 minutos
```

### Caso 4: Funcionário com Férias Aprovadas (NOVO)
```
Cenário: 
- Funcionário tem férias aprovadas de 2026-04-20 a 2026-04-24
- Tentamos registar falta em 2026-04-21

$deductService->handle(
    employeeId: 1,
    absenceDate: Carbon::parse('2026-04-21'),
    hoursToDeduct: 480,
    deductionType: 'unjustified_absence'
);

Resultado:
✅ Absence registada (para auditoria)
❌ HourBank NÃO alterado (tem férias aprovadas!)
```

### Caso 5: Licença Justificada vs Falta Injustificada (NOVO)
```
Cenário A - Baixa Médica (Justificada):
- LeaveAndAbsence criada com type: 'sick_leave'
- Tentamos descontar horas

Resultado: ❌ NÃO desconta (é justificada)

Cenário B - Falta Injustificada:
- SEM LeaveAndAbsence registada
- Tentamos descontar horas

Resultado: ✅ DESCONTA (falta injustificada)
```

## 📝 Notas Importantes

⚠️ **Saldo Negativo = Débito**: O funcionário deve horas à empresa
✅ **Saldo Positivo = Crédito**: Funcionário tem horas extras acumuladas
📅 **Período Mensal**: Cada mês tem seu próprio saldo (permite análise comparativa)
🔄 **Saldo Carregável**: O saldo anterior é carregado automaticamente

## 🐛 Troubleshooting

**P: Horas extras não sendo calculadas?**
- R: Verifique se o `time_out` está preenchido no AttendanceLog
- R: Verifique se `total_minutes` > 480

**P: Erro ao descontar horas?**
- R: Certifique-se que `employee_id` existe
- R: Verifique se a data está no formato correto

**P: Como resetar o banco de horas?**
- R: Use `HourBank::truncate()` em Tinker (cuidado!)
- R: Ou delete registos específicos: `HourBank::where('month_year', '2026-04')->delete()`

## ✨ Próximas Melhorias Sugeridas

- [ ] Criar Pages Filament para gerenciar manualmente
- [ ] Adicionar relatórios mensais em Excel/PDF
- [ ] Sistema de aprovação de horas extras
- [ ] Notificações quando saldo fica negativo
- [ ] Resgate de horas (conversor de horas para dias)
- [ ] Integração com calendário de feriados (não descontar feriados)

---

**Status**: ✅ Pronto para usar
**Próximo Passo**: `php artisan migrate`
