# 🏭 Guia de Factories e Seeders

Este documento descreve como usar as factories da aplicação para testes e populate da base de dados.

## 📋 Factories Disponíveis

### 1. **CountryFactory**
Cria registos de países com nome, código e código de telefone.

```php
// Uso básico
Country::factory()->create();

// Com países específicos
Country::factory()->create([
    'name' => 'Portugal',
    'code' => 'PT',
    'phonecode' => 351
]);
```

---

### 2. **StateFactory**
Cria estados/províncias e associa a um país automaticamente.

```php
// Uso básico
State::factory()->create();

// Com país específico
State::factory()->create([
    'country_id' => $country->id
]);
```

---

### 3. **CityFactory**
Cria cidades e associa a um estado automaticamente.

```php
// Uso básico
City::factory()->create();

// Com estado específico
City::factory()->create([
    'state_id' => $state->id
]);
```

---

### 4. **DesignationFactory**
Cria designações/cargos com salários base realistas.

**States Disponíveis:**
- `manager()` - Designação de nível gerencial (nível 4-5, salário 3000-6000€)
- `operational()` - Designação de nível operacional (nível 1-2, salário 1000-2000€)

```php
// Uso básico
Designation::factory()->create();

// Gestores
Designation::factory()->manager()->create();

// Operacional
Designation::factory()->operational()->create();
```

---

### 5. **UnitFactory**
Cria unidades organizacionais com hierarquia.

**States Disponíveis:**
- `mainDirection()` - Diretoria principal
- `department()` - Departamento
- `withParent($unit)` - Cria com hierarquia

```php
// Uso básico
Unit::factory()->create();

// Diretoria
Unit::factory()->mainDirection()->create();

// Com departamento pai
$mainDir = Unit::factory()->mainDirection()->create();
Unit::factory()->department()->withParent($mainDir)->create();
```

---

### 6. **EmployeeFactory**
Cria funcionários com todas as validações.

**States Disponíveis:**
- `manager()` - Funcionário gestor
- `dismissed()` - Funcionário desligado
- `recentlyHired()` - Contratado há menos de 3 meses
- `noVacationBalance()` - Sem saldo de férias

```php
// Uso básico
Employee::factory()->create();

// Gestor
Employee::factory()->manager()->create();

// Desligado
Employee::factory()->dismissed()->create();

// Com relacionamentos específicos
Employee::factory()->create([
    'city_id' => $city->id,
    'unit_id' => $unit->id,
    'designation_id' => $designation->id
]);
```

---

### 7. **UserFactory**
Cria utilizadores com associação a Employee.

**States Disponíveis:**
- `unverified()` - Email não verificado
- `mustChangePassword()` - Deve alterar password no login

```php
// Uso básico
User::factory()->create();

// Com employee específico
User::factory()->create([
    'employee_id' => $employee->id
]);

// Precisa alterar password
User::factory()->mustChangePassword()->create();

// Senha padrão: "password"
```

---

### 8. **ContractFactory**
Cria contratos de trabalho.

**States Disponíveis:**
- `ended()` - Contrato finalizado
- `temporary()` - Contrato temporário

```php
// Uso básico
Contract::factory()->create();

// Contrato ativo
Contract::factory()->create([
    'employee_id' => $employee->id
]);

// Contrato temporário
Contract::factory()->temporary()->create();

// Contrato encerrado
Contract::factory()->ended()->create();
```

---

### 9. **AttendanceLogFactory**
Cria registos de assiduidade/presença.

**States Disponíveis:**
- `withoutLunch()` - Sem pausa para almoço
- `withExtraHours()` - Com horas extras (saída ~21h)

```php
// Uso básico
AttendanceLog::factory()->create();

// Normal (8h + almoço)
AttendanceLog::factory()->create([
    'employee_id' => $employee->id
]);

// Sem almoço
AttendanceLog::factory()->withoutLunch()->create();

// Com horas extras
AttendanceLog::factory()->withExtraHours()->create();

// Múltiplos registos
AttendanceLog::factory(20)->create([
    'employee_id' => $employee->id
]);
```

---

### 10. **HourBankFactory**
Cria registos do banco de horas (mensal).

**States Disponíveis:**
- `positive()` - Saldo positivo (10-100 minutos)
- `negative()` - Saldo negativo (débito, -100 a -10)
- `forMonth($monthYear)` - Para mês específico (formato: "2024-04")

```php
// Uso básico
HourBank::factory()->create();

// Saldo positivo
HourBank::factory()->positive()->create();

// Saldo negativo (débito)
HourBank::factory()->negative()->create();

// Mês específico
HourBank::factory()->forMonth('2024-04')->create([
    'employee_id' => $employee->id
]);

// Histórico mensal
HourBank::factory()->create(['month_year' => now()->format('Y-m')]);
HourBank::factory()->create(['month_year' => now()->subMonth()->format('Y-m')]);
```

---

### 11. **LeaveAndAbsenceFactory**
Cria licenças e faltas.

**States Disponíveis:**
- `sickLeave()` - Licença médica
- `vacation()` - Férias
- `unpaid()` - Falta sem remunerar
- `personal()` - Licença pessoal

```php
// Uso básico
LeaveAndAbsence::factory()->create();

// Licença médica
LeaveAndAbsence::factory()->sickLeave()->create();

// Férias
LeaveAndAbsence::factory()->vacation()->create();

// Falta
LeaveAndAbsence::factory()->unpaid()->create();

// Licença pessoal
LeaveAndAbsence::factory()->personal()->create();
```

---

### 12. **AbsenceFactory**
Cria registos de ausências/deduções.

**States Disponíveis:**
- `sickLeave()` - Doença
- `unpaid()` - Sem remunerar (8 horas)

```php
// Uso básico
Absence::factory()->create();

// Doença
Absence::factory()->sickLeave()->create();

// Sem remunerar
Absence::factory()->unpaid()->create();

// Com licença associada
Absence::factory()->create([
    'leave_and_absence_id' => $leave->id,
    'employee_id' => $leave->employee_id
]);
```

---

### 13. **VacationFactory**
Cria registos de férias.

**States Disponíveis:**
- `approved()` - Férias aprovadas
- `pending()` - Aguardando aprovação
- `rejected()` - Férias rejeitadas

```php
// Uso básico
Vacation::factory()->create();

// Aprovada
Vacation::factory()->approved()->create();

// Pendente
Vacation::factory()->pending()->create();

// Rejeitada
Vacation::factory()->rejected()->create();

// Com aprovador
Vacation::factory()->create([
    'approved_by' => $manager->user->id
]);
```

---

## 🚀 Seeders

### **TestDataSeeder**

Popula a base de dados com uma estrutura completa e realista de dados para testes.

#### Estrutura Criada:

```
📍 Dados Geográficos:
  - 1 País (Portugal)
  - 1 Estado (Lisboa)
  - 1 Cidade (Lisboa)

🏢 Estrutura Organizacional:
  - 1 Diretoria Geral
  - Recursos Humanos (com gestor)
  - Tecnologia da Informação (com gestor)
  - Vendas (com gestor)

👥 Funcionários (~18 total):
  - 1 Diretor Geral
  - 3 Gestores de Departamento
  - 1 Coordenador RH
  - 5 Desenvolvedores Junior
  - 1 Developer Senior
  - 3 Vendedores

💼 Dados Relacionados:
  - Designações adequadas por nível
  - Contratos para cada funcionário
  - Alguns contratos antigos/encerrados (20% chance)
  - Utilizadores para 5 funcionários
  - 20 registos de assiduidade por funcionário (10 selecionados)
  - 3 registos com horas extras cada
  - Banco de horas (mês atual + anterior)
  - Licenças, ausências e férias variadas
```

#### Como Usar:

```bash
# Rodar o seeder específico
php artisan db:seed --class=TestDataSeeder

# Ou desde o início (limpar e recriar)
php artisan migrate:fresh --seed

# Apenas com este seeder
php artisan migrate:fresh --seeder=TestDataSeeder
```

#### Credenciais de Teste:

```
Email: admin@example.com
Password: password
```

---

## 📊 Exemplos de Uso em Testes

### Criar um funcionário com todas as relações:

```php
test('employee with full relationships', function () {
    $employee = Employee::factory()
        ->has(Contract::factory()->count(2))
        ->has(AttendanceLog::factory()->count(20))
        ->has(HourBank::factory()->count(2))
        ->create();

    expect($employee->contracts)->toHaveCount(2);
    expect($employee->attendanceLogs)->toHaveCount(20);
    expect($employee->hourBanks)->toHaveCount(2);
});
```

### Testar com dados específicos:

```php
test('calculate extra hours', function () {
    $employee = Employee::factory()->create();
    
    $log = AttendanceLog::factory()
        ->withExtraHours()
        ->create([
            'employee_id' => $employee->id,
            'time_in' => now()->setTime(9, 0),
            'time_out' => now()->setTime(21, 0),
        ]);

    expect($log->total_minutes)->toBeGreaterThan(720); // Mais de 12h
});
```

### Criar cenários complexos:

```php
test('department hierarchy', function () {
    $mainDir = Unit::factory()->mainDirection()->create();
    $dept1 = Unit::factory()->department()->withParent($mainDir)->create();
    $dept2 = Unit::factory()->department()->withParent($mainDir)->create();
    
    $teamLead = Employee::factory()->manager()->create([
        'unit_id' => $dept1->id
    ]);
    
    $team = Employee::factory(5)->create([
        'unit_id' => $dept1->id
    ]);

    expect($mainDir->children)->toHaveCount(2);
    expect($dept1->employees)->toHaveCount(6); // teamLead + 5
});
```

---

## 🔗 Relações Automáticas

As factories respeitam as seguintes relações:

| Modelo | Relações Automáticas |
|--------|---------------------|
| Employee | City, Unit, Designation |
| User | Employee |
| Contract | Employee, Designation |
| AttendanceLog | Employee |
| HourBank | Employee |
| LeaveAndAbsence | Employee |
| Absence | Employee, LeaveAndAbsence |
| Vacation | Employee, User (aprovador) |
| Unit | Estado (parent), Designação (manager) |
| State | Country |
| City | State |

---

## 💡 Dicas

1. **Use states para cenários específicos:**
   ```php
   Employee::factory()->dismissed()->create();  // Cenário de desligamento
   Vacation::factory()->pending()->create();    // Pendente de aprovação
   ```

2. **Combine factories para estruturas complexas:**
   ```php
   $dept = Unit::factory()->department()->create();
   $manager = Employee::factory()->manager()->create(['unit_id' => $dept->id]);
   $dept->update(['manager_id' => $manager->id]);
   ```

3. **Use count para criar múltiplos:**
   ```php
   $employees = Employee::factory(10)->create();
   ```

4. **Para testes rápidos, use `make()` em vez de `create()`:**
   ```php
   $employee = Employee::factory()->make(); // Não salva na DB
   ```

5. **Utilize o TestDataSeeder para populate rápida:**
   ```bash
   php artisan migrate:fresh --seeder=TestDataSeeder
   ```

---

## 🗑️ Limpeza entre Testes

No `TestCase.php`, as migrações são automáticas com `RefreshDatabase`:

```php
use RefreshDatabase;

test('meu teste', function () {
    // DB está limpa e migrada
    $employee = Employee::factory()->create();
    // ...
});
```

---

## 📚 Referências

- [Laravel Factories Documentation](https://laravel.com/docs/11.x/eloquent-factories)
- [Pest Testing Documentation](https://pestphp.com/)
- Modelos: [app/Models](app/Models/)
