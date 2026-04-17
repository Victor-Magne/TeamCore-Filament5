# Guia Rápido: Registar Faltas

> Para administradores: Como usar o novo sistema automático de faltas

---

## ⚡ Em 30 Segundos

1. Abrir **Ponto** (AttendanceLog)
2. Registar o dia **sem** preencher "Hora de Saída"
3. Salvar
4. ✅ Sistema automaticamente:
   - Detecta falta
   - Desconta 8h do banco de horas
   - Registra na auditoria

---

## 👣 Passo a Passo

### No Filament Admin

1. Navegar para **Ponto** → **Criar Novo**
2. Preencher:
   - **Funcionário**: (obrigatório)
   - **Data de Entrada**: (obrigatório)
   - **Hora de Entrada**: (obrigatório) - ex: 09:00
   - **Intervalo de Almoço**:
     - **Início**: (opcional) - ex: 12:30
     - **Fim**: (opcional) - ex: 13:30
   - **Hora de Saída**: (deixar em branco) ← Isto marca como FALTA
   - **Notas**: (opcional) - ex: "Doente", "Razão pessoal"
3. Clicar em **Guardar**

### Resultado Automático

```
✓ Absence criada em database
✓ HourBank reduzido em 480 min (8 horas)
✓ Referência guardada em AttendanceLog
✓ Log de auditoria preenchido
```

---

## ✅ Validações Automáticas

### NÃO Desconta Se:

| Condição | Razão |
|----------|-------|
| Tem "Hora de Saída" | É um dia normal, não falta |
| É sábado ou domingo | Fim de semana, não trabalha |
| Tem licença aprovada | Justificada (sick leave, casamento, etc) |
| Tem férias aprovadas | Período de férias, não desconta |
| Já existe Absence | Evita registar a mesma falta 2x |

---

## 🎯 Cenários Comuns

### Cenário 1: Falta Simple (Dia Normal)

**Situação**: Funcionário não apareceu no trabalho

```
1. Preencher formulário:
   - Funcionário: João Silva
   - Data: 17 de Abril 2026
   - Hora Entrada: 09:00
   - Hora Saída: [deixar em branco]
   - Notas: "Falta"

2. Guardar

3. Resultado:
   ✓ Absence criada
   ✓ HourBank: -8h (passa a dever 8 horas)
```

### Cenário 2: Licença Aprovada (NÃO Desconta!)

**Situação**: Funcionário está de licença médica

```
1. Primeiro, criar licença (antes do ponto):
   - Funcionário: João Silva
   - Tipo: Licença de Doença
   - Data: 17 de Abril 2026
   - Status: Aprovada

2. Depois, registar ponto (sem saída):
   - Funcionário: João Silva
   - Data: 17 de Abril 2026
   - Hora Entrada: 09:00
   - Hora Saída: [deixar em branco]

3. Resultado:
   ✓ Absence detectada
   ✓ Licença validada
   ✓ HourBank: NÃO desconta (justificada)
```

### Cenário 3: Fim de Semana (NÃO Desconta)

**Situação**: Funcionário não apareceu no sábado

```
1. Registar ponto:
   - Data: 18 de Abril 2026 (sábado)
   - Hora Saída: [deixar em branco]

2. Guardar

3. Resultado:
   ✓ Absence detectada
   ✓ É sábado → ignorado
   ✓ HourBank: NÃO desconta
```

### Cenário 4: Múltiplas Faltas (Uma Semana)

**Situação**: Funcionário faltou toda a semana

```
1. Registar ponto para Segunda:
   - Data: 14 de Abril
   - Hora Saída: [em branco]
   → HourBank: -8h

2. Registar ponto para Terça:
   - Data: 15 de Abril
   - Hora Saída: [em branco]
   → HourBank: -8h (total -16h)

3. Registar ponto para Quarta:
   - Data: 16 de Abril
   - Hora Saída: [em branco]
   → HourBank: -8h (total -24h)

... (continuar para quinta e sexta)

Resultado Final:
✓ 5 Absences criadas
✓ HourBank: -40h no mês
```

---

## 📊 Ver Resultados

### No Filament

1. Ir para **Faltas** (Absence)
   - Ver todas as faltas registadas
   - Filtrar por funcionário ou data
   - Ver horas deducidas

2. Ir para **Banco de Horas** (HourBank)
   - Ver saldo do mês
   - Horas deducidas vs adicionadas
   - Balanço total

### Por Linha de Comando

```bash
# Ver faltas do mês
php artisan tinker

>>> Absence::where('absence_date', '>=', '2026-04-01')
    ->where('absence_date', '<=', '2026-04-30')
    ->get();

>>> HourBank::where('month_year', '2026-04')->first();
```

---

## ⚠️ Erros Comuns

### ❌ "Hora de Saída" foi preenchida

**Problema**: Sistema não detectou como falta

**Solução**: Deixar "Hora de Saída" completamente em branco

**Exemplo de Correto**:
```
Hora Entrada: 09:00
Hora Saída: [vazio] ← Assim está correto!
```

### ❌ Falta registada mas não aparece

**Problema**: Pode estar com licença para essa data

**Solução**: Verificar em Licenças > Ver se há licença aprovada para essa data

### ❌ Desconto muito grande no mês

**Problema**: Múltiplas faltas acumuladas

**Solução**: Revisar o mês em Faltas > Filtrar por data

---

## 🔄 Corrigir Erros

### Remover Falta (Deslogar Desconto)

**Situação**: Registei uma falta mas era engano

```
1. Ir para Ponto > Encontrar o registo errado
2. Editar > Preencher "Hora de Saída" correta
3. Guardar

Resultado:
✓ AttendanceLog corrigido
✓ Absence removida
✓ HourBank revertido (horas recuperadas)
```

---

## 📞 Suporte

### Se algo não funcionar:

1. **Verificar**: Está preenchido "Hora de Saída"?
   - Se SIM → Sistema não detecta como falta

2. **Verificar**: É fim de semana?
   - Se SIM → Não desconta automaticamente

3. **Verificar**: Há licença aprovada?
   - Se SIM → Não desconta (justificada)

4. **Ver Logs**: 
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## 💡 Dicas

✅ Fazer no fim do dia: Registar faltas do dia

✅ Fazer no fim da semana: Revisar HourBank do mês

✅ Antes de registar falta: Confirmar que não é licença

✅ Deixar Notas: "Doente", "Razão pessoal" ajuda auditoria

---

## 🎓 Para Saber Mais

Ler: `docs/ATTENDANCE_LOG_INTEGRATION.md` - Guia técnico completo

