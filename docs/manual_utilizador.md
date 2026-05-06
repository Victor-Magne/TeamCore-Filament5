# Manual do Utilizador - Aplicação TeamCore

## 1. Objetivo
Este manual explica como usar a aplicação TeamCore para os diferentes tipos de utilizador:
- Administrador (`admin`)
- Recursos Humanos (`hr`)
- Colaborador (`employee`)

## 2. Acesso à Aplicação
A aplicação possui dois contextos principais:
- Painel administrativo (operações e gestão): normalmente `/admin`
- Portal do colaborador (acesso pessoal): normalmente `/app`

Notas:
- O acesso visível no menu depende do perfil e permissões.
- Algumas ações podem ficar ocultas por política de segurança.

## 3. Primeiros Passos (Todos os Utilizadores)
1. Fazer login com e-mail e palavra-passe.
2. Se for solicitado, alterar palavra-passe no primeiro acesso.
3. Configurar 2FA quando exigido.
4. Confirmar dados pessoais no perfil.

## 4. Perfis de Utilizador e Permissões

## 4.1 Administrador (Admin)
Responsabilidades típicas:
- gestão global de utilizadores e papéis;
- configuração de unidades e estrutura organizacional;
- supervisão de auditoria e acessos;
- apoio à equipa de RH em regras e governança.

Acesso esperado:
- recursos de sistema (`Users`, `Roles`, `Units`);
- recursos funcionais de RH e consulta global;
- logs de atividade e configurações centrais.

## 4.2 Recursos Humanos (HR)
Responsabilidades típicas:
- gestão de colaboradores e contratos;
- validação de férias/licenças;
- acompanhamento de assiduidade e banco de horas;
- processamento salarial.

Acesso esperado:
- `Employees`, `Contracts`, `AttendanceLogs`, `Vacations`, `LeaveAndAbsences`, `HourBanks`, `Payrolls`;
- dashboards com indicadores operacionais;
- ações de aprovação e atualização de registos.

## 4.3 Colaborador (Employee)
Responsabilidades típicas:
- consultar dados pessoais;
- registar presença;
- acompanhar saldo de férias e banco de horas;
- submeter pedidos de férias/licenças.

Acesso esperado:
- portal pessoal e widgets associados;
- página de check-in simplificado;
- consulta de contrato ativo e histórico pessoal permitido.

## 5. Fluxos de Uso por Perfil

## 5.1 Fluxo Admin
### Criar utilizador
1. Abrir `Users`.
2. Clicar em `Create`.
3. Preencher nome, e-mail e papel.
4. Guardar e validar acesso.

### Gerir papéis e permissões
1. Abrir `Roles`.
2. Selecionar papel.
3. Ajustar permissões necessárias.
4. Guardar e testar com conta de validação.

### Revisar auditoria
1. Abrir `ActivityLogs`.
2. Filtrar por utilizador/ação/data.
3. Validar alterações sensíveis.

## 5.2 Fluxo HR
### Registar novo colaborador
1. Abrir `Employees`.
2. Clicar em `Create`.
3. Preencher dados obrigatórios.
4. Guardar.

Resultado esperado automático:
- criação de utilizador;
- criação de contrato inicial;
- criação de banco de horas;
- notificações de confirmação.

### Gerir contratos
1. Abrir `Contracts`.
2. Criar ou editar contrato.
3. Confirmar salário, tipo, horários e estado.
4. Usar ação de PDF quando necessário.

### Aprovar férias/licenças
1. Abrir `Vacations` ou `LeaveAndAbsences`.
2. Filtrar por estado pendente.
3. Aprovar/rejeitar com justificativa quando aplicável.
4. Confirmar impacto no saldo.

### Processar salários
1. Abrir `Payrolls`.
2. Executar ação de processamento.
3. Validar totais e estados (`pending`, `processed`, `paid`).

## 5.3 Fluxo Employee
### Registar presença (check-in simplificado)
1. Aceder à página de assiduidade no portal.
2. Executar ações na sequência do dia:
- entrada;
- início de almoço;
- fim de almoço;
- saída.

3. Validar histórico do dia.

### Consultar banco de horas
1. Abrir secção de banco de horas.
2. Ver saldo e movimentos.
3. Em caso de divergência, comunicar RH.

### Pedir férias/licença
1. Abrir secção de pedidos.
2. Escolher período e tipo.
3. Submeter pedido.
4. Acompanhar estado de aprovação.

## 6. Regras Funcionais Importantes para Utilizadores
- Atrasos até tolerância podem não gerar penalização.
- Atrasos significativos e faltas podem gerar dedução.
- Férias aprovadas debitam saldo; rejeições/cancelamentos podem repor saldo.
- Utilizador não deve aprovar o próprio pedido sem permissão específica.

## 7. Boas Práticas de Utilização
- manter dados de contacto atualizados;
- registar assiduidade em tempo real;
- anexar comprovativos quando exigido;
- rever dados antes de confirmar ações críticas;
- usar filtros e pesquisa para evitar alterações no registo errado.

## 8. Notificações e Feedback
A aplicação apresenta notificações para:
- criação de registos com sucesso;
- erros de validação;
- falhas de autorização;
- ações concluídas no fluxo de RH.

Se uma ação não for concluída:
1. rever campos obrigatórios;
2. confirmar permissões;
3. tentar novamente;
4. reportar ao administrador se persistir.

## 9. FAQ Rápido
### Não vejo um menu/recurso. Porquê?
Provavelmente o seu papel não tem permissão para esse recurso.

### Não consigo aprovar um pedido meu.
Bloqueio de conflito de interesses por política de segurança.

### O saldo parece incorreto.
Verificar movimentos e registos de presença do período; se necessário, pedir validação ao RH.

### Não consigo entrar no painel.
Confirmar credenciais, 2FA e papel atribuído.

## 10. Procedimento de Suporte Interno
Quando abrir pedido de suporte, incluir:
- perfil do utilizador (`admin`, `hr`, `employee`);
- data/hora aproximada do problema;
- ecrã e ação executada;
- mensagem de erro exibida;
- registo/ID afetado (se existir).

## 11. Segurança para Utilizadores
- não partilhar palavra-passe;
- usar palavra-passe forte;
- ativar/confirmar 2FA quando aplicável;
- terminar sessão em equipamentos partilhados.

## 12. Referências de Navegação
Documentação detalhada por tema:
- `docs/arquitetura-e-seguranca/manual-utilizador.md`
- `docs/estrutura-organizacional/manual-utilizador.md`
- `docs/gestao-de-colaboradores/manual-utilizador.md`
- `docs/presenca-e-horarios/manual-utilizador.md`
- `docs/ausencias-e-ferias/manual-utilizador.md`
- `docs/processamento-salarial/manual-utilizador.md`
