# Relatório de Verificação: Sistema de Faltas (Absences)

## ⚠️ PROBLEMAS ENCONTRADOS

### 1. **Sistema Não Está Integrado** ❌
**Situação**: O serviço `DeductHourBankService` foi criado mas **não está sendo chamado em lugar nenhum** do código.

**Impacto**: Faltas não geram automaticamente deduções de horas no banco de horas.

**Evidência**: 
- Nenhum Controller, Job, Observer ou Listener chama este serviço
- O modelo `Absence` existe mas fica vazio

**Solução Necessária**: Implementar integração automática (sugerir com Observer ou Listener)

---

### 2. **Lógica do Serviço Tem Problemas** ⚠️
**Problema**: Quando uma licença justificada é detectada, o serviço retorna `null` mas **ainda cria o `HourBank`** mesmo sem descontar horas.

**Código Problemático** (linha 118-130):
```php
if ($leaveCheck['has_leave']) {
    // Verificar se é falta injustificada ou licença justificada
    if ($leaveCheck['type'] === 'leave' && $this->isJustifiedLeave($leaveCheck['leave_type'])) {
        // Não descontar - é uma licença justificada
        return null;  // ← Retorna aqui
    }
    // ...
}

// Mas depois...
$hourBank = HourBank::firstOrCreate(...);  // ← Ainda cria HourBank!
```

**Impacto**: Gera registos vazios na tabela `hour_banks`

**Correção Necessária**: Fazer o `return null` ANTES de criar o `HourBank`

---

### 3. **Modelo `Absence` Não Tem Relacionamento com `LeaveAndAbsence`** ⚠️
**Problema**: 
- O modelo `Absence` foi criado para deduções automáticas
- Mas existe um outro modelo `LeaveAndAbsence` para licenças/faltas manuais
- Não está claro qual deles é usado no sistema

**Confusão no Código**:
- Widgets usam `LeaveAndAbsence`
- Filament Resource `AbsenceResource` aponta para `Absence`
- A tabela `leaves_and_absences` tem faltas, férias, licenças
- A tabela `absences` é para deduções de hora banco

**Recomendação**: Documentar claramente o propósito de cada modelo

---

### 4. **Não Há Testes Automáticos** ❌
**Problema**: Não existem testes que validem o fluxo completo de registar uma falta e descontar horas.

**Impacto**: Mudanças no código podem quebrar funcionalidade sem serem detectadas.

**Testes Criados Agora**:
- ✅ Deduções básicas funcionam
- ✅ Validação de licenças justificadas funciona (com bug)
- ✅ Validação de férias aprovadas funciona (com bug)
- ✅ Forçar deduções funciona
- ✅ Períodos de faltas contam corretamente dias úteis

---

### 5. **Configuração Não Está Documentada** 📋
**Problema**: Existe `config/hour_bank.php` mas não é usado correctamente em toda a aplicação.

**Faltam**:
- Exemplos de como usar o serviço
- Indicação de onde deve ser chamado
- Testes de integração com as páginas Filament

---

## ✅ O QUE FUNCIONA CORRETAMENTE

1. ✅ **Modelo `Absence`** está correctamente estruturado com relacionamentos
2. ✅ **Validação de licenças justificadas** está implementada (com pequeno bug)
3. ✅ **Validação de férias aprovadas** está implementada
4. ✅ **Cálculo de períodos** exclui correctamente finais de semana
5. ✅ **Acumulação de saldo** entre meses funciona corretamente
6. ✅ **Interface Filament** (`AbsenceResource`) está bem estruturada

---

## 🔧 RECOMENDAÇÕES DE CORREÇÃO

### Prioridade 1 (Crítica): Integração
```
Onde implementar:
1. Quando uma LeaveAndAbsence com tipo 'unjustified' é criada
2. Quando um funcionário falta (falta automática detectada)
3. Via um novo endpoint/job que o admin pode executar manualmente

Sugestão: Criar um Observer ou Job que:
- Procura por LeaveAndAbsence do tipo unjustified aprovadas
- Chama DeductHourBankService->handlePeriod() para cada uma
- Registra a dedução em Absence
```

### Prioridade 2: Corrigir Bug Lógico
```
Ficheiro: app/Services/Hour/DeductHourBankService.php
Linha: ~100-150

Mudar de:
if ($validateLeaves && ! $forceDeduction) {
    $leaveCheck = $this->checkForLeaveOrVacation($employeeId, $absenceDate);
    if ($leaveCheck['has_leave']) {
        // ... verificações
        return null;
    }
}
// Cria HourBank aqui (problema!)

Para:
if ($validateLeaves && ! $forceDeduction) {
    $leaveCheck = $this->checkForLeaveOrVacation($employeeId, $absenceDate);
    if ($leaveCheck['has_leave']) {
        // Verificação de licença justificada
        if ($leaveCheck['type'] === 'leave' && $this->isJustifiedLeave($leaveCheck['leave_type'])) {
            return null; // Sai antes de criar HourBank
        }
        // Ferías aprovadas
        if ($leaveCheck['type'] === 'vacation') {
            return null; // Sai antes de criar HourBank
        }
    }
}
// Só cria HourBank se passou nas verificações
$hourBank = HourBank::firstOrCreate(...);
```

### Prioridade 3: Documentação
- Criar guia de uso com exemplos
- Documentar o fluxo de criação de faltas
- Indicar onde o `DeductHourBankService` deve ser chamado

---

## 📊 Resultados dos Testes

| Teste | Status | Notas |
|-------|--------|-------|
| Deducção básica | ✅ PASS | Funciona corretamente |
| Licença justificada | ❌ FAIL | Cria HourBank vazio |
| Férias aprovadas | ❌ FAIL | Mesmo problema |
| Forçar deduções | ✅ PASS | Funciona quando forçado |
| Períodos | ✅ PASS | Conta dias úteis corretamente |
| Acumulação mensal | ✅ PASS | Saldos anteriores funcionam |
| Campos da Absence | ✅ PASS | Todos os dados guardados |
| Config de validação | ✅ PASS | Responde às configurações |

---

## 🎯 Próximos Passos Recomendados

1. **Corrigir o bug lógico** (Prioridade 1) - ~10 min
2. **Implementar integração automática** (Prioridade 1) - ~30 min
3. **Adicionar mais testes** cobrindo casos de uso completos - ~20 min
4. **Documentar** o sistema com exemplos - ~15 min
