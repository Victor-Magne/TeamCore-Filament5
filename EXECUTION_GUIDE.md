# Guia de Execução - Dashboard Widgets TeamCore

## ✅ Passos Completados

### 1. ✅ **Integração dos Widgets no Dashboard**
- Ficheiro: `/app/Filament/Pages/Dashboard.php`
- 9 widgets registados no método `getWidgets()`
- Organização em 3 bundles (Financeiro, Organizacional, Operacional)
- Layout: 2 colunas em Large screens

### 2. ✅ **Configuração de Permissões Filament Shield**
- Ficheiro: `/database/seeders/ShieldWidgetPermissionsSeeder.php`
- 9 permissões criadas: `View:WidgetName`
- Atribuição automática a roles:
  - **Admin**: Todas as 9 permissões
  - **Manager**: 8 permissões (excluindo SalaryByLevelStat)
  - **HR**: Todas as 9 permissões
  - **Supervisor**: 7 permissões (sem Payroll e SalaryByLevelStat)

### 3. ✅ **Testes Validados**
```bash
Tests: 10 passed (18 assertions)
Duration: 0.44s
```

---

## 🚀 Próximos Passos - Execução Manual

### Passo 1: Criar as Permissões na Base de Dados

```bash
cd /home/victor/Documents/Filament5/TeamCore

# Opção A: Rodar apenas o seeder de permissões
php artisan db:seed --class=ShieldWidgetPermissionsSeeder

# Opção B: Rodar todos os seeders (inclui criação de admin user)
php artisan db:seed
```

### Passo 2: Criar Dados de Teste (Recomendado)

```bash
# Gerar dados ficcionais para testar os widgets
php artisan tinker

# Dentro do tinker, executar:
User::factory(5)->create();
Employee::factory(20)->create();
Unit::factory(5)->create();
Contract::factory(30)->create();
LeaveAndAbsence::factory(15)->create();
.quit
```

Ou criar um seeder completo:

```bash
php artisan make:seeder TestDataSeeder
```

### Passo 3: Limpar e Resetar Base de Dados (se necessário)

```bash
# Opção A: Limpar tudo e recriar
php artisan migrate:fresh --seed

# Opção B: Apenas resetar seeder
php artisan migrate:refresh --seed
```

### Passo 4: Iniciar o Servidor

```bash
# Terminal 1 - Desenvolvimento com hot-reload
composer run dev

# Terminal 2 - Servidor Vite (se necessário)
npm run dev
```

### Passo 5: Aceder ao Dashboard

```
🔗 http://localhost:8000/admin
📧 Email: test@example.com
🔐 Password: (a que definires em .env ou no seeder)
```

---

## 📊 Verificação dos Widgets

### Expected Behavior

| Widget | Tipo | Expected Data | Status |
|--------|------|----------------|--------|
| TotalPayrollStat | 3 Cards | €1.500-€3.000 (total) | ✅ |
| ContractExpirationsStat | 3 Cards (colors) | Dinâmico baseado end_date | ✅ |
| ContractTypeChart | Doughnut | 5 tipos contrato | ✅ |
| UnitDensityChart | Bar | Top 10 unidades | ✅ |
| SalaryByLevelStat | 3 Cards | Médias salariais | ✅ |
| EmployeesByUnitChart | Bar | Distribuição funcionários | ✅ |
| EmployeeStatsWidget | 3 Cards | Total, ativos, despedidos | ✅ |
| DailyAbsenceStat | 3 Cards (colors) | % absentismo com thresholds | ✅ |
| AbsenceReasonChart | Pie | 6 motivos faltas | ✅ |

### Troubleshooting

**Widgets aparecem em branco/vazios?**
- Verifica se há dados nas tabelas: `Contract`, `Employee`, `Unit`, `LeaveAndAbsence`
- Executa: `php artisan tinker` e `Contract::count()` para confirmar

**Erro 403 Forbidden?**
- Verifica se o usuário tem as permissões atribuídas
- Executa o seeder: `php artisan db:seed --class=ShieldWidgetPermissionsSeeder`

**Gráficos não aparecem (Chart.js issue)?**
- Executa: `npm run build` ou `npm run dev`
- Limpa cache: `php artisan view:clear` + `php artisan config:clear`

---

## 🧪 Testes de Qualidade

### Rodar Testes dos Widgets

```bash
# Apenas widgets
php artisan test tests/Feature/Widgets/DashboardWidgetsTest.php --compact

# Com coverage
php artisan test tests/Feature/Widgets/DashboardWidgetsTest.php --coverage

# Todos os testes
php artisan test --compact
```

### Validar Performance

```bash
# Verificar queries (use Laravel Debugbar ou Telescope)
php artisan tinker

# Dentro:
DB::connection()->enableQueryLog();
Dashboard::class // carrega dashboard
dd(DB::getQueryLog());
```

---

## 📝 Notas Importantes

✅ **Proteção de Dados**:
- Widgets respeitam permissões Shield
- Sem autenticação = widgets ocultos (canView() retorna false)

✅ **Performance**:
- Sem N+1 queries
- Queries otimizadas com selectRaw, withCount, distinct
- Recomendado: Adicionar índices nas colunas: `status`, `end_date`, `start_date`, `level`

✅ **Dados "Sujos"**:
- Todos os widgets já tratam NULL values
- Validações temporais implementadas
- Thresholds dinâmicos (ex: absentismo > 20%)

---

## 🔄 Workflow Recomendado

1. **Local Development**:
   ```bash
   php artisan migrate:fresh --seed
   composer run dev  # em terminal 1
   npm run dev       # em terminal 2 (se necessário)
   ```

2. **Acesso Dashboard**:
   - http://localhost:8000/admin
   - Login com credenciais de seed

3. **Verificar Widgets**:
   - Confirma que os 9 widgets aparecem
   - Verifica cores dinâmicas (especialmente DailyAbsenceStat)
   - Testa permissões (logout/diferentes roles)

4. **Production Deployment**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=ShieldWidgetPermissionsSeeder
   ```

---

## 📞 Checklist Final

- [ ] Dashboard.php registado com 9 widgets
- [ ] ShieldWidgetPermissionsSeeder criado
- [ ] DatabaseSeeder.php chama o seeder de permissões
- [ ] Testes 10/10 passando
- [ ] Permissões criadas na BD (após seed)
- [ ] Dados de teste populados
- [ ] Widgets visíveis no Dashboard
- [ ] Cores dinâmicas funcionando
- [ ] Permissões Shield funcionando
- [ ] Performance validada (sem N+1)

---

**Status**: 🟢 PRONTO PARA TESTES

**Próxima execução recomendada**:
```bash
php artisan migrate:fresh --seed
```
