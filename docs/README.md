# Documentação da Aplicação TeamCore

Bem-vindo à documentação oficial da **Aplicação TeamCore**, um sistema robusto de gestão de Recursos Humanos construído com Laravel 13 e Filament 5.

Esta documentação está dividida por temas, cada um contendo uma visão técnica (para programadores e administradores de sistema) e um manual de utilizador (para colaboradores e gestores de RH).

---

## 📂 Estrutura da Documentação

### 📘 Manuais Consolidados
*   **[Manual Técnico Consolidado](./manual_tecnico.md):** Arquitetura end-to-end, operação, manutenção, troubleshooting e governança técnica.
*   **[Manual de Utilizador Consolidado](./manual_utilizador.md):** Guia funcional por perfil (`Admin`, `HR`, `Employee`) com fluxos operacionais.

### 🛡️ [Arquitetura e Segurança](./arquitetura-e-seguranca/)
*   **[Documentação Técnica](./arquitetura-e-seguranca/tecnico.md):** Stack tecnológica, RBAC (Shield), auditoria e padrões de código.
*   **[Manual do Utilizador](./arquitetura-e-seguranca/manual-utilizador.md):** Níveis de acesso, segurança de conta e boas práticas.

### 🏢 [Estrutura Organizacional](./estrutura-organizacional/)
*   **[Documentação Técnica](./estrutura-organizacional/tecnico.md):** Modelagem de Unidades, Cargos e Localizações.
*   **[Manual do Utilizador](./estrutura-organizacional/manual-utilizador.md):** Como configurar departamentos, hierarquias e cargos.

### 👥 [Gestão de Colaboradores](./gestao-de-colaboradores/)
*   **[Documentação Técnica](./gestao-de-colaboradores/tecnico.md):** Ciclo de vida do funcionário, contratos e geração de PDFs.
*   **[Manual do Utilizador](./gestao-de-colaboradores/manual-utilizador.md):** Registar funcionários, gerir contratos e documentos.

### ⏰ [Presença e Horários](./presenca-e-horarios/)
*   **[Documentação Técnica](./presenca-e-horarios/tecnico.md):** Motor de cálculo de tempos, banco de horas e regras de atrasos.
*   **[Manual do Utilizador](./presenca-e-horarios/manual-utilizador.md):** Como picar o ponto, consultar o banco de horas e entender faltas.

### 🏖️ [Ausências e Férias](./ausencias-e-ferias/)
*   **[Documentação Técnica](./ausencias-e-ferias/tecnico.md):** Fluxos de aprovação, gestão de saldos e integração com o ponto.
*   **[Manual do Utilizador](./ausencias-e-ferias/manual-utilizador.md):** Pedir férias, justificar ausências e regras de aprovação.

### 💰 [Processamento Salarial](./processamento-salarial/)
*   **[Documentação Técnica](./processamento-salarial/tecnico.md):** Fórmulas financeiras e motor de processamento automático.
*   **[Manual do Utilizador](./processamento-salarial/manual-utilizador.md):** Como processar salários e consultar recibos de vencimento.

---

## 🛠️ Manutenção da Documentação
Esta documentação deve ser atualizada sempre que:
1.  Uma nova funcionalidade core for implementada.
2.  Uma regra de negócio for alterada (ex: coeficientes de horas extra).
3.  A estrutura da base de dados sofrer alterações significativas.

*Nota: Todas as descrições seguem a norma de Português de Portugal (PT-PT).*
