# 📊 Análise e Factories - Resumo Executivo

## ✅ O Que Foi Feito

### 1. **Análise Completa dos Modelos**

Foram analisados 13 modelos da aplicação com mapeamento de:
- ✅ Relações entre modelos (1:N, N:M, self-referências)
- ✅ Campos e tipos de dados
- ✅ Soft deletes e constraints
- ✅ Métodos e atributos computed
- ✅ Hooks e events
- ✅ Fluxos de negócio principais

**Estrutura Identificada:**
```
Country (1) ──── (N) State ──── (N) City ──── (N) Employee
                                              (1) Unit (hierarquia)
                                              (1) Designation
            ├─── Contracts
            ├─── AttendanceLogs
            ├─── HourBanks
            ├─── Vacations
            ├─── LeaveAndAbsences
            ├─── Absences
            └─── User (1-1)
```

### 2. **Factories Criadas/Melhoradas**

Foram criadas ou melhoradas **13 factories** com:

| Factory | Estado(s) | Geração de Dados |
|---------|-----------|------------------|
| CountryFactory | - | Países, códigos, phonecode |
| StateFactory | - | Estados com country_id automático |
| CityFactory | - | Cidades com state_id automático |
| DesignationFactory | `manager()` `operational()` | Cargos com salários realistas (€) |
| UnitFactory | `mainDirection()` `department()` `withParent()` | Hierarquia organizacional |
| EmployeeFactory | `manager()` `dismissed()` `recentlyHired()` `noVacationBalance()` | Funcionários com 13 campos |
| UserFactory | `unverified()` `mustChangePassword()` | Utilizadores com employee_id |
| ContractFactory | `ended()` `temporary()` | Contratos com datas realistas |
| AttendanceLogFactory | `withoutLunch()` `withExtraHours()` | Registos com cálculo de minutos |
| HourBankFactory | `positive()` `negative()` `forMonth()` | Saldos mensais |
| LeaveAndAbsenceFactory | `sickLeave()` `vacation()` `unpaid()` `personal()` | Licenças/faltas variadas |
| AbsenceFactory | `sickLeave()` `unpaid()` | Registos de deduções |
| VacationFactory | `approved()` `pending()` `rejected()` | Férias com status |

**Características:**
- ✅ Dados realistas (nomes, emails, NIFs, datas)
- ✅ Relações automáticas criadas
- ✅ States customizados para cenários comuns
- ✅ Valores de referência corretos
- ✅ Formatação PHP 8.2+ (property promotion, typed hints)

### 3. **TestDataSeeder Criado**

Seeder completo que popula a DB com cenário realista:

**Estrutura Organizacional:**
- 1 Diretoria Geral
- 3 Departamentos (RH, TI, Vendas)
- Hierarquia com gestores dedicados

**Dados Gerados (~18 funcionários):**
- 1 Diretor
- 3 Gestores de Departamento
- 1 Coordenador
- 5 Desenvolvedores Junior + 1 Senior
- 3 Vendedores

**Relacionados:**
- 20 registos de assiduidade por funcionário (10 selecionados)
- 3 com horas extras cada
- Banco de horas (2 meses por funcionário)
- Licenças médicas, férias, faltas
- Registos de absências com deduções
- Contratos + alguns históricos

### 4. **Documentação Completa**

Criado `FACTORIES_GUIDE.md` com:
- ✅ Exemplos de uso para cada factory
- ✅ States disponíveis com exemplos
- ✅ Como usar em testes Pest
- ✅ Cenários complexos
- ✅ Dicas e boas práticas
- ✅ Relações automáticas
- ✅ Credenciais de teste

---

## 🚀 Como Usar

### Populate a DB com dados de teste:

```bash
# Limpar e recriar com dados de teste
php artisan migrate:fresh --seeder=TestDataSeeder

# Ou apenas rodar o seeder (sem limpar)
php artisan db:seed --class=TestDataSeeder
```

### Usar em testes:

```php
use Tests\TestCase;

test('employee with contracts', function () {
    $employee = Employee::factory()
        ->has(Contract::factory()->count(2))
        ->create();

    expect($employee->contracts)->toHaveCount(2);
});

// Com states
test('manager with department', function () {
    $manager = Employee::factory()->manager()->create();
    $team = Employee::factory(5)->create([
        'unit_id' => $manager->unit_id
    ]);

    expect($team)->toHaveCount(5);
});
```

### Criar dados manualmente:

```php
// Via Tinker
php artisan tinker

$employee = Employee::factory()->create();
$contract = Contract::factory()->create(['employee_id' => $employee->id]);
AttendanceLog::factory(20)->create(['employee_id' => $employee->id]);
```

---

## 📊 Dados Gerados (TestDataSeeder)

```
✅ 1 País
✅ 1 Estado
✅ 1 Cidade
✅ 4 Unidades Organizacionais
✅ 5 Designações
✅ 18 Funcionários
✅ 6 Utilizadores
✅ ~22 Contratos (alguns antigos)
✅ ~200 Registos de Assiduidade
✅ ~16 Registos de Banco de Horas
✅ 15 Licenças/Absências
✅ 30-45 Registos de Ausências
✅ 6 Registos de Férias
```

**Credenciais de Teste:**
- Email: `admin@example.com`
- Password: `password`

---

## 🔍 Arquivos Modificados

```
database/factories/
├── CountryFactory.php ✨ Melhorado
├── StateFactory.php ✨ Melhorado
├── CityFactory.php ✨ Melhorado
├── DesignationFactory.php ✨ Melhorado + States
├── UnitFactory.php ✨ Melhorado + States
├── EmployeeFactory.php ✨ Melhorado + States
├── UserFactory.php ✨ Melhorado + employee_id
├── ContractFactory.php ✨ Melhorado + States
├── AttendanceLogFactory.php ✨ Novo + States
├── HourBankFactory.php ✨ Novo + States
├── LeaveAndAbsenceFactory.php ✨ Melhorado + States
├── AbsenceFactory.php ✨ Novo + States
└── VacationFactory.php ✨ Novo + States

database/seeders/
└── TestDataSeeder.php ✨ Novo (250+ linhas)

Documentação:
└── FACTORIES_GUIDE.md ✨ Novo (600+ linhas)
```

---

## ✨ Destaques

### Dados Realistas:
- ✅ Salários em euros com 2 casas decimais
- ✅ Datas de contrato realistas (-2 a -5 anos)
- ✅ NIFs, NSSs, e emailsValidados
- ✅ Nomes e apelidos reais (Faker)
- ✅ Estrutura organizacional com hierarquias

### Relações Automáticas:
- ✅ Employee cria City, Unit, Designation automaticamente
- ✅ User associa-se automaticamente a um Employee
- ✅ Contracts herda desig nation_id do Employee
- ✅ Absence cria LeaveAndAbsence se necessário

### States Úteis para Testes:
- ✅ `manager()` para gestores
- ✅ `dismissed()` para desligados
- ✅ `withExtraHours()` para cenários de horas extra
- ✅ `approved()` para aprovações
- ✅ `positive()` / `negative()` para saldos

### Melhorias Implementadas:
- ✅ Pint formatting aplicado automaticamente
- ✅ Sintaxe PHP 8.2+ em todo código
- ✅ Type hints e return types completos
- ✅ PHPDoc blocks descritivos
- ✅ Importações organizadas

---

## 📚 Próximos Passos Sugeridos

1. **Adicionar testes**:
   ```bash
   php artisan make:test EmployeeTest --pest
   ```

2. **Testar factories**:
   ```bash
   php artisan test tests/Unit/Models/
   ```

3. **Usar em controladores**:
   ```php
   $employees = Employee::factory(100)->create();
   ```

4. **Expandir seeders** com mais dados específicos conforme necessário

---

## 📖 Documentação Completa

Consulte `FACTORIES_GUIDE.md` para:
- Exemplos detalhados de cada factory
- Como combinar factories para cenários complexos
- Boas práticas de testes com factories
- Referências do Laravel e Pest

✅ **Tudo pronto para começar a testar!**
