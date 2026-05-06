# Manual Técnico - Aplicação TeamCore

## 1. Objetivo
Este manual técnico descreve a arquitetura, os componentes, os fluxos críticos, os procedimentos de operação e manutenção da Aplicação TeamCore.

## 2. Visão Geral da Solução
A TeamCore é uma aplicação de gestão de Recursos Humanos construída com Laravel 13 e Filament 5.

Funcionalidades principais:
- Gestão de colaboradores, contratos, cargos e unidades.
- Registo de presença, banco de horas e deduções por faltas.
- Gestão de férias e licenças com aprovação.
- Processamento salarial com cálculo automático.
- Auditoria de alterações e autenticação com 2FA/Passkeys.

## 3. Stack Técnica
- PHP: 8.3+
- Laravel: 13
- Filament: 5
- Livewire: 4
- TailwindCSS: 4
- Base de dados: MySQL (produção), SQLite em memória (testes)
- Testes: Pest 4
- Auditoria: Spatie Activity Log
- Autorização: Filament Shield + Spatie Permission
- Autenticação avançada: Filament Breezy

## 4. Arquitetura e Organização do Código
Estrutura principal:
- `app/Models`: entidades de domínio.
- `app/Filament/Resources`: CRUDs e UI de administração.
- `app/Policies`: regras de autorização por modelo.
- `app/Services`: lógica de negócio (Payroll, HourBank, Onboarding).
- `app/Observers`: automações reativas de domínio.
- `app/Console/Commands`: tarefas de manutenção/rotina.
- `database/migrations`: estrutura de dados.
- `database/seeders`: dados iniciais/estrutura de referência.
- `tests/Feature` e `tests/Unit`: validação automatizada.

## 5. Domínio de Dados (Resumo)
Modelos centrais:
- `User`, `Employee`, `Contract`, `Designation`, `Unit`
- `AttendanceLog`, `HourBank`, `HourBankMovement`, `Absence`
- `Vacation`, `LeaveAndAbsence`, `Payroll`
- `Country`, `State`, `City`
- `ActivityLog`

Restrições críticas:
- campos com `UNIQUE` (ex.: `users.email`, `employees.email`, `countries.code`, `designations.name`).
- integridade relacional com foreign keys.

## 6. Controlo de Acesso e Segurança
RBAC com perfis:
- `admin`: controlo total do sistema.
- `hr`: gestão funcional de RH.
- `employee`: acesso ao portal individual.

Camadas de proteção:
- middleware de acesso a painéis.
- policies por recurso/modelo.
- isolamento de dados por hierarquia organizacional.
- proteção CSRF e validação de input.
- auditoria de eventos sensíveis.

## 7. Fluxos de Negócio Críticos
### 7.1 Onboarding de colaborador
Ao criar `Employee`, o sistema gera automaticamente:
- `User`
- `Contract` inicial
- `HourBank`

### 7.2 Presença e banco de horas
- registo de `time_in`, almoço e `time_out`.
- cálculo de minutos trabalhados.
- criação de movimentos de banco de horas.
- deduções automáticas por atraso/falta conforme regras.

### 7.3 Férias e licenças
- aprovação/rejeição com impacto em saldos.
- prevenção de autoaprovação sem permissão específica.
- integração com regras de assiduidade.

### 7.4 Payroll
- cálculo de valor hora.
- aplicação de extras (coeficiente de horas extra).
- aplicação de deduções.
- prevenção de duplicação por período.

## 8. Operação Local (Developer Setup)
Passos típicos:
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

Se houver erro de assets Vite, executar:
```bash
npm run build
```
ou manter:
```bash
npm run dev
```

## 9. Comandos Úteis de Manutenção
- Executar testes completos:
```bash
php artisan test
```
- Execução compacta:
```bash
php artisan test --compact
```
- Formatação de código PHP:
```bash
vendor/bin/pint --dirty --format agent
```
- Verificação diária de assiduidade:
```bash
php artisan app:check-daily-attendance
```
- Re-sincronização do banco de horas:
```bash
php artisan app:sync-hour-bank
```

## 10. Testes e Qualidade
Estado atual validado:
- 23 ficheiros de teste
- 100 testes
- 222 assertions

Cobertura funcional inclui:
- onboarding
- policies
- presença e deduções
- férias/licenças
- payroll
- comandos
- widgets e páginas principais

Boas práticas:
- sempre correr testes afetados antes de merge.
- para alterações transversais, correr a suíte completa.
- manter factories determinísticas em campos `UNIQUE` para reduzir flakiness.

## 11. Procedimentos de Atualização da Aplicação
### 11.1 Antes de atualizar
- confirmar backup da base de dados.
- validar branch e estado limpo do deploy.
- executar suíte de testes.

### 11.2 Atualização
1. atualizar código.
2. executar `composer install --no-dev` (produção).
3. executar migrações.
4. compilar assets (`npm run build`).
5. limpar/otimizar caches Laravel.

Comandos típicos:
```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 11.3 Pós-atualização
- verificar login admin e employee.
- validar criação de colaborador.
- validar registo de presença.
- validar geração de payroll de teste.

## 12. Monitorização e Auditoria
- Logs aplicacionais: `storage/logs/`.
- Activity Log (auditoria funcional) via `activity_log`.
- Notificações persistidas na tabela `notifications`.

Recomendação operacional:
- monitorizar erros de autenticação, falhas de jobs e exceções de DB.

## 13. Troubleshooting
### Problema: falhas intermitentes de testes por `UNIQUE`
Causa comum: factories com dados aleatórios não únicos.
Ação: garantir geração única determinística para campos com índice único.

### Problema: erro de manifesto Vite
Ação:
```bash
npm run build
```
ou em ambiente de desenvolvimento:
```bash
npm run dev
```

### Problema: acesso negado no painel
Ação:
- validar role/permissões.
- validar policies.
- confirmar vínculo `User` -> `Employee` quando aplicável.

## 14. Rotina de Manutenção Recomendada
Diária:
- monitorizar logs e erros críticos.
- validar comando de assiduidade.

Semanal:
- executar suíte de testes completa.
- rever activity logs de ações sensíveis.

Mensal:
- revisão de permissões e contas inativas.
- revisão de performance de queries e recursos mais usados.

## 15. Checklist de Segurança
- credenciais fora do repositório.
- `.env` protegido e não versionado.
- backups regulares.
- princípio de menor privilégio (roles/permissões).
- revisão periódica de utilizadores com perfil `admin`.

## 16. Evolução e Governança
Ao adicionar novas funcionalidades:
- criar migration e policy correspondente.
- adicionar testes de feature e unit conforme impacto.
- atualizar documentação temática em `docs/`.
- atualizar este manual quando houver alteração operacional.

## 17. Referências Internas
- Índice documental: `docs/README.md`
- RPP: `RPP.md`
- Configuração principal de dependências: `composer.json`
