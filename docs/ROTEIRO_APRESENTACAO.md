# Roteiro de Apresentação: Aplicação TeamCore
**Tempo Estimado:** 15 Minutos

---

## 1. Introdução e Abertura (0:00 - 2:00)
*   **Abertura:** "Bom dia/boa tarde. O meu nome é Victor Gomes e hoje vou apresentar-vos a **TeamCore**, uma aplicação de gestão de Recursos Humanos (RH) desenvolvida para modernizar e simplificar as operações administrativas de uma empresa."
*   **A Área:** "A aplicação foca-se na área de Sistemas de Informação Organizacionais, especificamente na gestão do capital humano."
*   **O Problema:** "Muitas empresas, especialmente PMEs, lutam com sistemas de RH fragmentados, processos manuais propensos a erros (como o cálculo de horas extras) e a falta de visibilidade imediata para os funcionários sobre os seus próprios dados. A TeamCore propõe resolver isto através da centralização, automação e transparência."

---

## 2. Desenvolvimento: Funcionalidades Chave (2:00 - 13:00)

### A. Interface Unificada e Controlo de Acesso (RBAC) (2 min)
*   **Demonstração:** Mostrar o ecrã de Login (Filament Breezy/Passkeys).
*   **Explicação:** "A aplicação utiliza uma interface única onde o que o utilizador vê depende do seu papel (Admin, HR ou Employee). Isto garante segurança e privacidade dos dados (isolamento via Policies), permitindo que um funcionário veja apenas o seu perfil, enquanto o RH gere o departamento."

### B. Automação na Criação de Funcionários (2 min)
*   **Demonstração:** Criar um funcionário fictício no `EmployeeResource`.
*   **Explicação:** "Um dos pontos fortes é a automação. Ao registar um funcionário, o sistema utiliza *Observers* para criar automaticamente o seu utilizador, o seu contrato inicial e o seu banco de horas. Em segundos, o funcionário está pronto para trabalhar, com notificações 'toast' a confirmar cada passo."

### C. Gestão de Presença e Banco de Horas (3 min)
*   **Demonstração:** Mostrar a página `AttendanceCheckIn` (/app/attendance-check-in).
*   **Explicação:** "O coração da gestão de tempo é o check-in simplificado. O funcionário regista a entrada, pausas e saída com um clique. O sistema calcula automaticamente os minutos trabalhados vs. o contratado. Se houver défice ou excesso, o **HourBankService** propaga esse saldo automaticamente para o mês seguinte, sem intervenção humana."

### D. Férias e Licenças (2 min)
*   **Demonstração:** Mostrar o `VacationResource` e o fluxo de aprovação.
*   **Explicação:** "O sistema de férias é inteligente. Quando o RH aprova um pedido, o saldo de férias do funcionário é decrementado automaticamente. Se o pedido for cancelado ou rejeitado, o saldo é restaurado. Isto elimina a necessidade de folhas de cálculo paralelas."

### E. Processamento Salarial (Payroll) (1 min)
*   **Demonstração:** Mostrar o `PayrollResource`.
*   **Explicação:** "A aplicação fecha o ciclo com o Payroll. O sistema cruza os dados do contrato (salário base) com o banco de horas (horas extras ou faltas) para gerar automaticamente o valor líquido a pagar, permitindo exportar recibos e relatórios."

### F. Auditoria e Transparência (1 min)
*   **Demonstração:** Mostrar o `ActivityLogResource`.
*   **Explicação:** "Tudo o que acontece na TeamCore é auditado. Alterações de salários, eliminações ou acessos são registados via Spatie Activity Log, garantindo total conformidade e segurança."

---

## 3. Conclusão e Fecho (13:00 - 15:00)
*   **Resumo:** "Como vimos, a TeamCore transforma processos que costumavam levar horas em tarefas de segundos. Desde a admissão automática até ao cálculo exato do banco de horas."
*   **Apelo ao Problema:** "Voltando ao problema inicial: a burocracia não deve ser um obstáculo ao crescimento. A TeamCore resolve a complexidade através da tecnologia, devolvendo tempo ao RH para se focar no que realmente importa: as pessoas. Muito obrigado."

---

## Dicas para o Orador:
1.  **Contexto Visual:** Utilize as 'Instruções de Print' do `RPP.md` para preparar os slides ou a demo ao vivo.
2.  **Ritmo:** Mantenha um tom profissional e pausado.
3.  **Preparação:** Certifique-se de que tem um utilizador com role 'HR' e outro com 'Employee' prontos para trocar de ecrã rapidamente.
