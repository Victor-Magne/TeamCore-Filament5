# Arquitetura e Segurança - Documentação Técnica

Esta secção descreve os fundamentos técnicos, a infraestrutura de segurança e os padrões de arquitetura adotados na Aplicação TeamCore.

## 1. Stack Tecnológica
A aplicação é construída sobre tecnologias modernas e robustas:
- **Linguagem:** PHP 8.3+ (utilizando constructor property promotion e tipagem estrita).
- **Framework Web:** Laravel 13.
- **Interface Administrativa:** Filament 5 (TALL Stack: Tailwind CSS, Alpine.js, Laravel, Livewire).
- **Base de Dados:** MySQL 8.0+.
- **Testes:** Pest 4.
- **Frontend:** Vite 3 + Tailwind CSS 4.

## 2. Autenticação e Gestão de Utilizadores
A autenticação é gerida pelo **Filament Breezy**, que oferece:
- Login seguro.
- Suporte a **Two-Factor Authentication (2FA)**.
- Suporte a **Passkeys** (WebAuthn).
- Funcionalidade de "Esqueci-me da palavra-passe".

### Modelo `User`
O modelo `User` (`app/Models/User.php`) centraliza a identidade do utilizador:
- **Traits Utilizadas:**
  - `HasRoles`: Integração com Spatie Permission para RBAC.
  - `LogsActivity`: Registo automático de auditoria via Spatie Activity Log.
  - `TwoFactorAuthenticatable`: Suporte a 2FA.
- **Atributos Especiais:**
  - `must_change_password`: Booleano que força o utilizador a alterar a palavra-passe no primeiro acesso.
  - `employee_id`: Chave estrangeira que liga o utilizador ao seu perfil de funcionário.

## 3. Controlo de Acesso Baseado em Funções (RBAC)
A segurança de acesso é implementada através do **Filament Shield**, utilizando o modelo de permissões do Spatie.

### Permissões e Políticas
- As permissões seguem o formato `{Ação}:{Recurso}` (ex: `ViewAny:User`, `Create:Employee`).
- **Políticas (Policies):** Cada modelo possui uma Policy correspondente em `app/Policies/` que valida estas permissões.
- **Permissões Customizadas:** Definidas em `config/filament-shield.php`, incluem:
  - `Access:AdminPanel`: Permite aceder à área de administração.
  - `Access:AppPanel`: Permite aceder ao portal do funcionário.
  - `Approve:OwnVacation` / `Approve:OwnLeaveAndAbsence`: Exceções para permitir a auto-aprovação (bloqueada por defeito).

### Hierarquia e Visibilidade (Scopes)
A aplicação utiliza **Eloquent Scopes** e a trait `HasHierarchicalQuery` para garantir o isolamento de dados:
- **Admin:** Vê todos os registos.
- **HR:** Vê registos de toda a organização ou do seu departamento, conforme configurado.
- **Employee:** Vê apenas os seus próprios dados.

## 4. Auditoria e Logs de Atividade
Utiliza-se o **Spatie Activity Log** para rastrear todas as alterações sensíveis.
- **LogsActivity Trait:** Aplicada a modelos como `User`, `Employee`, `Contract`, etc.
- **Configuração:** O método `getActivitylogOptions()` define que apenas campos alterados (`logOnlyDirty`) são registados, incluindo os valores antigos e novos.
- **Recurso:** `ActivityLogResource` permite visualizar estas ações no painel de administração.

## 5. Automação com Observers
A aplicação utiliza o padrão **Observer** para manter a integridade dos dados e automatizar fluxos:
- `EmployeeObserver`: Ao criar um funcionário, gera automaticamente o utilizador, o contrato inicial e o banco de horas.
- `AbsenceObserver`: Recalcula o saldo do banco de horas sempre que uma ausência é alterada.
- `ContractObserver`: Sincroniza o cargo do funcionário com o contrato ativo.
- `AttendanceLogObserver`: Processa deduções no banco de horas com base nas picagens.

## 6. Segurança Adicional
- **Proteção CSRF:** Ativa em todos os formulários.
- **Prevenção de SQL Injection:** Garantida pelo uso do Eloquent ORM e Query Builder.
- **Sanitização de Dados:** Validação rigorosa em todos os formulários de entrada.
- **Soft Deletes:** Utilizados em modelos críticos para evitar a perda acidental de dados e manter integridade referencial histórica.
