
---

**Curso Profissional de Técnico de Informática-Sistemas**
Código de Referência do CNQ — 481039
Ciclo de Formação 2023/2026 | Ano letivo 2025/2026

---

# Prova de Aptidão Profissional — Relatório

## Aplicação TeamCore — Uma nova gestão

| | |
|---|---|
| **Autor** | Victor Gabriel Cristino Gomes — N.º 21 |
| **Orientadora** | Zélia Capitão |
| **Data** | 01/06/2026 |
| **Versão anterior** | 29/05/2026 |

---

## Agradecimentos

**Jorge Lafuente** — tutor de estágio ao longo do 11.º e do 12.º ano, e uma das pessoas que mais influenciou o rumo desta aplicação. Foi quem me apresentou o Laravel e o Filament, despertando em mim o interesse por estas tecnologias, e quem sugeriu funcionalidades concretas que acabaram por enriquecer significativamente o projeto. O seu acompanhamento e partilha de experiência profissional foram determinantes para o resultado final.

**Zélia Capitão** — orientadora da aplicação, pelo acompanhamento contínuo e disponibilidade ao longo de todo o processo, e pelas orientações que permitiram manter o trabalho no rumo certo.

**Ana Paula Azevedo** — docente de Sistemas Informáticos e Aplicações Web, pelos conhecimentos transmitidos ao longo do curso, que constituíram a base técnica essencial para a concretização desta aplicação.

**Willian Washington** — grande amigo que esteve presente durante todo este percurso. Pelo apoio na área de qualidade e testes, pela disponibilidade em rever e validar funcionalidades, e acima de tudo pela amizade genuína e pelo incentivo constante nos momentos mais desafiantes do desenvolvimento.

*A todos, o meu sincero obrigado.*

---

## Índice

1. [Acrónimos, Abreviaturas e Siglas](#acrónimos-abreviaturas-e-siglas)
2. [Resumo](#resumo)
3. [Introdução](#introdução)
   - [Enquadramento Teórico](#enquadramento-teórico)
   - [Fundamentação do Tema](#fundamentação-do-tema)
   - [Motivação e Contexto](#motivação-e-contexto)
   - [Objetivos Principais](#objetivos-principais)
   - [Abordagem e Metodologia](#abordagem-e-metodologia)
4. [Desenvolvimento da Aplicação](#desenvolvimento-da-aplicação)
   - [Metodologia e Ferramentas](#metodologia-e-ferramentas)
   - [Arquitetura de Dados Relacional](#arquitetura-de-dados-relacional)
   - [Principais Implementações](#principais-implementações)
   - [Controlo de Acesso e Segurança](#controlo-de-acesso-e-segurança)
   - [Gestão de Horas e Banco de Horas](#gestão-de-horas-e-banco-de-horas)
   - [Experiência do Utilizador](#experiência-do-utilizador)
   - [Melhorias e Otimizações](#melhorias-e-otimizações)
   - [Validação de E-mail e Automação de Criação](#validação-de-e-mail-e-automação-de-criação)
   - [Recursos Filament Implementados](#recursos-filament-implementados)
   - [Automatização com *Observers* e *Listeners*](#automatização-com-observers-e-listeners)
   - [Organograma de Unidades e Sincronização de Utilizadores](#organograma-de-unidades-e-sincronização-de-utilizadores)
5. [Suíte de Testes Automatizados](#suíte-de-testes-automatizados)
6. [Conclusão](#conclusão)
   - [Dificuldades Encontradas e Soluções Adotadas](#dificuldades-encontradas-e-soluções-adotadas)
   - [Pontos Fortes da Aplicação](#pontos-fortes-da-aplicação)
   - [Pontos a Melhorar](#pontos-a-melhorar)
   - [O que Aprendi com a Aplicação](#o-que-aprendi-com-a-aplicação)
   - [Importância para o Meu Futuro Profissional](#importância-para-o-meu-futuro-profissional)
   - [Reflexão Final](#reflexão-final)
7. [Bibliografia](#bibliografia)
8. [Anexos](#anexos)

---

## Índice de Figuras

| Figura | Descrição |
|--------|-----------|
| Figura 1 | Diagrama de Entidades e Relações (ER) — Estrutura da base de dados relacional |
| Figura 2 | *Dashboard* Admin com *widgets* de estatísticas (contratos, funcionários, presença) |
| Figura 3 | Gestão de Funcionários com tabela de dados e filtros de acesso |
| Figura 4 | Visualização de Dados Pessoais e Banco de Horas com acesso restrito |
| Figura 5 | *Dashboard* do Funcionário com *widgets* de resumo e ações rápidas |
| Figura 6 | Registo de Presença com campos de entrada/saída e pausas |
| Figura 7 | Visualização do Banco de Horas com saldo acumulado e histórico |
| Figura 8 | Página de *check-in* simplificado com botões de ação dinâmica e histórico diário |
| Figura 9 | Notificações *toast* ao criar funcionário — 4 mensagens de sucesso |
| Figura 10 | Diálogo de confirmação de ações destrutivas com avisos visuais |
| Figura 11 | *Badges* visuais — indicadores de funções e estados de contrato |
| Figura 12 | Formulário de criação de funcionário com validação de e-mail |
| Figura 13 | *EmployeeResource* — lista com tabela, filtros e ações |
| Figura 14 | *ContractResource* com ação de *download* de PDF de contrato |
| Figura 15 | *ActivityLogResource* — visualização de histórico de auditoria com filtros |
| Figura 16 | *UnitsTree* — Organograma hierárquico de unidades organizacionais com NestedSet |
| Figura 17 | Interface do Banco de Horas com *Relation Manager* de movimentos |
| Figura 18 | Página de Gestão de Equipa (*ManageTeam*) com *widgets* de presença e pedidos pendentes |

---

## Acrónimos, Abreviaturas e Siglas

| Sigla | Descrição |
|-------|-----------|
| CRUD | *Create, Read, Update, Delete* |
| CSRF | *Cross-Site Request Forgery* |
| HR | *Human Resources* |
| MVP | *Minimal Viable Product* |
| ORM | *Object-Relational Mapping* |
| PAP | Prova de Aptidão Profissional |
| PDF | *Portable Document Format* |
| PME | Pequena e Média Empresa |
| PT-PT | Português de Portugal |
| RBAC | *Role-Based Access Control* |
| RH | Recursos Humanos |
| TLD | *Top-Level Domain* |
| UI | *User Interface* |
| UX | *User Experience* |

---

## Resumo

O presente relatório de PAP descreve o desenvolvimento da Aplicação TeamCore, uma aplicação de gestão de RH concebida para simplificar os processos desse setor. A aplicação foi motivada pela necessidade de uma ferramenta de RH que fosse simples, fluida e segura de utilizar, contrastando com a complexidade de muitos sistemas existentes.

A Aplicação TeamCore foi desenvolvida com o objetivo de alcançar uma gestão abrangente de dados de funcionários, cargos, contratos e férias, promover a automatização de processos para minimizar a margem de erro, e fornecer auditoria completa de todas as operações. A qualidade técnica, focada na segurança, usabilidade e conformidade regulatória, foi um pilar central do desenvolvimento.

A metodologia de trabalho incluiu o levantamento detalhado de requisitos, a modelação da base de dados relacional e o desenvolvimento técnico com a *framework* Laravel v13 e Filament v5 para o *backend* e *frontend*. O processo foi complementado por validação rigorosa de funcionalidades e testes automatizados.

> **Estado da Aplicação — 01 de Junho de 2026**
>
> A Aplicação TeamCore encontra-se numa fase de maturidade *production-ready* com todas as funcionalidades completamente implementadas, testadas e validadas. A aplicação inclui: 18 modelos com isolamento de dados RBAC; 16 *resources* Filament com políticas de autorização; 17 *policies* para controlo granular; sistema de banco de horas cumulativo com rastreio de movimentos e validação automática de licenças; auditoria completa via Spatie Activity Log; 6 *observers* e 1 *listener* para automatização de processos; autenticação segura via Filament Breezy e *Passkeys*; *middleware* `CheckActiveUser` para gestão de ativação de utilizadores; serviços especializados com proteção de concorrência (`VacationBalanceService` com `DB::transaction` + `lockForUpdate`); configuração centralizada de negócio em `config/hr.php`; suíte de testes automatizados com Pest v4 com 29 ficheiros de teste; página de calendário pessoal (*EmployeeCalendar*) com vista mensal/semanal/lista e codificação por cores; *widget* de ponto do dia (*TodayAttendanceWidget*) com estatísticas de entrada, almoço, saída e tempo trabalhado; *widget* de faltas (*EmployeeAbsencesWidget*); validação de sobreposição de períodos em pedidos de férias e licenças para impedir conflitos de datas; organograma hierárquico de unidades (*UnitsTree*) com drag-and-drop via NestedSet; ações de sincronização de utilizadores na listagem de funcionários; e página de gestão de equipa (*ManageTeam*) para responsáveis de unidade com *widgets* de presença e pedidos pendentes.

---

## Introdução

### Enquadramento Teórico

A Aplicação TeamCore foi desenvolvida no âmbito da Prova de Aptidão Profissional do Curso Profissional de Técnico de Informática de Sistemas, simulando um contexto real de trabalho numa empresa de média dimensão com necessidades concretas de gestão de Recursos Humanos.

A gestão eficaz de Recursos Humanos é uma das funções críticas em qualquer organização moderna. Com a transformação digital, as aplicações de *software* para sistemas de informação de RH tornaram-se ferramentas essenciais para otimizar processos, reduzir erros administrativos e facilitar a tomada de decisões estratégicas.

A presente aplicação insere-se na área de Desenvolvimento de Sistemas de Informação, abrangendo:

- **Desenvolvimento *Backend*:** implementação de lógica de negócio, processamento de dados e integração de bases de dados relacionais.
- **Desenvolvimento *Frontend*:** criação de interfaces intuitivas e responsivas que facilitam a interação do utilizador.
- **Arquitetura de *Software*:** desenho de sistemas escaláveis, seguros e de fácil manutenção.
- **Controlo de Acesso:** implementação de mecanismos de autenticação e autorização para proteger dados sensíveis.

### Fundamentação do Tema

Esta aplicação foi selecionada por várias razões estratégicas:

- **Relevância Profissional:** o desenvolvimento de sistemas de RH representa uma aplicação prática e imediata dos conhecimentos adquiridos no curso, incluindo programação, bases de dados, segurança e *design* de interface.
- **Problema Real:** muitas organizações, especialmente PMEs, enfrentam dificuldades em gerir dados de funcionários de forma centralizada e eficiente. *Legacy systems* apresentam frequentemente problemas de usabilidade, integração limitada e custos elevados de manutenção.
- **Oportunidade de Aprendizagem:** o âmbito da aplicação permite aplicar múltiplas tecnologias atuais no mercado (Laravel, Filament, MySQL, Pest) e consolidar conhecimentos em autenticação, autorização, validação de dados e testes automatizados.
- **Viabilidade:** a aplicação tem uma amplitude apropriada para ser concluída num ciclo de desenvolvimento estruturado, permitindo implementar uma solução funcional com qualidade técnica.

### Motivação e Contexto

A Aplicação TeamCore nasce da necessidade de uma ferramenta de gestão de Recursos Humanos intuitiva e eficiente, capaz de apoiar tanto equipas de gestão como colaboradores nas operações diárias. A solução foi concebida para centralizar a gestão de funcionários, cargos e contratos, automatizar tarefas de registo de tempo e horas extras, e fornecer suporte à decisão através de relatórios e visualizações estruturadas.

### Objetivos Principais

Esta aplicação PAP visa desenvolver um sistema de gestão que:

1. **Centralize dados de RH** — consolide informação de funcionários, departamentos, designações, contratos e períodos de férias numa base de dados estruturada e facilmente consultável.
2. **Automatize registos de trabalho** — permita o registo eficiente de horas trabalhadas, com suporte a pausas, cálculo automático de horas extras e manutenção atualizada do banco de horas individual.
3. **Implemente controlo de acesso granular** — segmente permissões por função de utilizador (Administrador, RH, Colaborador), garantindo que cada utilizador acede apenas aos dados e funcionalidades adequados.
4. **Ofereça uma experiência de utilizador otimizada** — proporcione uma interface intuitiva, com notificações contextuais e confirmações para prevenir ações não intencionais.
5. **Garanta qualidade técnica** — assegure o funcionamento correto da aplicação através de testes automatizados, validação de regras de autorização e boas práticas de segurança.

### Abordagem e Metodologia

O desenvolvimento foi realizado em ciclos iterativos, adotando:

- **Tecnologias modernas:** Laravel v13 como *framework backend*, Filament v5 como interface administrativa unificada, PHP v8.4 e MySQL como base de dados relacional.
- **Testes automatizados:** desenvolvimento orientado a testes utilizando a *framework* Pest/PHPUnit para validação contínua de funcionalidades.
- **Validação com profissionais:** consulta com profissionais de RH durante o desenvolvimento para validar requisitos e funcionalidades essenciais.
- **Boas práticas de engenharia:** isolamento de dados ao nível do modelo através de *Policies* Eloquent, arquitetura em camadas clara e padrões de código consistentes.

---

## Desenvolvimento da Aplicação

### Metodologia e Ferramentas

A Aplicação TeamCore foi desenvolvida com a *framework* Laravel v13, utilizando Filament v5 como interface administrativa unificada e PHP v8.4 como linguagem de desenvolvimento. A persistência de dados foi implementada em MySQL, com uma arquitetura relacional que suporta entidades como Funcionários, Contratos, Departamentos, Designações, Bancos de Horas, Registos de Presença, Pedidos de Licença, Ausências e Registos de Auditoria.

O processo de desenvolvimento adotou uma metodologia iterativa com ciclos bissemanais de análise, implementação, testes e validação. Foi utilizado o Git para controlo de versão e mantida uma suíte de testes automatizados (Pest v4) executada a cada novo *commit*, garantindo que não ocorressem regressões durante o desenvolvimento.

| Tecnologia | Função na Aplicação | Versão |
|---|---|---|
| Laravel | *Framework Backend* | 13.0 |
| Filament | Construção da interface | 5.5+ |
| PHP | Linguagem de desenvolvimento | 8.4 |
| MySQL | Base de dados relacional | 8.0 |
| Pest | *Framework* de testes | 4.5 |
| Vite | *Bundler Frontend* | 6.2.4+ |
| Spatie Activity Log | Auditoria de operações | 5.0 |
| wsmallnews/filament-nestedset | Gestão hierárquica de unidades | 2.2 |

---

### Arquitetura de Dados Relacional

> **Figura 1:** Diagrama de Entidades e Relações (ER) — Estrutura da base de dados relacional.

A aplicação suporta as seguintes entidades principais:

| Entidade | Descrição |
|---|---|
| **Funcionários** (`Employee`) | Dados pessoais, contactos, informações profissionais e saldo de férias. |
| **Utilizadores** (`User`) | Credenciais de acesso, papéis e permissões (autenticação via Filament Breezy). |
| **Contratos** (`Contract`) | Vínculo laboral (*permanent*, *fixed_term*, *unfixed_term*, *service_provision*, *internship*), remuneração e estado (*active*, *terminated*, *on_hold*). |
| **Unidades Organizacionais** (`Unit`) | Estrutura hierárquica da empresa (*direction*, *management*, *department*, *section*) com gestor responsável e suporte a NestedSet para organograma. |
| **Cargos** (`Designation`) | Funções profissionais com níveis (*junior*, *pleno*, *senior*, *specialist*, *lead*) e salários base. |
| **Banco de Horas** (`HourBank`) | Controlo cumulativo de horas com suporte a movimentos históricos. |
| **Movimentos do Banco de Horas** (`HourBankMovement`) | Registo polimórfico de cada alteração ao saldo (ganhos e descontos). |
| **Registos de Presença** (`AttendanceLog`) | Registos diários de entrada/saída/pausas com cálculo automático de minutos totais. |
| **Ausências** (`Absence`) | Auditoria de descontos de horas com tipos (*unjustified_absence*, *partial_absence*, *other*). |
| **Férias** (`Vacation`) | Gestão de férias anuais com saldo por ano, estado de aprovação e dias tomados. |
| **Licenças e Afastamentos** (`LeaveAndAbsence`) | Licenças formais (*sick_leave*, *parental*, *marriage*, *bereavement*, *justified_absence*) e ausências injustificadas (*unjustified*) com fluxo de aprovação. |
| **Processamento Salarial** (`Payroll`) | Geração automática de recibos de vencimento com base em contratos e movimentos do banco de horas. |
| **Subscrições Push** (`PushSubscription`) | Registo de endpoints de notificações *Web Push* associados a utilizadores, permitindo o envio de alertas em tempo real via protocolo VAPID. |
| **Localização** | País (`Country`), Estado (`State`) e Cidade (`City`) para preenchimento de dados de funcionários. |

---

### Principais Implementações

A aplicação utiliza uma interface administrativa unificada (`/admin`) onde o acesso a recursos e dados é controlado dinamicamente via funções e permissões geridas pelo Filament Shield. Esta abordagem simplifica a navegação e centraliza a gestão, garantindo o isolamento de dados através de *Policies* Eloquent.

> **Figura 2:** *Dashboard* Admin com *widgets* de estatísticas (contratos, funcionários, presença).
> *(Print: aceder a `/admin` com utilizador Admin.)*

> **Figura 3:** Gestão de Funcionários com tabela de dados e filtros de acesso.
> *(Print: aceder a `/admin/employees`.)*

> **Figura 4:** Visualização de Dados Pessoais e Banco de Horas com acesso restrito.
> *(Print: aceder a `/admin/employees/{id}`.)*

#### Áreas de Gestão por Perfil

**Administração (ADMIN)**
- Gestão completa de utilizadores, papéis e unidades organizacionais.
- Administração de cargos, benefícios e auditoria global.
- Controlo total das configurações da aplicação.

**Gestão de RH (HR)**
- Gestão completa de funcionários e contratos.
- Processamento de licenças, faltas e salários (*Payroll*).
- Relatórios estratégicos e análise de produtividade.

**Acesso Pessoal (EMPLOYEE)**
- Visualização de dados pessoais e banco de horas.
- Gestão de férias e pedidos de licenças com fluxo de aprovação.
- *Dashboard* interativo com *widgets* detalhados, incluindo ponto do dia, faltas e ações rápidas.
- O portal do funcionário utiliza um `EmployeeDashboard` personalizado que centraliza o contrato ativo, saldo de férias, *widget* de ponto do dia (`TodayAttendanceWidget`) e *widget* de faltas (`EmployeeAbsencesWidget`), garantindo ao colaborador uma visão 360° da sua situação.
- **Calendário pessoal** (`EmployeeCalendar`) acessível em `/app/calendar`, com vista mensal, semanal e em lista: apresenta férias a laranja/verde (consoante o estado), licenças a azul e faltas a vermelho, com atalhos para solicitar férias ou licença diretamente no calendário.
- **Gestão de equipa** (`ManageTeam`) acessível em `/app/manage-team`, disponível apenas para utilizadores que sejam responsáveis de unidade: apresenta estatísticas de equipa, pedidos pendentes de aprovação e registos de presença dos subordinados.

> **Figura 5:** *Dashboard* do Funcionário com *widgets* de resumo e ações rápidas.
> *(Print: aceder a `/app` com um utilizador com papel *employee*.)*

#### Características da Interface

- Isolamento de dados ao nível do modelo através de *Policies* Eloquent (*scopes*).
- Componentes de UI, recursos e ações visíveis apenas para perfis autorizados.
- Menu de navegação dinâmico que se adapta às permissões do utilizador (Filament Navigation).
- *Middleware* robusto para verificação de permissões via Filament Shield.
- Navegação e estrutura otimizadas para uma experiência de utilizador fluida e centralizada.

---

### Controlo de Acesso e Segurança

O sistema implementa um modelo RBAC (*Role-Based Access Control*) com papéis e permissões dinâmicas via Filament Shield.

#### Papéis Dinâmicos

| Papel | Descrição |
|---|---|
| **ADMIN** | Acesso completo ao sistema — gestão de utilizadores, unidades e auditoria. |
| **HR** | Gestor de recursos humanos — funcionários, contratos, licenças e férias. |
| **EMPLOYEE** | Colaborador — acesso limitado a dados pessoais e pedidos próprios. |

#### Implementação em Três Camadas

1. ***Middleware* de Rotas** — bloqueio de acesso inicial via verificação de papel através do Filament Shield.
2. **Filament *Policies*** — 17 *policies* implementadas para controlo granular (`UserPolicy`, `EmployeePolicy`, `ContractPolicy`, etc.). Inclui ainda `ActivityPolicy` para o modelo base `Activity` do Spatie, complementando a `ActivityLogPolicy` do recurso de auditoria.
3. ***Eloquent Scopes*** — isolamento de dados ao nível da *query* com visibilidade hierárquica recursiva: colaboradores veem apenas os seus próprios dados, enquanto gestores veem os dados de todos os subordinados nas unidades que gerem e respetivas sub-unidades em cascata.

#### Recursos Filament com Controlo de Acesso (16 total)

| Área | Recursos |
|---|---|
| Gestão de Sistema | Users, Roles, Units (Admin) |
| Gestão de Funcionários | Employees, AttendanceLogs, ActivityLogs (HR/Admin) |
| Gestão de Organização | Designations, Countries, States, Cities |
| Gestão de Contratos | Contracts |
| Gestão de Ausências | Vacations, LeaveAndAbsences |
| Gestão de Banco de Horas | HourBanks, HourBankMovements (via *Relation Manager*), Absences |
| Gestão de Salários | Payrolls |

#### Proteções de Segurança Implementadas

- **Isolamento de dados:** visibilidade hierárquica recursiva baseada na gestão de unidades (unidades filhas e descendentes).
- **Auto-aprovação bloqueada:** o utilizador não pode aprovar os seus próprios pedidos (via *Policies*).
- ***Soft deletes*:** preservação de histórico em todos os modelos críticos.
- ***Activity logging*:** rastreio completo via Spatie Activity Log (automático com a *trait* `LogsActivity`).
- **Serviços especializados:** lógica de negócio complexa encapsulada em *Services* (`GeneratePayrollService`, `CalculateExtraHoursService`, `DeductHourBankService`, `HourBankService`, `AttendanceCalculationService`, `VacationBalanceService`, `ContractPdfService`).
- ***Middleware* de ativação:** `CheckActiveUser` verifica o campo `is_active` em cada pedido — utilizadores desativados são imediatamente desautenticados e redirecionados, sem necessidade de expirar a sessão manualmente.
- **Proteção de concorrência:** `VacationBalanceService` usa `DB::transaction` + `lockForUpdate` para prevenir *race conditions* em aprovações simultâneas de férias.
- **Configuração centralizada:** `config/hr.php` define todos os limiares de negócio (tolerâncias, multiplicadores, limites de atrasos), eliminando constantes dispersas no código.
- **Proteção CSRF:** *tokens* em todos os formulários.
- **Autenticação:** Filament Breezy com suporte a *Passkeys*.
- **Auditoria de modelos:** registo automático de criação, atualização e eliminação com *tracking* do utilizador.
- **Notificações *Web Push*:** o modelo `PushSubscription` regista endpoints de notificação dos utilizadores, integrando com o pacote `laravel-notification-channels/webpush` para alertas em tempo real (por exemplo, alteração de estado de pedidos de férias).

---

### Gestão de Horas e Banco de Horas

> **Figura 6:** Registo de Presença com campos de entrada/saída e pausas.
> *(Print: aceder a `/admin/attendance-logs/create`.)*

> **Figura 7:** Visualização do Banco de Horas com saldo acumulado e histórico.
> *(Print: aceder a `/admin/hour-banks`.)*

#### Sistema de *Check-in* Simplificado

A aplicação disponibiliza uma interface dedicada para o registo de presença simplificado. Esta página permite ao funcionário realizar o *check-in* e *check-out* com um único clique, capturando automaticamente o *timestamp* do servidor. O sistema gere inteligentemente o estado do dia, alternando entre:

1. Entrada (*Time In*)
2. Início de Pausa (*Lunch Break Start*)
3. Fim de Pausa (*Lunch Break End*)
4. Saída (*Time Out*)

A página valida se o utilizador possui um `employee_id` associado antes de permitir o acesso, garantindo a integridade dos dados.

> **Figura 8:** Página de *check-in* simplificado com botões de ação dinâmica e histórico diário.
> *(Print: aceder a `/app/attendance-check-in` com um utilizador que tenha `employee_id`.)*

#### Registos de Trabalho

- Entrada/saída com *timestamp* automático.
- Suporte a múltiplos registos por dia.
- Cálculo automático de durações.

#### Pausas e Intervalos

- Campos `break_start` / `break_end` para registo de pausas.
- Exclusão automática do tempo de pausa nos cálculos.
- Validação da lógica de intervalos (início antes do fim).

#### Banco de Horas

- Atualização automática e incremental após cada registo validado.
- Rastreio cumulativo de horas através de Movimentos (`HourBankMovement`).
- Criação automática atómica via `EmployeeOnboardingService`.
- Relatórios, extratos de saldo e comando de sincronização (`app:sync-hour-bank` — com suporte ao modo de simulação `--dry-run`, que identifica e lista movimentos órfãos sem efetuar alterações na base de dados).
- Validação automática de licenças antes de descontar: o sistema verifica se existe uma licença ou férias aprovada antes de efetuar desconto por ausência.
- Suporte a períodos de ausência: cálculo correto apenas para dias úteis (segunda a sexta).
- Configuração flexível: tipos de licenças justificadas versus injustificadas são configuráveis em `config/hr.php`.

#### Serviço Central do Banco de Horas (`HourBankService`)

O `HourBankService` é o motor central de gestão incremental do banco de horas. Delega o cálculo técnico ao `CalculateExtraHoursService` e oferece três operações fundamentais:

- **`syncLog`** — sincroniza um movimento com base num registo de presença, calculando extras ou défices e evitando dupla penalização quando já existe uma `Absence` registada para o mesmo dia.
- **`syncAbsence`** — sincroniza um movimento com base numa ausência registada, reprocessando automaticamente o log de presença do mesmo dia para consistência.
- **`removeMovement`** — remove movimentos órfãos (cujas origens foram eliminadas) e corrige os saldos em transação.

#### Auditoria de Ausências (*Absence Resource*)

- Visualização *read-only* de todas as ausências registadas.
- Histórico completo de descontos com motivos.
- Filtros por funcionário, data e tipo de dedução.
- *Badges* visuais para diferentes tipos de desconto (injustificado, parcial, etc.).

---

### Experiência do Utilizador

> **Figura 9:** Notificações *toast* ao criar funcionário — 4 mensagens de sucesso.
> *(Print: capturar o ecrã imediatamente após clicar em "Create" num novo Employee.)*

#### Notificações *Toast* ao Criar Funcionário

Quando um utilizador de RH cria um novo funcionário, o sistema executa automaticamente um fluxo encadeado que cria múltiplas entidades (Utilizador, Contrato e Banco de Horas). Para dar *feedback* claro de cada operação realizada, foram implementadas notificações individuais em *toast* com ícones e cores distintos. As 4 notificações enviadas são:

1. Utilizador criado (e-mail, papel, estado com obrigação de troca de senha).
2. Contrato criado (tipo, salário, data de início).
3. Banco de horas criado (saldo inicial, data de acumulação).
4. Notificação consolidada final com marcadores de sucesso.

> **Figura 10:** Diálogo de confirmação de ações destrutivas com avisos visuais.
> *(Print: clicar no botão "Delete" em qualquer registo para abrir o modal.)*

#### Diálogos de Confirmação de Ações Destrutivas

Operações críticas como eliminar um funcionário ou encerrar um contrato são irreversíveis e podem ter impacto significativo nos dados. O sistema implementa diálogos de confirmação antes de qualquer ação destrutiva, apresentando claramente o que será eliminado e pedindo confirmação explícita. Os avisos visuais (cores de alerta, ícones) tornam inequívoco que se trata de uma ação importante, protegendo contra erros acidentais.

> **Figura 11:** *Badges* visuais — indicadores de funções e estados de contrato.
> *(Print: ver a lista em `/admin/users` ou `/admin/contracts`.)*

#### *Badges* Visuais e Indicadores de Estado

As *badges* (pequenos rótulos coloridos) permitem identificar rapidamente o papel de cada utilizador e o estado de cada contrato, sem necessidade de ler texto em tabelas extensas:

- Indicadores de função (*role*): Admin, HR, Employee com cores distintas.
- Estados de contrato: Ativo (verde), Encerrado (cinzento), Suspenso (laranja).
- Indicadores de estado de licenças e férias.

#### Localização em PT-PT

- Rótulos e campos dos formulários em português.
- Mensagens de validação localizadas.
- Colunas de tabelas traduzidas.
- Datas e números formatados para PT-PT.

#### Interface Responsiva

- Suporte completo a dispositivos móveis.
- *Layout* adaptativo para *tablets* e computador.
- Barra lateral colapsível em computador, libertando 15–20% de espaço horizontal.
- Navegação otimizada para múltiplos tamanhos de ecrã.

---

### Melhorias e Otimizações

#### Padronização de Senhas

- Definição de senha padrão para novos utilizadores.
- Obrigatoriedade de alteração no primeiro acesso.
- Reforço de políticas de segurança.

#### Isolamento de Dados do Funcionário

- Proteção de privacidade e conformidade com a LGPD.
- Dados pessoais acessíveis apenas a utilizadores autorizados.
- Registos de acesso a dados sensíveis.

#### Ações Condicionadas

- Botões e ações visíveis apenas para os perfis autorizados.
- Desativação contextual de funcionalidades.
- Guia visual das permissões do utilizador.

#### Gestão de Ativação de Utilizadores

- Campo `is_active` na tabela `users` para controlo de acesso por estado.
- *Middleware* `CheckActiveUser` desautentica automaticamente utilizadores desativados em cada pedido.
- Redirecionamento com notificação quando a conta está inativa.

#### Otimização de Desempenho

- Índices compostos adicionados nas tabelas `vacations`, `contracts`, `payrolls` e `leaves_and_absences` para colunas frequentemente filtradas.
- Resolução do problema N+1 no `PayrollResource` com *eager loading* da relação `employee`.

#### Validações de Formulários

- **`ContractForm`:** limites mínimos e máximos para campos de salário e horas.
- **`AttendanceLogForm`:** validação de ordem cronológica nos campos de data/hora.
- **`VacationForm`:** validação `afterOrEqual`, verificação de saldo disponível e prevenção de sobreposição de períodos.
- **`PayrollResource`:** proteção contra criação de *payroll* em meses futuros.
- **`LeaveAndAbsenceForm`:** validação de sobreposição de datas equivalente ao `VacationForm`.
- **`EmployeeActionsWidget` e `EmployeeCalendar`:** validação cruzada de sobreposição — ao submeter um pedido de férias ou licença, o sistema verifica se existe qualquer férias ou licença com estado `pending` ou `approved` que partilhe pelo menos um dia com o período solicitado; em caso de conflito, o pedido é bloqueado com notificação de erro e o modal permanece aberto para correção.

#### *Relation Managers* no EmployeeResource

- **`ContractsRelationManager`:** lista todos os contratos do funcionário (tipo, designação, salário, estado, datas).
- **`VacationsRelationManager`:** lista férias por ano com filtro por estado.
- **`LeavesRelationManager`:** lista licenças e afastamentos com tipo, período, estado de aprovação e motivo de rejeição.
- **`AbsencesRelationManager`:** lista faltas registadas com data, tipo de dedução (injustificada, parcial) e horas descontadas.
- **`HourBankMovementsRelationManager`:** histórico completo do banco de horas com formato legível (Xh Ym).

#### Boas Práticas de Engenharia

- Código limpo e bem estruturado.
- Padrões consistentes em toda a *codebase*.
- Convenções de nomenclatura (*naming conventions*) claras e significativas.
- Separação de responsabilidades clara entre camadas.

---

### Validação de E-mail e Automação de Criação

> **Figura 12:** Formulário de criação de funcionário com validação de e-mail.
> *(Print: aceder a `/admin/employees/create` e introduzir um e-mail inválido.)*

O formulário de criação de funcionário inclui um campo de e-mail que valida em tempo real se o domínio é válido (por exemplo, rejeita `utilizador@empresa` mas aceita `utilizador@empresa.com`). A validação visual (com mensagens de erro claras) é apresentada imediatamente, evitando que o utilizador submeta o formulário com dados inválidos. Isto é particularmente importante porque e-mails inválidos comprometem toda a comunicação automática do sistema e a criação do utilizador associado.

#### Validação Rigorosa de E-mail

A regra personalizada `ValidEmailDomain` aplica as seguintes regras:

- **Rejeita:** `teste@teste` (sem TLD válido).
- **Aceita:** `utilizador@empresa.com` (TLD mínimo de 2 caracteres).
- **Expressão regular:** `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/`
- Aplicada em `EmployeeResource` e `UserResource` nos formulários de criação e edição.

#### Sistema Automático de Gatilhos (*Observer Pattern*)

O `EmployeeObserver` interceta a criação de um `Employee` e automaticamente cria:

- **User** — com e-mail e nome do funcionário (papel: *employee*, senha padrão do `.env`, `must_change_password: true`).
- **Contract** — tipo indefinido, salário da designação, estado ativo.
- **HourBank** — saldo inicial de 0 horas, `accrual_date` igual à data de contratação.

O *observer* armazena dados em *cache* durante o ciclo de eventos e recupera-os após conclusão, evitando múltiplas *queries* e permitindo notificações granulares.

#### Proteção de Metadados

- `email_verified_at` no `UserResource` é sempre preenchido automaticamente com a data atual.
- `created_at` e `updated_at` nunca são editáveis em formulários.
- Campos de metadados visíveis em tabelas apenas como *toggleable* (`isToggledHiddenByDefault: true`).
- `ActivityLogResource` mostra `created_at` desativado (*read-only*).

---

### Recursos Filament Implementados

> **Figura 13:** *EmployeeResource* — lista com tabela, filtros e ações.
> *(Print: aceder a `/admin/employees`.)*

O `EmployeeResource` apresenta todos os funcionários numa tabela interativa com capacidades avançadas: filtros permitem procurar por departamento, designação ou estado de contrato; a pesquisa permite localizar rapidamente um funcionário pelo nome ou e-mail; as ações em cada linha (editar, ver detalhes, eliminar) estão sempre acessíveis. O isolamento de dados garante que utilizadores de RH apenas veem funcionários do seu departamento, enquanto o Admin vê toda a organização.

> **Figura 14:** *ContractResource* com ação de *download* de PDF de contrato.
> *(Print: aceder a `/admin/contracts` e mostrar o botão de ação de PDF.)*

Cada contrato pode ser visualizado em lista e descarregado como PDF com toda a informação formatada profissionalmente. Esta funcionalidade é essencial para fins legais e administrativos — permite ao RH manter cópias arquivadas dos contratos e aos funcionários aceder aos seus documentos sem necessidade de contactar o departamento.

> **Figura 15:** *ActivityLogResource* — visualização de histórico de auditoria com filtros.
> *(Print: aceder a `/admin/activity-logs`.)*

O `ActivityLogResource` regista todas as alterações importantes no sistema (criação de utilizadores, edição de salários, eliminação de registos). Os filtros permitem procurar por tipo de ação, utilizador ou data, facilitando investigações e auditorias internas.

> **Figura 16:** *UnitsTree* — Organograma hierárquico de unidades organizacionais com NestedSet.
> *(Print: aceder a `/admin/units-tree`.)*

#### Organograma de Unidades (*UnitsTree*)

A página `UnitsTree` disponibiliza uma vista em árvore da estrutura organizacional da empresa, implementada com o *package* `wsmallnews/filament-nestedset` (NestedSet v2.2). A interface permite:

- Visualizar o organograma completo com todos os níveis hierárquicos (*direction*, *management*, *department*, *section*).
- Criar, editar e eliminar unidades diretamente na árvore, sem sair da página.
- Reordenar unidades por *drag-and-drop*, com atualização automática dos nós `_lft`/`_rgt` do NestedSet na base de dados.
- Visualizar os detalhes de cada unidade (nome, tipo, gestor responsável, estado ativo) num painel lateral.

A migração correspondente adiciona as colunas `_lft`, `_rgt` e `parent_id` à tabela `units`, garantindo compatibilidade total com o modelo NestedSet e preservando a hierarquia existente.

#### Ações de Sincronização de Utilizadores

A página de listagem de funcionários (`ListEmployees`) inclui duas ações administrativas de sincronização acessíveis no cabeçalho:

- **Employees → Users:** cria contas de utilizador para todos os funcionários que ainda não possuem uma, atribuindo senha definida no .env e obrigando a troca no primeiro acesso.
- **Users → Employees:** liga utilizadores existentes a registos de funcionário por correspondência de endereço de e-mail, resolvendo inconsistências de migração de dados.

#### Lista Completa de *Resources* (16)

| *Resource* | Modelo |
|---|---|
| `EmployeeResource` | `Employee` |
| `ContractResource` | `Contract` |
| `PayrollResource` | `Payroll` |
| `UnitResource` | `Unit` (Unidades Organizacionais) |
| `DesignationResource` | `Designation` |
| `UserResource` | `User` |
| `RoleResource` | `Role` (Papéis via Filament Shield) |
| `AttendanceLogResource` | `AttendanceLog` |
| `LeaveAndAbsenceResource` | `LeaveAndAbsence` (Licenças e Afastamentos) |
| `VacationResource` | `Vacation` (Férias) |
| `HourBankResource` | `HourBank` |
| `AbsenceResource` | `Absence` (Auditoria de Descontos) |
| `StateResource` | `State` |
| `CityResource` | `City` |
| `CountryResource` | `Country` |
| `ActivityLogResource` | `ActivityLog` (Auditoria via Spatie) |

Cada *resource* inclui:

- Formulários com validação adequada e regras de negócio.
- Tabelas com ordenação, pesquisa e filtros inteligentes.
- Ações personalizadas por função (*create*, *edit*, *delete*, *view*).
- Isolamento de dados por RBAC via *Policies* Eloquent.
- Auditoria automática de alterações via Spatie Activity Log.

---

### Automatização com *Observers* e *Listeners*

#### *Listener* Principal

**`AuthenticationActivityLogger`** — regista eventos de autenticação (*login*/*logout*) e atividades críticas de utilizadores.

#### *Observers* (6)

| *Observer* | Função |
|---|---|
| `EmployeeObserver` | Cria automaticamente o utilizador, contrato inicial e banco de horas ao registar um novo funcionário. |
| `ContractObserver` | Quando um contrato é criado ou atualizado, sincroniza automaticamente a `designation_id` do `Employee` com base no contrato ativo. |
| `AttendanceLogObserver` | Automatiza cálculos de tempos e delega verificações de atrasos/faltas para o `DeductHourBankService`. |
| `AbsenceObserver` | Monitoriza a criação, atualização e eliminação de ausências, despoletando o recálculo automático do saldo do Banco de Horas para o período correspondente. |
| `LeaveAndAbsenceObserver` | Gere a remoção de ausências automáticas ou criação de deduções por licenças não remuneradas aquando da aprovação de um pedido. |
| `VacationObserver` | Ao atualizar o estado de uma férias para *approved* ou *rejected*, envia automaticamente uma notificação ao funcionário via `RequestStatusNotification`, informando-o do resultado do seu pedido. |

> **Figura 17:** Interface do Banco de Horas com *Relation Manager* de movimentos detalhando cada transação.
> *(Print: aceder a `/admin/hour-banks/{id}/edit` e mostrar a relação de movimentos.)*

#### *Activity Logging* com Spatie

Todos os modelos críticos utilizam a *trait* `LogsActivity` do Spatie para rastreio automático: `Employee`, `User`, `Contract`, `Vacation`, `LeaveAndAbsence`, `AttendanceLog`, `HourBank`, `Absence`, `Unit`, entre outros.

Cada alteração (*create*, *update*, *delete*) é automaticamente registada na tabela `activity_log` com:

- Utilizador que efetuou a alteração (`causer_id`)
- Modelo e ID do objeto alterado
- Evento (*created*, *updated*, *deleted*)
- Valores anteriores vs. novos (`attribute_changes`)
- *Timestamp* da operação

---

## Suíte de Testes Automatizados

A aplicação implementa uma suíte abrangente de testes automatizados usando Pest v4 para validar funcionalidades críticas e garantir a integridade da lógica de negócio.

### Testes Implementados (29 ficheiros)

#### Testes de *Feature*

1. **`HourBankTest`** — valida o cálculo de saldos de banco de horas: criação automática ao criar funcionário, cálculo de horas extras, dedução por ausências injustificadas e transporte de saldos entre meses.

2. **`EmployeePolicyTest`** — testa políticas de autorização: Admin pode ver/editar todos os funcionários; HR vê apenas os do seu departamento; Employee não pode ver dados de terceiros; apenas Admin pode eliminar funcionários.

3. **`EmployeeCreationTest`** — valida o fluxo de criação de funcionário: validação de formato de e-mail, criação automática de User/Contract/HourBank, envio de 3 notificações de sucesso e obrigação de troca de senha no primeiro *login*.

4. **`AttendanceProcessingTest`** — testa a lógica de processamento de presença: cálculo de minutos trabalhados, deteção de atrasos (>15 min de tolerância), atrasos >1h classificados como falta injustificada, deteção de saídas antecipadas e remoção automática de penalizações se corrigidas.

5. **`VacationAndLeaveTest`** — testa a gestão de férias e licenças: criação de pedidos, dedução e restauração de dias, tipos de licença e prevenção de aprovação de pedidos próprios (via *Policy*).

6. **`PayrollProcessingTest`** — valida a geração de salários: criação de recibos, cálculo de bónus de horas extras, aplicação de deduções, prevenção de *payroll* duplicado no mesmo mês e rastreio de estados de pagamento.

7. **`ContractPdfTest`** — testa a geração de PDFs de contrato: proteção por autenticação, proteção por *policies*, *download* correto em formato PDF e *bulk download* de múltiplos contratos.

8. **`EmployeeNotificationTest`** — testa o sistema de notificações: 3 notificações enviadas ao criar funcionário, títulos corretos e armazenamento em base de dados para a UI.

9. **`DeductHourBankServiceTest`** — valida regras complexas de descontos: aplicação de tolerâncias (15 min), conversão de atraso em falta total (>1h) e regra de 3 atrasos consecutivos.

10. **`EmployeeOnboardingServiceTest`** — garante atomicidade na criação: sucesso cria Employee/User/Contract/HourBank numa transação; falha em qualquer passo reverte toda a operação.

11. **`GeneratePayrollServiceTest`** — valida precisão financeira: cálculo de taxas horárias, soma correta de movimentos por mês e prevenção de duplicados.

12. **`AttendanceLogObserverTest`** — testa a reatividade do sistema: atualização de saldo ao criar/editar picagens e reversão de movimentos ao eliminar picagens.

13. **`DashboardWidgetsTest`** — valida a visibilidade de dados: *widgets* mostram valores corretos para Admin vs. Employee, respeitando permissões de visualização.

14. **`AttendanceCalculationServiceTest`** — valida o serviço de cálculo de presenças: minutos trabalhados, deteção de atrasos acima da tolerância configurável, saídas antecipadas e classificação de atraso grave como falta injustificada.

15. **`VacationBalanceServiceTest`** — valida o serviço de gestão de saldo de férias: débito ao aprovar, restauração ao rejeitar/cancelar, proteção contra *race conditions* (`DB transaction` + `lockForUpdate`) e prevenção de saldo negativo.

16. **`HrConfigTest`** — valida a configuração centralizada em `config/hr.php`: `delay_tolerance_minutes` menor que `full_absence_threshold_minutes`, `consecutive_delays_limit` de pelo menos 2 e tipos de retorno corretos.

17. **`CheckActiveUserTest`** — valida o *middleware* de ativação: utilizadores ativos passam normalmente; utilizadores inativos (`is_active = false`) são desautenticados e redirecionados; utilizadores não autenticados não são afetados.

18. **`TeamManagementTest`** — valida o fluxo de gestão de equipas: responsáveis de unidade acedem à página `ManageTeam` e visualizam os dados de presença e pedidos pendentes dos seus subordinados; utilizadores sem unidades geridas não têm acesso à página.

19. **`UserPushSubscriptionTest`** — testa o registo de subscrições *Web Push*: criação e associação de `PushSubscription` a utilizadores para envio de notificações *push* pelo servidor.

20. **`VacationBalanceTest`** — testa cenários adicionais de saldo de férias, complementando a cobertura do `VacationBalanceServiceTest` com validações de fluxo ao nível do recurso.

21. **`LeaveApprovalRestoresBalanceTest`** — verifica que a aprovação de uma licença remove ausências automáticas geradas e restaura o saldo do banco de horas de forma consistente.

22. **`EmployeeWidgetsTest`** — valida os *widgets* do painel do colaborador: `TodayAttendanceWidget`, `EmployeeAbsencesWidget` e `EmployeeActionsWidget` apresentam dados corretos e respeitam o contexto do utilizador autenticado.

23. **`PayrollResourceTest`** — testa o recurso de processamento salarial ao nível do Filament: proteção contra criação de *payroll* em meses futuros e validação de permissões de acesso ao recurso.

24. **`AttendanceCheckInTest`** — valida a página de *check-in* simplificado (`/app/attendance-check-in`): fluxo dos quatro estados (entrada, início de pausa, fim de pausa, saída), bloqueio de acesso sem `employee_id` associado e registo correto de *timestamps*.

25. **`CheckDailyAttendanceTest`** — testa o comando `CheckDailyAttendance` que verifica automaticamente as presenças diárias e regista ausências não justificadas para funcionários sem registo de presença no dia.

#### Testes de *Unit*

- **`DashboardWidgetsTest`** (Unit) — valida componentes isolados dos *widgets* do painel administrativo.
- **`EnsureUtf8EncodingTest`** — valida o *middleware* de codificação UTF-8 para respostas da aplicação.
- **`ExampleTest`** — teste de exemplo base da suíte.

---

### Inventário Completo dos Ficheiros de Teste

**Testes de *Feature* (26 ficheiros):**

- `tests/Feature/AttendanceProcessingTest.php`
- `tests/Feature/ContractPdfTest.php`
- `tests/Feature/EmployeeCreationTest.php`
- `tests/Feature/EmployeeNotificationTest.php`
- `tests/Feature/EmployeePolicyTest.php`
- `tests/Feature/ExampleTest.php`
- `tests/Feature/HourBankTest.php`
- `tests/Feature/PayrollProcessingTest.php`
- `tests/Feature/TeamManagementTest.php`
- `tests/Feature/UserPushSubscriptionTest.php`
- `tests/Feature/VacationAndLeaveTest.php`
- `tests/Feature/VacationBalanceTest.php`
- `tests/Feature/CheckActiveUserTest.php`
- `tests/Feature/App/EmployeeWidgetsTest.php`
- `tests/Feature/Console/Commands/CheckDailyAttendanceTest.php`
- `tests/Feature/Config/HrConfigTest.php`
- `tests/Feature/Observers/AttendanceLogObserverTest.php`
- `tests/Feature/Pages/AttendanceCheckInTest.php`
- `tests/Feature/Resources/PayrollResourceTest.php`
- `tests/Feature/Services/DeductHourBankServiceTest.php`
- `tests/Feature/Services/EmployeeOnboardingServiceTest.php`
- `tests/Feature/Services/LeaveApprovalRestoresBalanceTest.php`
- `tests/Feature/Services/Attendance/AttendanceCalculationServiceTest.php`
- `tests/Feature/Services/Payroll/GeneratePayrollServiceTest.php`
- `tests/Feature/Services/Vacation/VacationBalanceServiceTest.php`
- `tests/Feature/Widgets/DashboardWidgetsTest.php`

**Testes de *Unit* (3 ficheiros):**

- `tests/Unit/ExampleTest.php`
- `tests/Unit/Dashboard/DashboardWidgetsTest.php`
- `tests/Unit/Http/Middleware/EnsureUtf8EncodingTest.php`

Este inventário confirma a existência de 29 ficheiros de teste, distribuídos entre validação de comportamento ponta a ponta (*Feature*) e validação de componentes isolados (*Unit*).

---

### Cobertura Funcional por Domínio

A suíte cobre os domínios mais sensíveis da aplicação com foco em cenários de risco operacional real:

- ***Onboarding* de colaboradores:** criação encadeada de Employee/User/Contract/HourBank, com validação de *rollback* transacional em caso de falha.
- **Gestão de presença e assiduidade:** picagens, atrasos, saídas antecipadas, tolerâncias, faltas e interação com banco de horas.
- **Férias e licenças:** aprovação, rejeição, reposição de saldo, prevenção de conflitos de aprovação própria e prevenção de sobreposição de períodos.
- **Processamento salarial:** cálculo de base, extras, deduções, prevenção de duplicação mensal e atualização de estados de pagamento.
- **Segurança e autorização:** *policies* por perfil, proteção de rotas, isolamento de dados e validações de acesso a recursos.
- **Auditoria e *observers*:** efeitos colaterais automáticos de eventos de domínio e consistência de movimentos.
- **Notificações e subscrições *push*:** registo de endpoints e envio de alertas em tempo real ao utilizador.
- **Gestão de equipa:** acesso restrito à página `ManageTeam` para responsáveis de unidade e visualização de dados de subordinados.

---

### Critérios de Fiabilidade e Reprodutibilidade

Os testes foram desenhados para execução frequente, com foco em previsibilidade:

- Uso de `RefreshDatabase` para isolamento entre cenários.
- Execução sobre SQLite em memória para rapidez e limpeza de contexto.
- *Factories* para construção consistente de dados de teste.
- Validação periódica em execução completa e em modo compacto.

Foram também tratadas fontes de flutuação relacionadas com colisões de campos `UNIQUE` em *factories*, reduzindo falsos negativos e melhorando a estabilidade estatística da suíte. Este aspeto é especialmente relevante em ambientes de integração contínua, onde o determinismo dos testes influencia diretamente a confiança no *pipeline* e a velocidade de entrega.

---

### Estado Atual da Qualidade — 01/06/2026

A suíte foi executada de forma completa com `php artisan test` e também no modo compacto (`php artisan test --compact`), mantendo consistência nos resultados e confirmando estabilidade entre testes de *Unit* e *Feature*.

| Métrica | Valor |
|---|---|
| Ficheiros de teste | 29 |
| Falhas no estado final | 0 |
| Novos ficheiros desde 15/05 | 4 (`AttendanceCalculationServiceTest`, `VacationBalanceServiceTest`, `HrConfigTest`, `CheckActiveUserTest`) |

Para além da cobertura funcional direta, os testes garantem propriedades arquiteturais importantes:

- **Integridade transacional** em fluxos de *onboarding* e processamento encadeado.
- **Consistência de regras de negócio** em férias, ausências, banco de horas e processamento salarial.
- **Segurança de acesso** com validação de *policies* em cenários de permissões distintas.
- **Fiabilidade de *observers* e serviços** em operações de criação, atualização e reversão de estado.

Do ponto de vista prático, esta maturidade de testes traduz-se em três ganhos imediatos:

1. Deteção precoce de regressões críticas sem dependência de validação manual.
2. Maior segurança para refatorações de serviços centrais (*Payroll*, *HourBank*, *Onboarding*).
3. Menor custo de manutenção, reduzindo o tempo gasto com falhas intermitentes não funcionais.

---

## Conclusão

### Dificuldades Encontradas e Soluções Adotadas

**Isolamento de dados complexo** — um dos maiores obstáculos foi implementar o isolamento de dados por função de utilizador de forma robusta. No sistema RBAC, não era suficiente bloquear o acesso a recursos e páginas — era necessário garantir que cada utilizador só consultasse os seus próprios dados ao nível da base de dados. A solução foi implementar *Policies* Eloquent em cada modelo e *Query Scopes*, garantindo que as *queries* já vinham filtradas na origem.

**Automatização com *Observers*** — quando um contrato é criado, o sistema precisa de sincronizar a designação (*designation*) do funcionário para manter consistência. Esta lógica foi implementada com *Observers*, assegurando que alterações no contrato se refletem automaticamente no modelo `Employee`. O `VacationObserver` complementa este padrão ao garantir que o funcionário recebe notificação automática sempre que o estado do seu pedido de férias é atualizado.

**Integração de auditoria** — implementar o Spatie Activity Log em múltiplos modelos sem criar *overhead* de desempenho foi desafiante. A solução foi utilizar a *trait* `LogsActivity` e configurar os eventos adequados em cada modelo crítico.

**Validação do Banco de Horas** — o sistema que valida licenças antes de descontar horas poderia originar problemas de N+1. A solução foi otimizar com *eager loading* correto das relações. O `HourBankService` centraliza toda a lógica incremental de movimentos, prevenindo dupla contabilização ao verificar a existência de `Absence` para o mesmo dia antes de calcular défices de presenças.

---

### Pontos Fortes da Aplicação

- **Gestão salarial automatizada (*Payroll*):** motor de cálculo que considera o salário base contratual, horas extras do banco de horas e deduções, gerando recibos de vencimento de forma automática.
- **Gestão inteligente de férias:** sistema de controlo de saldo que debita automaticamente dias ao aprovar férias e restaura o saldo em caso de cancelamento ou rejeição, prevenindo erros manuais.
- **Arquitetura bem estruturada:** separação clara de responsabilidades, com isolamento de dados ao nível do modelo via *Policies*. Os *Eloquent Scopes* garantem segurança sem depender exclusivamente do *frontend*.
- **Autenticação robusta:** integração com Filament Breezy e *Passkeys* oferece camadas de segurança modernas.
- **Automatização inteligente:** o padrão *Observer* (6 *observers*) automatiza a sincronização de dados entre modelos, incluindo notificações automáticas ao funcionário quando o estado dos seus pedidos é atualizado.
- **Auditoria completa:** Spatie Activity Log fornece rastreio automático de todas as operações sensíveis.
- **RBAC dinâmico:** Filament Shield permite gerir papéis e permissões de forma granular e flexível.
- **Testes com cobertura robusta:** Pest v4 com 29 ficheiros de teste cobrindo funcionalidades críticas. Cada teste valida comportamentos essenciais: cálculo de horas extras, processamento de ausências, gestão de férias, geração de *payroll*, *policies* de autorização e ativação de utilizadores.
- **Notificações em tempo real:** integração com *Web Push* via `PushSubscription` e `RequestStatusNotification`, alertando os colaboradores de alterações de estado sem necessidade de recarregar a página.

---

### Pontos a Melhorar

- **Integrações externas:** falta de ligação com sistemas bancários externos para automatização real de pagamentos e exportação de ficheiros SEPA.
- **Testes de carga:** não foi validado com volumes grandes de dados (milhares de funcionários, múltiplos utilizadores simultâneos).
- **Documentação técnica:** centralizada na pasta `/docs` com manuais técnicos e de utilizador exaustivos para cada secção.
- ***Edge cases* em operações críticas:** refinar a gestão de transações em fluxos encadeados (por exemplo, criação de funcionário) para garantir atomicidade absoluta em cenários de falha de base de dados.
- **Cobertura de testes unitários:** expandir a cobertura para cenários de borda em cálculos de horas extras e fusos horários.
- **Evolução da qualidade de testes:** apesar de a suíte já ser robusta em termos funcionais, é recomendável reforçar continuamente testes de resiliência (por exemplo, cenários com dados extremos e colisões de unicidade em massa), além de consolidar práticas de determinismo em *factories* e *seeders* para manter previsibilidade em *pipelines* de integração contínua.

---

### O que Aprendi com a Aplicação

- **Pensar em arquitetura desde o início:** decisões de desenho como *Policies* e *Observers* tiveram impacto em toda a aplicação. A segurança deve estar no ADN da arquitetura, não ser um complemento tardio.
- **Importância dos padrões de *design*:** *Observer*, *Policy*, *Scope* — padrões estabelecidos resolvem problemas complexos de forma elegante.
- **Auditoria desde o primeiro dia:** integrar o Spatie Activity Log desde o início é muito mais simples do que fazê-lo posteriormente. A conformidade regulatória não é opcional.
- **RBAC é complexo:** implementar corretamente em três camadas (*routes*, *policies*, *queries*) é essencial para segurança real.
- **Testes como fonte de confiança:** o Pest proporcionou confiança ao refatorar código sem receio de regressões.
- **Integração com *frameworks* maduros:** Filament, Filament Shield, Filament Breezy, Spatie Activity Log — escolher as ferramentas certas acelera o desenvolvimento e melhora a qualidade.

---

### Importância para o Meu Futuro Profissional

Esta aplicação demonstra competências críticas procuradas no mercado:

- Capacidade de desenhar arquiteturas seguras e escaláveis, não apenas escrever código funcional.
- Implementação de RBAC robusto, um requisito em qualquer sistema com múltiplos utilizadores.
- Compreensão da importância de auditoria e conformidade regulatória desde a fase de *design*.
- Trabalho com ferramentas profissionais reais (Laravel v13, Filament v5, pacotes Spatie) e aplicação correta das suas capacidades.
- Valorização de *code quality*, testes e boas práticas desde o início, não como *afterthought*.

---

### Reflexão Final

A Aplicação TeamCore não é uma aplicação perfeita — tem limitações e áreas a melhorar. No entanto, é uma solução robusta e funcional que resolve problemas reais de gestão de RH.

Mais importante, o processo de desenvolvimento ensinou-me a pensar e a agir como um engenheiro de *software*. Não se trata apenas de escrever código que funciona — trata-se de desenhar soluções que:

- ✅ Sejam seguras desde a arquitetura
- ✅ Sejam auditáveis para conformidade
- ✅ Sejam escaláveis para crescimento
- ✅ Sejam fáceis de manter por outros
- ✅ Sejam testáveis para transmitir confiança

Para as próximas oportunidades profissionais, levarei a compreensão de que qualidade técnica, segurança e experiência do utilizador não são extras — são componentes essenciais de qualquer solução profissional.

---

## Bibliografia

### *Frameworks* e Linguagens

- Laravel LLC. (2025). *Laravel – The PHP framework for web artisans*. Versão 13.0. Acedido em 16 de abril de 2026, em https://laravel.com
- Filament. (2025). *Filament – Accelerated Laravel development*. Versão 5.5+. Acedido em 16 de abril de 2026, em https://filamentphp.com
- The PHP Group. (2025). *PHP: Hypertext preprocessor*. Versão 8.4. Acedido em 16 de abril de 2026, em https://www.php.net
- Oracle Corporation. (2025). *MySQL 8.0 Reference Manual*. Acedido em 16 de abril de 2026, em https://dev.mysql.com/doc

### Pacotes e Plugins

- Salleh, B. (2025). *Filament Shield – Role & Permission Manager for Filament*. Acedido em 16 de abril de 2026, em https://github.com/bezhanSalleh/filament-shield
- Greco, J. (2025). *Filament Breezy – Authentication & Passkeys for Filament*. Versão 3.2+. Acedido em 16 de abril de 2026, em https://github.com/jeffgreco13/filament-breezy
- Spatie. (2025). *Laravel Activity Log*. Versão 5.0. Acedido em 16 de abril de 2026, em https://spatie.be/docs/laravel-activitylog/
- Van Dyck, B. (2025). *Laravel DomPDF – PDF generation for Laravel*. Versão 3.1+. Acedido em 16 de abril de 2026, em https://github.com/barryvdh/laravel-dompdf
- Rotter, P. (2025). *Filament Excel – Excel export for Filament*. Versão 3.6+. Acedido em 16 de abril de 2026, em https://github.com/pxlrbt/filament-excel
- Wsmallnews. (2025). *Filament Nestedset – Hierarchical tree management for Filament*. Versão 2.2. Acedido em 16 de abril de 2026, em https://github.com/wsmallnews/filament-nestedset
- Azgasim. (2025). *Filament Unsaved Changes Modal – Unsaved data alert for Filament*. Versão 1.0+. Acedido em 16 de abril de 2026, em https://github.com/azgasim/filament-unsaved-changes-modal
- Laravel Notification Channels. (2025). *WebPush – Web Push notifications for Laravel*. Versão 10.5+. Acedido em 16 de abril de 2026, em https://github.com/laravel-notification-channels/webpush
- Predis Authors. (2025). *Predis – Redis client for PHP*. Versão 3.4+. Acedido em 16 de abril de 2026, em https://github.com/predis/predis

### Ferramentas de Desenvolvimento

- Pest. (2025). *Pest – PHP Testing Framework*. Versão 4.5. Acedido em 16 de abril de 2026, em https://pestphp.com
- Laravel Pint Authors. (2025). *Laravel Pint – PHP code style fixer*. Versão 1.27+. Acedido em 16 de abril de 2026, em https://laravel.com/docs/pint
- Composer Authors. (2025). *Composer – Dependency manager for PHP*. Acedido em 16 de abril de 2026, em https://getcomposer.org
- GitHub, Inc. (2025). *GitHub – Where the world builds software*. Acedido em 16 de abril de 2026, em https://github.com

---

## Anexos
