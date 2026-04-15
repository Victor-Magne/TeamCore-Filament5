# ⚡ Guia Rápido - Factories e Seeders

## 🎯 Primeiros Passos

### 1. Populate a DB com dados realistas

```bash
# Limpar tudo e popular com dados de teste
php artisan migrate:fresh --seeder=TestDataSeeder
```

Isto cria automaticamente:
- ✅ 1 estrutura organizacional completa (4 unidades com hierarquia)
- ✅ 18 funcionários com diferentes cargos
- ✅ 6 utilizadores para login
- ✅ 200+ registos de assiduidade
- ✅ Contratos, férias, licenças, banco de horas

**Credenciais:**
- Email: `admin@example.com`
- Senha: `password`

---

## 🏭 Usar em Testes (Pest)

### Criar um funcionário simples

```php
test('employee creation', function () {
    $employee = Employee::factory()->create();
    
    expect($employee)->toHaveProperty('first_name')
        ->and($employee->city)->not->toBeNull()
        ->and($employee->designation->base_salary)->toBeGreaterThan(1000);
});
```

### Com relações

```php
test('employee with contracts', function () {
    $employee = Employee::factory()
        ->has(Contract::factory(3)->ended(), 'contracts')
        ->create();

    expect($employee->contracts)->toHaveCount(3);
    expect($employee->contracts->every(fn($c) => $c->status === 'ended'))->toBeTrue();
});
```

### Com states (cenários específicos)

```php
// Gestor desligado há 2 meses
$dismissed = Employee::factory()->manager()->dismissed()->create();

// Horas extras
$log = AttendanceLog::factory()->withExtraHours()->create();

// Férias aprovadas
$vacation = Vacation::factory()->approved()->create();

// Saldo negativo
$hourBank = HourBank::factory()->negative()->create();
```

---

## 📊 Estrutura de States

### Employee
```php
->manager()              // Gestor
->dismissed()            // Desligado
->recentlyHired()        // Contratado há <3 meses
->noVacationBalance()    // Sem férias
```

### Designation
```php
->manager()              // Nível 4-5, salário 3k-6k€
->operational()          // Nível 1-2, salário 1k-2k€
```

### Unit
```php
->mainDirection()        // Diretoria
->department()           // Departamento
->withParent($unit)      // Com hierarquia
```

### Contract
```php
->ended()                // Contrato terminado
->temporary()            // Temporário com data fim
```

### AttendanceLog
```php
->withoutLunch()         // Sem pausa
->withExtraHours()       // Saída ~21h
```

### HourBank
```php
->positive()             // Saldo 10-100 min
->negative()             // Débito -100 a -10 min
->forMonth('2024-04')    // Mês específico
```

### LeaveAndAbsence
```php
->sickLeave()            // Licença médica
->vacation()             // Férias
->unpaid()               // Falta
->personal()             // Assunto pessoal
```

### Vacation
```php
->approved()             // Aprovada
->pending()              // Pendente
->rejected()             // Rejeitada
```

---

## 💡 Exemplos Práticos

### Cenário 1: Departamento com equipa

```php
$dept = Unit::factory()->department()->create();
$manager = Employee::factory()->manager()->create(['unit_id' => $dept->id]);
$team = Employee::factory(5)->create(['unit_id' => $dept->id]);

// $dept agora tem 1 gestor + 5 funcionários
```

### Cenário 2: Gestão de férias

```php
$employee = Employee::factory()->create(['vacation_balance' => 21]);

$vacation = Vacation::factory()->pending()->create([
    'employee_id' => $employee->id,
    'days_taken' => 5,
]);

$manager = $employee->unit->manager();
$vacation->update(['approved_by' => $manager->user->id]);
$vacation->update(['status' => 'approved']);
```

### Cenário 3: Cálculo de horas

```php
$employee = Employee::factory()->create();

// Semana de trabalho normal
AttendanceLog::factory(5)->create(['employee_id' => $employee->id]);

// Semana com extras
AttendanceLog::factory(3)->withExtraHours()->create(['employee_id' => $employee->id]);

// Semana com falta (licença médica)
LeaveAndAbsence::factory()->sickLeave()->create(['employee_id' => $employee->id]);
```

### Cenário 4: Histórico de contratos

```php
$employee = Employee::factory()->create();

// Contrato antigo (encerrado)
Contract::factory()->ended()->create([
    'employee_id' => $employee->id,
    'end_date' => now()->subYears(2),
]);

// Contrato promotivo tempor&aacute;rio
Contract::factory()->temporary()->create([
    'employee_id' => $employee->id,
    'end_date' => now()->addMonths(6),
]);

// Contrato actual
Contract::factory()->create([
    'employee_id' => $employee->id,
    'status' => 'active',
]);
```

---

## 🔗 Relações Automáticas

Ao criar um modelo, as relações são criadas automaticamente:

```php
// Isto cria automaticamente:
// - City (se não fornecido)
// - Unit (se não fornecido)
// - Designation (se não fornecido)
// - User (1-1 com employee)

$employee = Employee::factory()->create();

echo $employee->city->name;           // ✅ Existe
echo $employee->unit->name;           // ✅ Existe
echo $employee->designation->name;    // ✅ Existe
echo $employee->user->email;          // ✅ Pode não existir, User é opcional
```

---

## 🚀 Atalhos Úteis

### Criar múltiplos rapidamente

```php
$employees = Employee::factory(100)->create();
$logs = AttendanceLog::factory(1000)->create();
```

### Sem salvar na DB (para testes unitários)

```php
$employee = Employee::factory()->make();  // Não salva!
expect($employee->full_name)->toBe('Jose Silva');
```

### Com relacionamentos aninhados

```php
$unit = Unit::factory()
    ->has(Employee::factory(10), 'employees')
    ->has(Employee::factory(1)->manager(), 'employees')
    ->create();
```

---

## 🔍 Verificar Dados Criados

```bash
# Ver quantos registos foram criados
php artisan tinker <<EOF
echo Employee::count() . " funcionários\n";
echo Contract::count() . " contratos\n";
echo AttendanceLog::count() . " registos de presença\n";
echo User::count() . " utilizadores\n";
EOF
```

---

## 📚 Documentação Completa

Para detalhes avançados e exemplos completos, consulte:
- 📖 `FACTORIES_GUIDE.md` - Guia detalhado de cada factory
- 📊 `FACTORIES_SUMMARY.md` - Resumo técnico

---

## ⚠️ Troubleshooting

### "Factory already exists"
```bash
# As factories já existem e foram melhoradas
# Use como estão!
```

### Erro de foreign key
```php
// Sempre forneça os IDs de relações se criar manualmente
$employee = Employee::factory()->create([
    'city_id' => $city->id,
    'unit_id' => $unit->id,
    'designation_id' => $designation->id,
]);
```

### Preciso de dados em mês específico
```php
// Use o state forMonth
$hourBank = HourBank::factory()
    ->forMonth('2024-04')
    ->positive()
    ->create();
```

---

## ✅ Quick Checklist

- [ ] Rodei `php artisan migrate:fresh --seeder=TestDataSeeder`
- [ ] Posso fazer login com `admin@example.com` / `password`
- [ ] Tenho 13 factories para usar
- [ ] Tenho 1 seeder para populate completo
- [ ] Li os exemplos acima
- [ ] Consultei `FACTORIES_GUIDE.md` para casos especiais

✨ **Pronto para começar!**
