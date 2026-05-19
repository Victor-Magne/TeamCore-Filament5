# Guião de Apresentação PAP: Aplicação TeamCore

**Duração Estimada:** 20 Minutos
**Público-alvo:** Júri da Prova de Aptidão Profissional
**Apresentador:** Victor Gabriel Cristino Gomes

---

## 1. Introdução e Contextualização (2 Minutos)
*   **Abertura**: Apresentação pessoal e do tema: "TeamCore - Uma nova gestão de Recursos Humanos".
*   **O Problema**: A complexidade e falta de usabilidade dos sistemas de RH tradicionais em PMEs.
*   **A Solução**: Uma aplicação intuitiva, segura e automatizada baseada em Laravel e Filament.
*   **Objectivos**: Centralização de dados, automação de processos críticos e auditoria total.

## 2. Arquitectura e Segurança (3 Minutos)
*   **Stack Tecnológica**: Laravel 13, PHP 8.4, Filament v5, MySQL e Pest para testes.
*   **RBAC (Role-Based Access Control)**: Explicação dos 3 perfis principais (ADMIN, HR, EMPLOYEE).
*   **Isolamento de Dados**: Demonstrar como um funcionário só vê os seus dados, enquanto gestores vêm apenas os seus subordinados (Uso de Policies e Scopes Eloquent).
*   **Auditoria**: Menção ao `ActivityLog` (Spatie) que regista cada clique e alteração sensível.

## 3. Gestão de Funcionários e Onboarding (3 Minutos)
*   **Demonstração Rápida**: Criação de um novo funcionário no `EmployeeResource`.
*   **O "Pulo do Gato" (Automação)**: Explicar que ao clicar em "Criar", o sistema usa o `EmployeeObserver` para gerar automaticamente:
    1.  Conta de utilizador com senha temporária.
    2.  Contrato inicial (placeholder).
    3.  Registo de Banco de Horas com saldo zero.
*   **UX**: Feedback visual através das 4 notificações Toast (feedback positivo imediato).

## 4. Deep Dive: Gestão de Assiduidade e Banco de Horas (5 Minutos) - **PONTO CRÍTICO**
*   **Registo de Ponto**: Mostrar a página de `Check-in` simplificado (Mobile-friendly).
*   **Lógica de Negócio (DeductHourBankService)**:
    *   **Tolerância**: 15 minutos de cortesia.
    *   **Atraso Parcial**: Entre 15 min e 1 hora (desconto exacto).
    *   **Falta Injustificada**: Atrasos superiores a 1 hora convertem-se em falta de dia inteiro.
    *   **Regra dos 3 Atrasos**: Demonstração teórica de como 3 atrasos consecutivos se tornam 1 falta.
*   **Banco de Horas Cumulativo**: Mostrar o `HourBankResource` e como cada picagem gera um `HourBankMovement` (incremental e auditável).

## 5. Deep Dive: Processamento Salarial (Payroll) (3 Minutos) - **PONTO CRÍTICO**
*   **O Motor de Cálculo**: Explicar o `GeneratePayrollService`.
*   **Fórmulas**:
    *   Cálculo do valor hora (Salário Base / 176h mensais).
    *   Integração com Banco de Horas: Horas extra pagas a 150% (factor 1.5).
    *   Deduções automáticas baseadas em ausências não justificadas.
*   **Demonstração**: Gerar o payroll de um funcionário para o mês actual e mostrar o recibo gerado.

## 6. Qualidade Técnica e Testes (2 Minutos)
*   **Confiança**: "A aplicação não só funciona, como é provado por testes".
*   **Métricas**: 97 testes automatizados (Pest v4) cobrindo todos os serviços críticos mencionados.
*   **Resiliência**: Como os testes garantem que uma alteração no banco de horas não quebra o processamento salarial.

## 7. Conclusão e Reflexão (2 Minutos)
*   **Dificuldades**: O desafio de implementar a lógica de 3 camadas de segurança e a sincronização atómica de dados.
*   **Futuro**: Integrações bancárias (SEPA) e dashboards analíticos avançados.
*   **Encerramento**: "A TeamCore transforma a gestão de RH de um fardo administrativo numa vantagem estratégica".
*   **Abertura para Perguntas**: Colocar-se à disposição do júri.

---

### Dicas para a Apresentação:
1.  **Prepare Dados de Teste**: Tenha funcionários com atrasos reais e horas extras para mostrar o Banco de Horas a "mexer".
2.  **Foque na Lógica**: O júri valoriza mais saber *porquê* o sistema descontou uma hora do que apenas ver o formulário bonito.
3.  **Use Termos Técnicos**: Refira "Observers", "Services", "Traits" e "Policies" para demonstrar domínio do Laravel.
