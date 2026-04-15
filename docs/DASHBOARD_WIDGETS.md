# Dashboard Widgets - TeamCore

## Visão Geral
9 widgets criados em 3 bundles temáticos para o Dashboard do TeamCore, seguindo rigorosamente os padrões de performance do Laravel Boost e segurança com Filament Shield.

---

## Bundle Financeiro (3 widgets)

### 1. **TotalPayrollStat** (`TotalPayrollStat.php`)
- **Tipo**: StatsOverviewWidget (3 stat cards)
- **Dados**:
  - Folha de Pagamento Total (€): Soma dos salários de contratos ativos
  - Salário Médio (€): Média salarial
  - Salário Máximo (€): Maior salário
- **Query**: Otimizada com status='active' e validação de end_date
- **Cores**: Success (verde), Info (azul), Warning (amarelo)
- **Segurança**: `can('View:TotalPayrollStat')`

### 2. **ContractExpirationsStat** (`ContractExpirationsStat.php`)
- **Tipo**: StatsOverviewWidget (3 stat cards)
- **Dados**:
  - Vencimento nos Próximos 30 Dias: Contagem com cor dinâmica (danger se > 0)
  - Vencimentos Este Mês: Total do mês
  - Críticos (< 7 dias): Necessita ação imediata
- **Query**: Otimizada com whereBetween e whereMonth
- **Cor Dinâmica**: Danger se contratos críticos > 0
- **Segurança**: `can('View:ContractExpirationsStat')`

### 3. **ContractTypeChart** (`ContractTypeChart.php`)
- **Tipo**: ChartWidget Doughnut (gráfico de rosca)
- **Dados**: Distribuição percentual dos 5 tipos de contrato (Permanente, A Termo Certo, A Termo Incerto, Prestação de Serviços, Estágio)
- **Query**: selectRaw com groupBy, sem N+1
- **Cores**: 5 cores harmónicas (verde, azul, âmbar, vermelho, roxo)
- **Polling**: 30s
- **Segurança**: `can('View:ContractTypeChart')`

---

## Bundle Organizacional (2 widgets)

### 4. **UnitDensityChart** (`UnitDensityChart.php`)
- **Tipo**: ChartWidget Bar
- **Dados**: Top 10 Unidades por nº de funcionários
- **Query**: withCount('employees') - sem carregamento do modelo completo (performance!)
- **Limitação**: Apenas unidades com employees_count > 0
- **Cores**: Azul sólido (#3b82f6)
- **Polling**: 30s
- **Segurança**: `can('View:UnitDensityChart')`

### 5. **SalaryByLevelStat** (`SalaryByLevelStat.php`)
- **Tipo**: StatsOverviewWidget (3 stat cards)
- **Dados**:
  - Salário Médio Nível Sénior (level >= 3)
  - Salário Médio Nível Médio (level == 2)
  - Salário Médio Nível Júnior (level <= 1)
- **Query**: Join otimizado entre Designation e Contract com agregação
- **Cores**: Success, Info, Warning (por hierarquia)
- **Segurança**: `can('View:SalaryByLevelStat')`

### 6. **EmployeesByUnitChart** (`EmployeesByUnitChart.php`)
- **Tipo**: ChartWidget Bar
- **Dados**: Distribuição de funcionários por todas as unidades com tipo
- **Query**: withCount('employees') + seleção restrita de colunas
- **Cores Dinâmicas**:
  - Direction: Verde (#059669)
  - Department: Azul (#3b82f6)
  - Section: Roxo (#8b5cf6)
- **Labels**: Incluem tipo de unidade (Direção, Departamento, Secção)
- **Polling**: 30s
- **Segurança**: `can('View:EmployeesByUnitChart')`

### 7. **EmployeeStatsWidget** (`EmployeeStatsWidget.php`)
- **Tipo**: StatsOverviewWidget (3 stat cards)
- **Dados**:
  - Funcionários Ativos: Sem date_dismissed ou > hoje
  - Total de Funcionários: Incluindo afastados
  - Despedidos: Saíram da organização
- **Query**: Validação temporal com whereNull/orWhere para robustez
- **Threshold Dinâmico**: Considera data_dismissed nullable
- **Segurança**: `can('View:EmployeeStatsWidget')`

---

## Bundle Operacional (2 widgets)

### 8. **DailyAbsenceStat** (`DailyAbsenceStat.php`)
- **Tipo**: StatsOverviewWidget (3 stat cards)
- **Dados**:
  - Taxa de Absentismo Hoje (%): Dinâmica com thresholds
    - Danger: > 20%
    - Warning: > 10%
    - Success: <= 10%
  - Funcionários Ausentes: Contagem absoluta
  - Faltas Injustificadas Hoje: Conta unjustified
- **Query**: Complexa com whereBetween + orWhere para validação de datas
- **Query Otimizada**: distinct('employee_id') para evitar duplicatas
- **Segurança**: `can('View:DailyAbsenceStat')`

### 9. **AbsenceReasonChart** (`AbsenceReasonChart.php`)
- **Tipo**: ChartWidget Pie (gráfico de pizza)
- **Dados**: Distribuição de faltas por motivo (últimos 30 dias)
- **Motivos**: Baixa Médica, Licença Parental, Casamento, Falecimento, Falta Justificada, Falta Injustificada
- **Query**: selectRaw + groupBy sem N+1
- **Cores Únicas**: 6 cores distintas por tipo
- **Período**: Últimos 30 dias (dinâmico)
- **Polling**: 30s
- **Segurança**: `can('View:AbsenceReasonChart')`

---

## Padrões Implementados

### ✅ Performance (Laravel Boost)
- `selectRaw()` com agregações (SUM, AVG, COUNT)
- `withCount()` sem carregamento do modelo completo
- Seleção restrita de colunas: `get(['id', 'name', 'employees_count'])`
- Sem N+1 queries em nenhum widget
- `groupBy()` eficiente em queries de contagem

### ✅ Segurança (Filament Shield)
- Todos os widgets implementam `public static function canView(): bool`
- Pattern: `Auth::user()?->can('View:ClassName') ?? false`
- Proteção contra access não autorizado
- Padrão de permissão: `View:WidgetName`

### ✅ UI/UX
- Cores dinâmicas baseadas em thresholds (DailyAbsenceStat, ContractExpirationsStat)
- Descrições claras com ícones Heroicons
- Labels localizados em Português
- Tipo de unidade mostrado nos labels (EmployeesByUnitChart)
- Polling 30s em gráficos para dados quase-reais

### ✅ Robustez
- Validações temporais com `whereNull()` / `orWhere()`
- Tratamento de NULL em agregações (AVG, MAX)
- Contagem com `distinct()` para evitar duplicatas
- Limites (LIMIT 10) em queries potencialmente largas

---

## Registar no Dashboard

Para adicionar os widgets ao Dashboard, incluir em `app/Filament/Pages/Dashboard.php`:

```php
public function getWidgets(): array
{
    return [
        // Bundle Financeiro
        TotalPayrollStat::class,
        ContractExpirationsStat::class,
        ContractTypeChart::class,

        // Bundle Organizacional
        UnitDensityChart::class,
        SalaryByLevelStat::class,
        EmployeesByUnitChart::class,
        EmployeeStatsWidget::class,

        // Bundle Operacional
        DailyAbsenceStat::class,
        AbsenceReasonChart::class,
    ];
}
```

---

## Requisitos de Permissões Shield

Garantir que as seguintes permissões estão definidas no Filament Shield:
- `View:TotalPayrollStat`
- `View:ContractExpirationsStat`
- `View:ContractTypeChart`
- `View:UnitDensityChart`
- `View:SalaryByLevelStat`
- `View:EmployeesByUnitChart`
- `View:EmployeeStatsWidget`
- `View:DailyAbsenceStat`
- `View:AbsenceReasonChart`

---

## Notas Técnicas

1. **Queries Kompleksas (DailyAbsenceStat)**:
   - Valida intervalo de datas com 3 condições (start_date, end_date, dentro de intervalo)
   - Usa `distinct('employee_id')` para evitar contar funcionário 2x em múltiplos dias

2. **Cores em EmployeesByUnitChart**:
   - Match dinâmico no map() garante cores consistentes por tipo

3. **Thresholds Dinâmicos**:
   - DailyAbsenceStat: 3 níveis (20%, 10%, default)
   - ContractExpirationsStat: apenas 1 threshold (> 0)

4. **Período em AbsenceReasonChart**:
   - Dinâmico: sempre últimos 30 dias a partir de hoje

5. **Tratamento de NULL em Agregações**:
   - `?? 0` em AVG/MAX para evitar erros se nenhum resultado
