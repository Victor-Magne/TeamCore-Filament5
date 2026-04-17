# Integração Automática de Faltas via AttendanceLogObserver

## 📋 Visão Geral

O sistema de faltas foi integrado com sucesso através de um **Observer padrão** que monitora quando um `AttendanceLog` é criado ou atualizado. Quando é detectada uma falta (sem hora de saída), o sistema automaticamente desconta horas do banco de horas.

## 🔄 Como Funciona

### Fluxo Automático

```
Admin registra AttendanceLog
        ↓
Observer detecta falta (sem time_out)
        ↓
Verifica se há licença/férias para essa data
        ↓
Se não há → Chama DeductHourBankService
        ↓
Desconta 8h do banco de horas
        ↓
Cria registo em tabela Absence
        ↓
Atualiza HourBank com novo saldo
```

### Detectar uma Falta

Uma falta é detectada quando:
- ✅ O `time_out` é NULL (não registou saída)
- ✅ O `total_minutes` é NULL ou zero (sem horas)
- ✅ Não é fim de semana (sábado/domingo)
- ✅ Não há LeaveAndAbsence para essa data
- ✅ Não há Vacation aprovada para essa data

### O Que NÃO Desconta

- ❌ Se tem `time_out` registado (é um dia de trabalho normal)
- ❌ Se é sábado ou domingo
- ❌ Se há licença (sick_leave, marriage, bereavement, etc)
- ❌ Se há férias aprovadas
- ❌ Se já existe uma Absence para essa data (evita duplicados)

## 📁 Arquivos Envolvidos

### Novo Observer
**`app/Observers/AttendanceLogObserver.php`**
- Monitora eventos `created` e `updated` do AttendanceLog
- Detecta faltas automaticamente
- Valida licenças e férias antes de descontar
- Chama o `DeductHourBankService`

### Serviço Existente
**`app/Services/Hour/DeductHourBankService.php`** (já existente)
- Realiza o cálculo e desconto de horas
- Valida licenças justificadas
- Cria registos em `Absence`
- Atualiza `HourBank`

### Registro do Observer
**`app/Providers/AppServiceProvider.php`**
```php
AttendanceLog::observe(AttendanceLogObserver::class);
```

## 🧪 Testes de Integração

**`tests/Feature/Observers/AttendanceLogObserverTest.php`**

10 testes validam:
- ✅ Criação automática de Absence para faltas
- ✅ Deduação do HourBank
- ✅ Não desconta com licença justificada
- ✅ Não desconta fins de semana
- ✅ Processa múltiplas faltas
- ✅ Não cria duplicados
- ✅ Atualiza AttendanceLog com referência
- ✅ Inclui referência ao AttendanceLog na razão
- ✅ Funciona sem contrato
- ✅ Processa atualizações

**Resultado**: 10/10 PASSAR ✅

## 💡 Exemplos de Uso

### Cenário 1: Admin Registra Falta Simples

```php
// Admin cria um AttendanceLog sem saída
AttendanceLog::create([
    'employee_id' => 1,
    'time_in' => Carbon::create(2026, 4, 17, 9, 0),
    'lunch_break_start' => null,
    'lunch_break_end' => null,
    'time_out' => null, // ← Sem saída = falta
    'notes' => 'Falta ao trabalho',
]);

// ➜ Automaticamente:
// 1. Observer detecta falta
// 2. Cria Absence com 480 min (8h)
// 3. Atualiza HourBank: balance -= 480
// 4. Registra razão: "Falta automática detectada via ponto (AttendanceLog #123)"
```

### Cenário 2: Funcionário em Licença Não Desconta

```php
// Admin criou LeaveAndAbsence para essa data
LeaveAndAbsence::create([
    'employee_id' => 1,
    'type' => 'sick_leave',
    'start_date' => Carbon::create(2026, 4, 17),
    'end_date' => Carbon::create(2026, 4, 17),
    'status' => 'approved',
]);

// Depois registou AttendanceLog
AttendanceLog::create([
    'employee_id' => 1,
    'time_in' => Carbon::create(2026, 4, 17, 9, 0),
    'time_out' => null,
]);

// ➜ Observer:
// 1. Detecta falta
// 2. Verifica LeaveAndAbsence
// 3. Encontra licença → NÃO desconta
// 4. Retorna sem criar Absence
```

### Cenário 3: Múltiplas Faltas

```php
// Criar vários AttendanceLogs sem saída
for ($i = 13; $i <= 15; $i++) {
    AttendanceLog::create([
        'employee_id' => 1,
        'time_in' => Carbon::create(2026, 4, $i, 9, 0),
        'time_out' => null,
    ]);
}

// ➜ Resultado:
// 3 Absence registadas (seg, ter, qua)
// HourBank: balance = -1440 (-3 * 480)
// Sábado e domingo (18-19) não desconta
```

## ⚙️ Configuração

### Arquivo: `config/hour_bank.php`

```php
return [
    // Validar licenças antes de descontar
    'validate_leaves_before_deduction' => true,

    // Tipos de licença que NÃO geram desconto
    'justified_leave_types' => [
        'sick_leave',
        'parental',
        'marriage',
        'bereavement',
        'justified_absence',
    ],
];
```

### Variáveis de Ambiente

```env
# .env
HOUR_BANK_VALIDATE_LEAVES=true
```

## 📊 Dados Associados

### Tabela: `absences`
```sql
id
employee_id
absence_date        ← Data da falta
hours_deducted      ← 480 minutos (8h) por padrão
deduction_type      ← 'unjustified_absence'
reason              ← "Falta automática detectada via ponto (AttendanceLog #N)"
created_at
updated_at
```

### Tabela: `hour_banks`
```sql
id
employee_id
month_year          ← '2026-04'
balance             ← Saldo atual (negativo = deve)
extra_hours_added   ← Horas extras adicionadas
extra_hours_used    ← Horas deductadas
previous_balance    ← Saldo do mês anterior
```

## 🔗 Relacionamentos

```
AttendanceLog
    ↓ Observer detecta falta
AttendanceLogObserver
    ↓ Chama
DeductHourBankService
    ↓ Cria/Atualiza
Absence
HourBank
```

## 🚨 Tratamento de Erros

Se houver erro durante o desconto:

```php
try {
    $service->handle(...);
} catch (\Exception $e) {
    \Log::error('Erro ao descontar horas de falta', [
        'employee_id' => $attendanceLog->employee_id,
        'attendance_log_id' => $attendanceLog->id,
        'error' => $e->getMessage(),
    ]);
}
```

**Importante**: O erro é registado mas NÃO bloqueia a criação do AttendanceLog.

## ✨ Vantagens da Implementação

| Aspecto | Antes | Depois |
|---------|-------|--------|
| Deduções | Manual | Automáticas ✅ |
| Erros | Humanos frequentes | Zero (automático) ✅ |
| Licenças | Manual verificar | Automático ✅ |
| Auditoria | Registro manual | Completo com Absence ✅ |
| Integração | Inexistente | Transparente ✅ |

## 📝 Notas Importantes

1. **Quando a falta é detectada?**
   - Quando um `AttendanceLog` é criado COM `time_in` MAS SEM `time_out`

2. **Como prevenir desconto?**
   - Criar `LeaveAndAbsence` antes de `AttendanceLog`
   - Usar tipos de licença configurados

3. **Pode descontar mesmo com licença?**
   - Sim, se forçar via `forceDeduction=true` no serviço
   - Mas o Observer não faz isso automaticamente

4. **E os fins de semana?**
   - Automaticamente ignorados (não desconta)

5. **E feriados?**
   - Atualmente não há validação de feriados
   - Implementável no futuro em `shouldSkipDeduction`

## 🚀 Próximos Passos Opcionais

1. [ ] Adicionar validação de feriados
2. [ ] Dashboard mostrando faltas vs deduções
3. [ ] Email ao funcionário quando falta é registada
4. [ ] Command para processar faltas em massa
5. [ ] Relatório de faltas por departamento

## 📚 Referências

- Testes: [DeductHourBankServiceTest.php](../../tests/Feature/Services/DeductHourBankServiceTest.php)
- Testes do Observer: [AttendanceLogObserverTest.php](../../tests/Feature/Observers/AttendanceLogObserverTest.php)
- Auditoria: [ABSENCE_SYSTEM_AUDIT.md](ABSENCE_SYSTEM_AUDIT.md)
