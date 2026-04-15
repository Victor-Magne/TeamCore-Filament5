# 📊 Filament Resources - Banco de Horas e Ausências

## Recursos Criados para Administração

### 1. **HourBank Resource** 🏦
Gerenciamento e visualização do banco de horas dos funcionários.

**Localização**: `app/Filament/Resources/HourBanks/`

**Funcionalidades**:
- ✅ Listar todos os bancos de horas por funcionário/mês
- ✅ Visualizar saldo total (em horas e minutos)
- ✅ Ver horas extras adicionadas e descontadas
- ✅ Consultar saldo anterior
- ❌ Criação manual (gerado automaticamente pelo sistema)
- ❌ Edição ou exclusão (é um registo de auditoria)

**Colunas da Tabela**:
| Coluna | Descrição | Visível |
|--------|-----------|---------|
| Funcionário | Nome do funcionário | Sim |
| Mês/Ano | Período em formato YYYY-MM | Sim |
| **Saldo Total** | Saldo em horas/minutos (crédito ou débito) | Sim |
| Horas Extras Adicionadas | Horas extras acumuladas neste mês | Toggleável |
| Horas Descontadas | Horas descontadas por faltas | Toggleável |
| Saldo Anterior | Saldo do mês anterior | Toggleável |
| Data de Criação | Quando foi registado | Toggleável |

**Cores de Saldo**:
- 🟢 **Verde**: Saldo positivo (funcionário tem crédito)
- 🔴 **Vermelho**: Saldo negativo (funcionário deve horas)

---

### 2. **Absence Resource** ⚠️
Visualização do histórico de ausências e descontos de horas.

**Localização**: `app/Filament/Resources/Absences/`

**Funcionalidades**:
- ✅ Listar todas as ausências/faltas
- ✅ Ver motivo da ausência
- ✅ Visualizar horas descontadas
- ✅ Consultar datas e tipos de dedução
- ❌ Criação manual (gerado pelo `DeductHourBankService`)
- ❌ Edição ou exclusão (é um registo de auditoria)

**Colunas da Tabela**:
| Coluna | Descrição | Visível |
|--------|-----------|---------|
| Funcionário | Nome do funcionário | Sim |
| Data da Ausência | Data do evento | Sim |
| **Tipo de Dedução** | Badge com tipo (Falta Injustificada, Parcial, etc) | Sim |
| Horas Descontadas | Horas em formato legível (4h 30m) | Sim |
| Motivo | Razão da ausência | Toggleável |
| Data de Criação | Quando foi registado | Toggleável |

**Tipos de Dedução**:
- 🔴 **Falta Injustificada** (danger) - Desconta sempre
- 🟡 **Falta Parcial** (warning) - Desconta parcialmente
- ⚪ **Outra** (gray) - Outros tipos

---

## 🔍 Como Acessar

### Via Menu Filament

No sidebar do Filament, procure:
- **Banco de Horas** - ícone 🕐 (Clock)
- **Ausências/Faltas** - ícone ✖️ (X Mark)

### URLs Diretos

```
/admin/hour-banks
/admin/absences
```

---

## 📋 Visualização Detalhada

### Página do Banco de Horas

Ao clicar em um registo, vê:

**Seção: Informações do Banco de Horas**
- Funcionário (desabilitado)
- Mês/Ano (desabilitado)

**Seção: Saldos**
- Saldo Total (minutos)
- Horas Extras Adicionadas (minutos)
- Horas Descontadas (minutos)
- Saldo Anterior (minutos)

*Todos os campos são desabilitados para impedir alterações*

---

### Página de Ausência

Ao clicar em um registo, vê:

**Seção: Informações da Ausência**
- Funcionário (desabilitado)
- Data da Ausência (desabilitado)

**Seção: Dedução de Horas**
- Tipo de Dedução (desabilitado)
- Horas Descontadas em minutos (desabilitado)

**Seção: Observações**
- Motivo da Ausência (desabilitado)

*Todos os campos são desabilitados para impedir alterações*

---

## 🔐 Segurança & Auditorias

✅ **Sem Criação Manual**: Nenhum admin pode criar registos manualmente
✅ **Sem Edição**: Todos os campos estão desabilitados
✅ **Sem Exclusão**: Ações de delete foram removidas
✅ **Rastreável**: Cada registo tem timestamp de criação
✅ **Protegido**: Gerados automaticamente pelos Services

---

## 📊 Fluxo de Dados

```
AttendanceLog criado
    ↓
CalculateExtraHoursService
    ↓
HourBank criado/atualizado
    ↓
✅ Visível em /admin/hour-banks

---

Falta registada
    ↓
DeductHourBankService
    ↓
Absence criado + HourBank atualizado
    ↓
✅ Visível em /admin/absences
```

---

## 🎯 Casos de Uso Típicos

### Para Administração RH

**Verificar saldo de um funcionário**:
1. Abrir `/admin/hour-banks`
2. Procurar pelo nome
3. Ver saldo total (verde = crédito, vermelho = débito)

**Auditar descontos de um funcionário**:
1. Abrir `/admin/absences`
2. Filtrar por funcionário
3. Ver histórico completo com motivos

**Analisar padrão de ausências**:
1. Abrir `/admin/absences`
2. Ordenar por data
3. Ver tipos de dedução por período

---

## 🔄 Integração com Sistema

Os resources são **somente leitura** (read-only). Toda ação de modificação vem dos Services:

- `CalculateExtraHoursService` → Cria HourBank
- `DeductHourBankService` → Cria Absence e atualiza HourBank
- `AttendanceLog` hook → Dispara cálculos

---

## ⚙️ Parâmetros de Configuração

Ambos os resources herdam configurações de `config/hour_bank.php`:

```php
'validate_leaves_before_deduction' => true,  // Validação ativa?
'justified_leave_types' => [...],             // Tipos que não descontam
'unjustified_leave_types' => [...],           // Tipos que descontam
'daily_work_hours' => 480,                    // Minutos por dia
```

---

## 📝 Notas

- Modificações no HourBank e Absence não devem ser feitas manualmente através da UI
- Use os Services (`CalculateExtraHoursService`, `DeductHourBankService`) para modificações
- Os registos são de auditoria permanente
- Filtros e buscas funcionam normalmente (por nome, data, tipo, etc)

---

**Status**: ✅ Pronto para Produção
**Permissões**: Recomendado apenas para Administradores/RH
**Backup**: Recomendado fazer backup regular dos registos
