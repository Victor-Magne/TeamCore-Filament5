


Curso Profissional de Técnico de Informática-Sistemas 
Código de Referência do CNQ - 481039 
Ciclo de Formação  2023/2026 
Ano letivo   2025/2026

Prova de Aptidão Profissional
Relatório
Aplicação TeamCore - Uma nova gestão

Autor:
Victor Gabriel Cristino Gomes	N.º 21
Orientador/a(es):
	Zélia Capitão

Data 
22/04/2026
Data de Versão Anterior: 16/04/2026
Agradecimentos a:
Jorge Lafuente — tutor de estágio ao longo do 11.º e do 12.º ano, e uma das pessoas que mais influenciou o rumo desta aplicação. Foi quem me apresentou o Laravel e o Filament, despertando em mim o interesse por estas tecnologias, e quem sugeriu funcionalidades concretas que acabaram por enriquecer significativamente a aplicação. O seu acompanhamento e partilha de experiência profissional foram determinantes para o resultado final.
Zélia Capitão — orientadora da aplicação, pelo acompanhamento contínuo e disponibilidade ao longo de todo o processo, e pelas orientações que permitiram manter o trabalho no rumo certo.
Ana Paula Azevedo — docente de Sistemas Informáticos e Aplicações Web, pelos conhecimentos transmitidos ao longo do curso, que constituíram a base técnica essencial para a concretização desta aplicação.
Willian Washington — grande amigo que esteve presente durante todo este percurso. Pelo apoio na área de qualidade e testes, pela disponibilidade em rever e validar funcionalidades, e acima de tudo pela amizade genuína e pelo incentivo constante nos momentos mais desafiantes do desenvolvimento.
A todos, o meu sincero obrigado.








Índice

Acrónimos / Abreviaturas / Siglas	5
Resumo	6
Introdução	7
Enquadramento Teórico	7
Fundamentação do Tema	7
Motivação e Contexto	8
Objetivos Principais	8
Abordagem e Metodologia	8
Desenvolvimento da Aplicação	9
Metodologia e Ferramentas	9
Arquitetura de Dados Relacional	10
Processo de Desenvolvimento	11
Principais Implementações	12
Interface Administrativa Unificada e RBAC	14
Características da Interface	15
Controlo de Acesso e Segurança	15
Gestão de Horas e Banco de Horas	16
Experiência do Utilizador (UX)	18
Melhorias e Otimizações	20
Validação de E-mail e Automação de Criação	22
Recursos Filament Implementados	24
Listeners e Automação	25
Conclusão	26
Dificuldades Encontradas e Soluções Adotadas	26
Pontos Fortes da Aplicação	27
Pontos a Melhorar	28
O que Aprendi com a Aplicação	29
Importância para o Meu Futuro Profissional	30
Reflexão Final	31
Bibliografia	32
Anexos	33



Índice de figuras

Figura 1: Diagrama de Entidades e Relações (ER) - Estrutura da base de dados relacional	10
Figura 2: Dashboard Admin com Widgets de Estatísticas (contratos, funcionários, presença)	12
Figura 3: Gestão de Funcionários com tabela de dados e filtros de acesso	13
Figura 4: Visualização de Dados Pessoais e Banco de Horas com acesso restrito	14
Figura 5: Diagrama RBAC - Hierarquia de Papeis (Admin, HR, Employee) e Permissões por Funcionalidade	15
Figura 6: Registo de Presença (Attendance) com Campos de Entrada/Saída e Pausas	16
Figura 7: Visualização do Banco de Horas (Hourbank) com Saldo Acumulado e Histórico	17
Figura 8: Notificações Toast ao Criar Funcionário - 4 Mensagens de Sucesso (Utilizador, Contrato, Banco de Horas)	18
Figura 9: Diálogo de Confirmação de Ações Destrutivas com Avisos Visuais	19
Figura 10: Badges Visuais - Indicadores de Funções (Admin/HR/Employee) e Estados de Contrato (Ativo/Encerrado/Suspenso)	19
Figura 11: Formulário de Criação de Funcionário (EmployeeResource) com Validação de E-mail	22
Figura 12: Resource EmployeeResource - Lista com Tabela, Filtros e Ações	24
Figura 13: Resource ContractResource com Ação de Download PDF de Contrato	25
Figura 14: Resource ActivityLogResource - Visualização de Histórico de Auditoria com Filtros	26
Figura 15: Página de Check-in Simplificado com botões de ação dinâmica e histórico diário	16
Figura 16: Dashboard do Funcionário com widgets de resumo e ações rápidas	13




Acrónimos / Abreviaturas / Siglas
Sigla
Descrição
CRUD
Create, Read, Update, Delete
CSRF
Cross-Site Request Forgery
HR
Human Resources
MVP
Minimal Viable Product
ORM
Object-Relational Mapping
PAP
Prova de Aptidão Profissional
PDF
Portable Document Format
PME
Pequena e Média Empresa
PT-PT
Português de Portugal
RBAC
Role-Based Access Control
RH
Recursos Humanos
TLD
Top-Level Domain
UI
User Interface
UX
User Experience



Resumo
O presente relatório de PAP descreve o desenvolvimento da Aplicação TeamCore, uma aplicação de gestão de RH concebida para simplificar os processos desse setor. A aplicação foi motivada pela necessidade de uma ferramenta de RH que fosse simples, fluida e segura de usar, contrastando com a complexidade de muitos sistemas existentes.

A Aplicação TeamCore foi desenvolvida com o objetivo de alcançar uma gestão abrangente de dados de funcionários, cargos, contratos e férias, promover a automação de processos para minimizar a margem de erro, e fornecer auditoria completa de todas as operações. A qualidade técnica, focada na segurança, usabilidade e conformidade regulatória, foi um pilar central do desenvolvimento.

A metodologia de trabalho incluiu o levantamento detalhado de requisitos, a modelação da base de dados relacional, e o desenvolvimento técnico utilizando a framework Laravel v13 com Filament v5 para o backend e frontend. O processo foi complementado por validação rigorosa de funcionalidades e testes automatizados.

Nota sobre o Estado da Aplicação: No momento da redação deste relatório (22 de Abril de 2026), a Aplicação TeamCore encontra-se numa fase de maturidade produção-ready com todas as funcionalidades core completamente implementadas, testadas e validadas. A aplicação inclui: 15 Models com isolamento de dados RBAC; 16 Filament Resources com políticas de autorização; 17 Policies para controlo granular; Sistema de banco de horas com validação automática de licenças; Auditoria completa via Spatie Activity Log; 4 Observers para automação de processos; Autenticação segura via Filament Breezy e Passkeys; e testes automatizados com Pest v4. A aplicação está pronta para utilização em ambiente de produção.


Introdução
Enquadramento Teórico
A Aplicação TeamCore foi desenvolvida no âmbito da Prova de Aptidão Profissional do Curso Profissional de Técnico de Informática de Sistemas, simulando um contexto real de trabalho numa empresa de média dimensão com necessidades concretas de gestão de Recursos Humanos.
A gestão eficaz de Recursos Humanos é uma das funções críticas em qualquer organização moderna. Com a transformação digital, as aplicações de software para sistemas de informação de RH tornaram-se ferramentas essenciais para otimizar processos, reduzir erros administrativos e facilitar a tomada de decisões estratégicas.
A presente aplicação insere-se na área de Desenvolvimento de Sistemas de Informação, abrangendo:
Desenvolvimento Backend: Implementação de lógica de negócio, processamento de dados e integração de bases de dados relacionais.
Desenvolvimento Frontend: Criação de interfaces intuitivas e responsivas que facilitam a interação do utilizador.
Arquitetura de Software: Desenho de sistemas escaláveis, seguros e com fácil manutenção.
Controlo de Acesso: Implementação de mecanismos de autenticação e autorização para proteger dados sensíveis.

Fundamentação do Tema
Esta aplicação foi selecionada por várias razões estratégicas:

Relevância Profissional: O desenvolvimento de sistemas de RH representa uma aplicação prática e imediata dos conhecimentos adquiridos no curso, incluindo programação, bases de dados, segurança e design de interface.
Problema Real: Muitas organizações, especialmente PMEs, enfrentam dificuldades em gerir dados de funcionários de forma centralizada e eficiente. Legacy systems frequentemente apresentam problemas de usabilidade, integração limitada e custos elevados de manutenção.
Oportunidade de Aprendizagem: O escopo da aplicação permite aplicar múltiplas tecnologias atuais no mercado (Laravel, Filament, MySQL, Pest) e consolidar conhecimentos em autenticação, autorização, validação de dados e testes automatizados.
Viabilidade: A aplicação é de amplitude apropriada para ser completado num ciclo de desenvolvimento estruturado, permitindo implementar uma solução funcional com qualidade técnica.


Motivação e Contexto
A Aplicação TeamCore nasce da necessidade de uma ferramenta de Gestão de Recursos Humanos intuitiva e eficiente, capaz de apoiar tanto equipas de gestão como colaboradores nas operações diárias. A solução foi concebida para centralizar a gestão de funcionários, cargos e contratos, automatizar tarefas de registo de tempo e horas extras, e fornecer suporte à decisão através de relatórios e visualizações estruturadas.

Objetivos Principais
Esta aplicação PAP visa desenvolver um sistema de gestão que:
Centralizar dados de RH: Consolidar informação de funcionários, departamentos, designações, contratos e períodos de férias numa base de dados estruturada e facilmente consultável.
Automatizar registos de trabalho: Permitir o registo eficiente de horas trabalhadas, com suporte a pausas/almoços, cálculo automático de horas extras e manutenção atualizada do banco de horas individual.
Implementar controlo de acesso granular: Segregar permissões por função de utilizador (ADMIN, HR, EMPLOYEE), garantindo que cada utilizador acede apenas aos dados e funcionalidades apropriados.
Oferecer experiência de utilizador otimizada: Proporcionar uma interface intuitiva, com notificações contextuais e confirmações para prevenir ações não intencionais.
Garantir a qualidade técnica: Assegurar a aplicação através de testes automatizados, validação de regras de autorização e boas práticas de segurança.

Abordagem e Metodologia
O desenvolvimento foi realizado em ciclos iterativos, adotando:
Tecnologias modernas: Laravel v13 como framework backend, Filament v5 como interface administrativa unificada, PHP v8.3+ e MySQL como base de dados relacional.
Testes automatizados: Desenvolvimento orientado a testes utilizando framework Pest/PHPUnit para validação contínua de funcionalidades.
Validação com profissionais: Consulta com profissionais de RH durante o desenvolvimento para validar requisitos e funcionalidades essenciais.
Boas práticas de engenharia: Isolamento de dados ao nível do modelo através de Policies Eloquent, arquitetura em camadas clara, padrões de código consistentes.


Desenvolvimento da Aplicação
Metodologia e Ferramentas
A Aplicação TeamCore foi desenvolvida com a framework Laravel v13, utilizando Filament v5 como interface administrativa unificada e PHP v8.3 como linguagem de desenvolvimento. A persistência de dados foi implementada em MySQL, com uma arquitetura relacional suportando entidades como Funcionários, Contratos, Departamentos, Designações, Bancos de Horas, Registos de Presença, Pedidos de Licença, Ausências e Logs de Auditoria.

O processo de desenvolvimento adotou uma metodologia iterativa com ciclos bissemanais de análise, implementação, testes e validação. Foi feita a utilização Git para controlo de versão e manutenção de uma suíte de testes automatizados (Pest v4) que executam em cada novo commit, garantindo que regressões não ocorressem durante o desenvolvimento.

Tecnologia
Aplicação
Versão
Laravel
Framework Backend
13.0
Filament
Construção da interface
5.5+
PHP
Linguagem
8.3
MySQL
Base de Dados
8.0
Pest
Framework para teste
4.5
Vite
Bundler Frontend
6.2.4+
Spatie Activity Log
Auditoria de Operações
5.0



Arquitetura de Dados Relacional

 Figura 1: Diagrama de Entidades e Relações (ER) - Estrutura da base de dados relacional

A aplicação suporta as seguintes entidades principais:
Funcionários (Employee): Dados pessoais, contactos, informações profissionais e saldo de férias.
Utilizadores (User): Credenciais de acesso, papeis e permissões (com autenticação via Filament Breezy).
Contratos (Contract): Informações de vínculo laboral (permanent, fixed_term, unfixed_term, service_provision, internship), remuneração e status (active, terminated, on_hold).
Unidades Organizacionais (Unit): Estrutura hierárquica da empresa (direction, department, section) com gestor responsável.
Cargos (Designation): Definição de funções profissionais com níveis (junior, pleno, senior, specialist, lead) e salários base.
Banco de Horas (HourBank): Controlo mensal de horas acumuladas/deficitárias por funcionário.
Registos de Presença (AttendanceLog): Registos diários de entrada/saída/pausas com cálculo automático de minutos totais.
Ausências (Absence): Auditoria de descontos de horas com tipos (unjustified_absence, partial_absence, other).
Férias (Vacation): Gestão de férias anuais com saldo por ano, status de aprovação e dias tomados.
Licenças e Ausências (LeaveAndAbsence): Gestão de licenças justificadas (sick_leave, parental, marriage, bereavement, justified_absence, unjustified) com aprovação workflow.
Payroll: Processamento salarial automático com base em contratos e banco de horas.
Localização: País, Estado e Cidade para preenchimento de dados de funcionários.
Principais Implementações
A aplicação utiliza uma interface administrativa unificada (/admin) onde o acesso a recursos e dados é controlado dinamicamente via funções e permissões geridas pelo Filament Shield. Esta abordagem simplifica a navegação e centraliza a gestão, garantindo o isolamento de dados através de Policies Eloquent.

Figura 2: Dashboard Admin com Widgets de Estatísticas (contratos, funcionários, presença).
(Instrução de print: Aceder a /admin com utilizador Admin)

Figura 3: Gestão de Funcionários com tabela de dados e filtros de acesso.
(Instrução de print: Aceder a /admin/employees)

Figura 4: Visualização de Dados Pessoais e Banco de Horas com acesso restrito.
(Instrução de print: Aceder a /admin/employees/{id})

Principais Áreas de Gestão (Acesso via Roles):

Administração (ADMIN)
Gestão completa de utilizadores, papéis e unidades.
Administração de cargos, benefícios e auditoria global.
Controlo total de configurações da aplicação.

Gestão de RH (HR)
Gestão completa de funcionários e contratos.
Processamento de licenças, faltas e salários (Payroll).
Relatórios estratégicos e análise de produtividade.

Acesso Pessoal (EMPLOYEE)
Visualização de dados pessoais e banco de horas.
Gestão de férias e pedidos de licenças com workflow de aprovação.
Dashboard interativo com widgets detalhados de estatísticas, incluindo gráficos de contratos, densidade de unidades, salários por nível e banco de horas.
O portal do funcionário utiliza um `EmployeeDashboard` personalizado que centraliza informações críticas, como detalhes do contrato ativo, saldo de férias e widgets de ações rápidas, garantindo que o colaborador tenha uma visão 360º da sua situação na empresa.

Figura 16: Dashboard do Funcionário com widgets de resumo e ações rápidas.
(Instrução de print: Aceder a /app com um utilizador que tenha role employee)

Características da Interface Unificada
Isolamento de dados ao nível do modelo através de Policies Eloquent (Scopes).
Componentes UI, recursos e ações visíveis apenas para perfis autorizados.
Menu de navegação dinâmico que se adapta às permissões do utilizador (Filament Navigation).
Middleware robusto para verificação de permissões via Filament Shield.
Navegação e estrutura otimizadas para uma experiência de utilizador fluida e centralizada.

Controlo de Acesso e Segurança

O sistema implementa um modelo RBAC (Role-Based Access Control) com papeis e permissões dinâmicas via Filament Shield.

Papeis (Roles) Dinâmicos:
ADMIN — Acesso completo ao sistema, gestão de utilizadores, unidades, auditoria.
HR — Gestor de recursos humanos, gestão de funcionários, contratos, licenças e férias.
EMPLOYEE — Colaborador, acesso limitado a dados pessoais e pedidos próprios.

Implementação em 3 Camadas:
Middleware de Rotas — Bloqueio de acesso inicial (usando role checks via Shield).
Filament Policies — 17 policies implementadas para controlo granular (UserPolicy, EmployeePolicy, ContractPolicy, etc.).
Eloquent Scopes — Isolamento de dados ao nível da query (ex: Employees só veem seus dados, HR veem do seu departamento).

Recursos Filament com Controlo de Acesso (16 total):
Gestão de Sistema: Users, Roles, Units (Admin)
Gestão de Funcionários: Employees, AttendanceLogs, ActivityLogs (HR/Admin)
Gestão de Organização: Designations, Countries, States, Cities
Gestão de Contratos: Contracts
Gestão de Ausências: Vacations, LeaveAndAbsences
Gestão de Banco de Horas: HourBanks, Absences
Gestão de Salários: Payrolls

Proteções de Segurança:
Isolamento de dados: Employee só vê dados pessoais, HR vê do seu departamento
Auto-aprovação bloqueada: Utilizador não pode aprovar seus próprios pedidos (via Policies)
Soft deletes: Preservação de histórico em todos os modelos críticos
Activity logging: Rastreio completo via Spatie Activity Log (automatic com LogsActivity trait).
Services especializados: Implementação de lógica de negócio complexa em Services (GeneratePayrollService, CalculateExtraHoursService, DeductHourBankService).
CSRF protection: Tokens em formulários
Autenticação: Filament Breezy com suporte a Passkeys
Auditoria de Modelos: Registo automático de create, update e delete com user tracking

Gestão de Horas e Banco de Horas

Figura 6: Registo de Presença (Attendance) com Campos de Entrada/Saída e Pausas
(Instrução de print: Aceder a /admin/attendance-logs/create)

Figura 7: Visualização do Banco de Horas (HourBank) com Saldo Acumulado e Histórico
(Instrução de print: Aceder a /admin/hour-banks)

Sistema de Check-in Simplificado (AttendanceCheckIn)
A aplicação disponibiliza uma interface dedicada para o registo de presença simplificado. Esta página permite ao funcionário realizar o "Check-in" e "Check-out" com um único clique, capturando automaticamente o timestamp do servidor. O sistema gere inteligentemente o estado do dia, alternando entre:
- Entrada (Time In)
- Início de Pausa (Lunch Break Start)
- Fim de Pausa (Lunch Break End)
- Saída (Time Out)
A página valida se o utilizador possui um `employee_id` associado antes de permitir o acesso, garantindo a integridade dos dados.

Figura 15: Página de Check-in Simplificado com botões de ação dinâmica e histórico diário.
(Instrução de print: Aceder a /app/attendance-check-in com um utilizador que tenha employee_id)

Registos de Trabalho:
Entrada/saída com timestamp automático.
Suporte a múltiplos registos por dia.
Cálculo automático de durações.

Pausas e Intervalos:
Campos 'break_start' / 'break_end' para registo de pausas.
Exclusão automática de tempo de pausa nos cálculos.
Validação de lógica de intervalos.

Banco de Horas (HourBank):
Atualização automática após cada registo validado.
Rastreamento de horas acumuladas/deficitárias.
Criação automática ao criar novo funcionário.
Relatórios e extratos de saldo.
Validação automática de licenças antes de descontar: O sistema verifica se existe uma licença ou férias aprovada antes de descontar horas por ausência.
Suporte a períodos de ausência: Cálculo correto apenas para dias úteis (segunda a sexta).
Configuração flexível: Tipos de licenças justificadas vs injustificadas são configuráveis.

Absence Resource (Auditoria):
Visualização read-only de todas as ausências registadas.
Histórico completo de descontos com motivos.
Filtros por funcionário, data e tipo de dedução.
Badges visuais para diferentes tipos de descontos (injustificado, parcial, etc.).


Experiência do Utilizador (UX)


Figura 8: Notificações Toast ao Criar Funcionário - 4 Mensagens de Sucesso (Utilizador, Contrato, Banco de Horas)
(Instrução de print: Capturar o ecrã imediatamente após clicar em 'Create' num novo Employee)
Notificações Toast ao Criar Funcionário
Quando um utilizador de RH cria um novo funcionário, o sistema executa automaticamente um fluxo encadeado que cria múltiplas entidades (Utilizador, Contrato e Banco de Horas). Para dar feedback claro de cada operação realizada, foram implementadas notificações individuais em toast com ícones e cores distintos. As 4 notificações enviadas são:
Utilizador criado (email, role, status força troca de senha).
Contrato criado (tipo, salário, data início).
Banco de horas criado (saldo inicial, accrual_date).
Notificação consolidada final com checkmarks de sucesso.

Figura 9: Diálogo de Confirmação de Ações Destrutivas com Avisos Visuais
(Instrução de print: Clicar no botão de 'Delete' em qualquer registo para abrir o modal)
Diálogos de Confirmação de Ações Destrutivas
Operações críticas como eliminar um funcionário ou encerrar um contrato são irreversíveis e podem ter impacto significativo nos dados. O sistema implementa diálogos de confirmação antes de qualquer ação destrutiva, apresentando claramente o que será eliminado e pedindo confirmação explícita. Os avisos visuais (cores de alerta, ícones) tornam óbvio que se trata de uma ação importante, protegendo contra erros acidentais.
Figura 10: Badges Visuais - Indicadores de Funções (Admin/HR/Employee) e Estados de Contrato (Ativo/Encerrado/Suspenso)
(Instrução de print: Ver a lista de /admin/users ou /admin/contracts)

Badges Visuais e Indicadores de Estado
As badges (pequenos rótulos coloridos) permitem identificar rapidamente o papel de cada utilizador e o estado de cada contrato, sem necessidade de ler texto em tabelas longas:
Indicadores de função (role): Admin, HR, Employee com cores distintas.
Estados de contrato: Ativo (verde), Encerrado (cinzento), Suspenso (laranja).
Indicadores de status de licenças e férias.

Tradução PT-PT:
Rótulos e campos do formulário em português.
Mensagens de validação localizadas.
Colunas de tabelas traduzidas.
Datas e números formatados para PT-PT.

Notificações Contextuais:
Mensagens específicas por ação realizada.

Confirmações de Ação:
Diálogos de confirmação em ações destrutivas.
Avisos de mudanças não salvas.

Badges Visuais:
Indicadores de função (role) na tabela de utilizadores.
Estados visuais para contratos (ativo, encerrado, suspenso).
Indicadores de status de licenças.

Interface Responsiva:
Suporte completo a dispositivos móveis.
Layout adaptativo para tablets e desktop.
Sidebar colapsível em desktop, liberando 15-20% de espaço horizontal.
Navegação otimizada para múltiplos tamanhos de ecrã.


Melhorias e Otimizações
Padronização de Senhas:
Definição de senha padrão para novos utilizadores.
Obrigatoriedade de alteração no primeiro acesso.
Reforço de políticas de segurança.

Isolamento de Dados de Funcionário:
Proteção de privacidade e conformidade LGPD.
Dados pessoais acessíveis apenas aos autorizados.
Logs de acesso a dados sensíveis.

Ações Condicionadas:
Botões e ações visíveis apenas para perfis autorizados.
Desativação contextual de funcionalidades.
Guia visual das permissões do utilizador.

Boas Práticas de Engenharia:
Código limpo e bem estruturado.
Padrões consistentes em toda a codebase.
Naming conventions claras e significativas.
Separação de responsabilidades clara.


Validação de E-mail e Automação de Criação

Figura 11: Formulário de Criação de Funcionário (EmployeeResource) com Validação de E-mail
(Instrução de print: Aceder a /admin/employees/create e introduzir um email inválido)
O formulário de criação de funcionário inclui um campo de e-mail que valida em tempo real se o domínio é válido (exemplo: rejeita `usuario@empresa` mas aceita `usuario@empresa.com`). A validação visual (com mensagens de erro claras) é apresentada imediatamente, evitando que o utilizador submeta o formulário com dados inválidos. Isto é particularmente importante porque emails inválidos comprometem toda a comunicação automática do sistema e a criação do utilizador associado. A implementação de uma regra customizada de validação garante que a qualidade dos dados seja mantida desde o ponto de entrada.

Validação Rigorosa de E-mail
O formulário de criação de funcionário inclui validação em tempo real do domínio de e-mail através de uma Custom Rule ValidEmailDomain:
Rejeita: teste@teste (sem TLD válido).
Aceita: usuario@empresa.com (TLD mínimo de 2 caracteres).
Regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
Aplicado em Employee e User Resources em formulários de criação/edição.

Sistema Automático de Gatilhos (Observer Pattern):
O EmployeeObserver intercepta a criação de um Employee e automaticamente cria:
User: com email/nome do Employee (role: employee, senha default do .env, must_change_password: true).
Contract: tipo indefinido, salário da designação, status ativo.
Hourbank: saldo inicial 0 horas, accrual_date = data de contratação.


O Observer armazena dados em cache durante o ciclo de eventos e recupera-os após conclusão, evitando múltiplas queries e permitindo notificações granulares.

Proteção contra Metadata:
email_verified_at em UserResource sempre é auto-preenchido com a data atual.
created_at e updated_at nunca editáveis em formulários.
Campos metadata visíveis apenas em tabelas como toggleable (isToggledHiddenByDefault: true).
ActivityLogResource mostra created_at desabilitado (read-only).


Recursos Filament Implementados

Figura 12: Resource EmployeeResource - Lista com Tabela, Filtros e Ações
(Instrução de print: Aceder a /admin/employees)

O Resource EmployeeResource apresenta todos os funcionários numa tabela interativa com capacidades avançadas: filtros permitem procurar por departamento, designação ou status de contrato; a pesquisa permite localizar rapidamente um funcionário pelo nome ou e-mail; as ações em cada linha (editar, ver detalhes, eliminar) estão sempre acessíveis. O isolamento de dados garante que utilizadores de RH só veem funcionários do seu departamento, enquanto Admin vê toda a organização. Esta interface reduz significativamente o tempo necessário para encontrar e atualizar informações de funcionários, comparado com sistemas que exigem navegação através de menus complexos.
Figura 13: Resource ContractResource com Ação de Download PDF de Contrato
(Instrução de print: Aceder a /admin/contracts e mostrar o botão de ação de PDF)
Cada contrato pode ser visualizado em lista e, numa ação especializada, pode ser descarregado como PDF com toda a informação formatada profissionalmente. Esta funcionalidade é crítica para fins legais e administrativos — permite que o RH mantenha cópias arquivadas dos contratos, e aos funcionários ter acesso aos seus documentos sem necessidade de contactar o departamento. A implementação de PDF automático elimina trabalho manual repetitivo e garante consistência na documentação.


Figura 14: Resource ActivityLogResource - Visualização de Histórico de Auditoria com Filtros
(Instrução de print: Aceder a /admin/activity-logs)
O ActivityLogResource registra todas as alterações importantes no sistema (criação de utilizadores, edição de salários, eliminação de registos). Esta informação é crítica para conformidade regulatória e para rastrear quem fez o quê e quando. Os filtros permitem procurar por tipo de ação, utilizador que realizou a ação, ou data, facilitando investigações e auditorias. Por exemplo, se surgir uma discrepância num banco de horas, é possível rapidamente ver todo o histórico de alterações nesse registo. Isto é particularmente importante em contexto empresarial onde são necessárias evidências documentadas de todas as operações para fins legais.

A aplicação implementa 16 Resources Filament para gestão:

Resource
Modelo
EmployeeResource
Employee
ContractResource
Contract
PayrollResource
Payroll
UnitResource
Unit (Unidades Organizacionais)
DesignationResource
Designation
UserResource
User
RoleResource
Role (Papeis via Filament Shield)
AttendanceLogResource
AttendanceLog
LeaveAndAbsenceResource
LeaveAndAbsence (Licenças e Ausências)
VacationResource
Vacation (Férias)
HourBankResource
HourBank
AbsenceResource
Absence (Auditoria de Descontos)
StateResource
State
CityResource
City
CountryResource
Country
ActivityLogResource
ActivityLog (Auditoria via Spatie)


Cada Resource inclui:
Formulários com validação apropriada e regras de negócio.
Tabelas com ordenação, pesquisa e filtros inteligentes.
Ações customizadas por função (create, edit, delete, view).
Isolamento de dados por RBAC via Policies Eloquent.
Auditoria automática de alterações via Spatie Activity Log.

Listeners
O sistema implementa 1 Listener principal:
AuthenticationActivityLogger: Regista eventos de autenticação (login/logout) e actividades críticas de utilizadores.
Automação com Observers
O sistema utiliza Observer Pattern para automatizar operações:

EmployeeObserver: Cria automaticamente o utilizador, contrato inicial e banco de horas ao registar um novo funcionário.

ContractObserver: Quando um novo contrato é criado/atualizado, sincroniza automaticamente a designation_id do Employee com base no contrato. Isto garante que a função do funcionário é sempre consistente com o contrato ativo.

AttendanceLogObserver: Automatiza cálculos e validações nos registos de presença.

AbsenceObserver: Monitoriza a criação, atualização e eliminação de ausências, despoletando o recálculo automático do saldo do Banco de Horas para o mês correspondente, garantindo que o saldo esteja sempre sincronizado.

Activity Logging com Spatie
Todos os modelos críticos utilizam a trait LogsActivity do Spatie para rastreamento automático:
Employee, User, Contract, Vacation, LeaveAndAbsence, AttendanceLog, HourBank, Absence, Unit, etc.
Cada alteração (create, update, delete) é automaticamente registada na tabela activity_log com:
- Utilizador que fez a alteração (causer_id)
- Modelo e ID do objeto alterado
- Evento (created, updated, deleted)
- Valores antigos vs novos (attribute_changes)
- Timestamp da operação




Conclusão
Dificuldades Encontradas e Soluções Adotadas
Ao longo do desenvolvimento da Aplicação TeamCore, enfrentei desafios importantes que foram determinantes para o sucesso da aplicação.

Isolamento de dados complexo: Um dos maiores obstáculos foi implementar o isolamento de dados por função de utilizador de forma robusta. No sistema RBAC, não era suficiente apenas bloquear o acesso aos recursos e páginas — era necessário garantir que cada utilizador só consultava os seus próprios dados ao nível da base de dados. A solução foi implementar Policies Eloquent em cada modelo e Query Scopes, garantindo que as queries já vinham filtradas da fonte.

Automatização com Observers: Quando um contrato é criado, o sistema precisa sincronizar a designation do Employee para manter consistência. Implementei isto com Observers, assegurando que mudanças no contrato sempre refletem no Employee automaticamente.

Integração de Auditoria: Implementar Spatie Activity Log em múltiplos modelos sem criar overhead de performance foi desafiante. A solução foi usar o trait LogsActivity e configurar eventos apropriados em cada modelo crítico.

Validação de Banco de Horas: O sistema que valida licenças antes de descontar horas poderia criar N+1 problems. A solução foi otimizar com eager loading correto das relações.

Pontos Fortes da Aplicação
A aplicação conseguiu alcançar os seus objetivos principais com qualidade:

Gestão Salarial Automatizada (Payroll): Implementação de um motor de cálculo que considera o salário base contratual, horas extras do banco de horas e deduções, gerando recibos de vencimento de forma automática.

Gestão Inteligente de Férias: Sistema de controlo de saldo que debita automaticamente dias ao aprovar férias e restaura o saldo em caso de cancelamento ou rejeição, prevenindo erros manuais.

Arquitetura bem estruturada: Separação clara das responsabilidades, com isolamento de dados ao nível do modelo via Policies. As scopes Eloquent garantem segurança sem depender apenas de frontend.

Autenticação robusta: Integração com Filament Breezy e Passkeys oferece camadas de segurança modernas.

Automação inteligente: O Observer Pattern automatiza a sincronização de dados entre modelos, reduzindo erros manuais.

Auditoria completa: Spatie Activity Log fornece rastreamento automático de todas as operações sensíveis.

RBAC dinâmico: Filament Shield permite gerir papéis e permissões de forma granular e flexível.

Testes com cobertura: Pest v4 com testes que cobrem funcionalidades críticas.

Pontos a Melhorar
Integrações Externas: Falta de ligação com sistemas bancários externos para automatização real de pagamentos e exportação de ficheiros SEPA.

Testes de carga: Não foi validado com volumes grandes de dados (milhares de funcionários, múltiplos utilizadores simultâneos).

Documentação Técnica: Apesar de boas práticas, faltam comentários explicativos detalhados em algoritmos complexos de cálculo de horas e payroll.

Edge cases em operações críticas: Refinar a gestão de transações em fluxos encadeados (ex: criação de funcionário) para garantir atomicidade absoluta em cenários de falha de base de dados.

Cobertura de testes unitários: Expandir a cobertura de testes para cobrir cenários de borda em cálculos de horas extras e fusos horários.

O que Aprendi com a Aplicação
Esta aplicação consolidou conhecimentos fundamentais em engenharia de software:

Pensamento em arquitetura desde o início: Decisões de desenho como Policies e Observers tiveram impacto em toda a aplicação. Segurança deve estar no DNA da arquitetura.

Importância de padrões de design: Observer, Policy, Scope — padrões estabelecidos resolvem problemas complexos elegantemente.

Auditoria desde o dia 1: Integrar Spatie Activity Log desde cedo é mais fácil do que retrofit. Conformidade regulatória não é opcional.

RBAC é complexo: Implementar corretamente em 3 camadas (routes, policies, queries) é essencial para segurança real.

Testes como confiança: Pest forneceu confiança ao refatorizar código sem medo de regressões.

Integração com frameworks maduros: Filament, Filament Shield, Filament Breezy, Spatie Activity Log — escolher ferramentas corretas acelera e melhora qualidade.

Importância para o Meu Futuro Profissional
Esta aplicação demonstra competências críticas procuradas no mercado:

Sou capaz de desenhar arquiteturas seguras e escaláveis, não apenas escrever código funcional.

Consigo implementar RBAC robusto, um requisito em qualquer sistema com múltiplos utilizadores.

Compreendo a importância de auditoria e conformidade regulatória desde o design.

Trabalho com ferramentas profissionais reais (Laravel v13, Filament v5, Spatie packages) e as aplico corretamente.

Valorizo code quality, testes e boas práticas desde o início, não como afterthought.

Reflexão Final
A Aplicação TeamCore não é uma aplicação perfeita — tem limitações e áreas para melhoria. No entanto, é uma solução robusta e funcional que resolve problemas reais de gestão de RH.

Mais importante, o processo de desenvolvimento ensinou-me como pensar e agir como um engenheiro de software. Não é apenas sobre código que funciona — é sobre desenhar soluções que:
✅ Sejam seguras desde a arquitetura
✅ Sejam auditáveis para conformidade
✅ Sejam escaláveis para crescimento
✅ Sejam maintíveis para colegas
✅ Sejam testáveis para confiança

Para as próximas oportunidades profissionais, levarei a compreensão de que qualidade técnica, segurança e experiência do utilizador não são extras — são componentes essenciais de qualquer solução profissional.

Bibliografia
Laravel LLC. (2025). Laravel – The PHP framework for web artisans. Versão 13.0. Acedido em 16 de abril de 2026, de https://laravel.com 
Filament. (2025). Filament – Accelerated Laravel development. Versão 5.5+. Acedido em 16 de abril de 2026, de https://filamentphp.com 
Spatie. (2025). Laravel Activity Log. Versão 5.0. Acedido em 16 de abril de 2026, de https://spatie.be/docs/laravel-activitylog/
The PHP Group. (2025). PHP: Hypertext preprocessor. Versão 8.3. Acedido em 16 de abril de 2026, de https://www.php.net 
Composer Authors. (2025). Composer – Dependency manager for PHP. Acedido em 16 de abril de 2026, de https://getcomposer.org 
Oracle Corporation. (2025). MySQL 8.0 reference manual. Acedido em 16 de abril de 2026, de https://dev.mysql.com/doc 
GitHub, Inc. (2025). GitHub – Where the world builds software. Acedido em 16 de abril de 2026, de https://github.com 
Pest. (2025). Pest – PHP Testing Framework. Versão 4.5. Acedido em 16 de abril de 2026, de https://pestphp.com 





Anexos



