# Feature Specification: Epic Auth — Autenticação

**Feature Branch**: `001-epic-auth`
**Created**: 2026-05-10
**Status**: Draft
**Input**: User description: "Epic Auth — Login, Recuperar Senha, Resetar Senha, Confirmar Email para guardian-web, guardian-mobile e school-web"

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Login do Responsável (Priority: P1)

Um responsável (guardian) recebe acesso ao sistema quando cadastrado por um administrador escolar. Ao acessar o app web ou mobile, informa seu e-mail e senha para entrar na plataforma. Se o e-mail ainda não foi confirmado, o acesso é bloqueado com instrução clara para verificar a caixa de entrada.

**Why this priority**: Sem login, nenhuma outra funcionalidade do aplicativo do responsável é acessível. É o ponto de entrada de toda a experiência.

**Independent Test**: Pode ser testado de forma isolada criando um responsável pré-cadastrado, acessando a tela de login e verificando o acesso ao dashboard após autenticação bem-sucedida.
    
**Acceptance Scenarios**:

1. **Given** um responsável com e-mail confirmado e senha cadastrada, **When** informa credenciais corretas, **Then** acessa o sistema e recebe sessão autenticada.
2. **Given** um responsável com e-mail confirmado, **When** informa senha incorreta por 5 vezes consecutivas, **Then** o sistema bloqueia novas tentativas temporariamente e exibe mensagem de orientação.
3. **Given** um responsável com e-mail ainda não confirmado, **When** tenta fazer login com credenciais corretas, **Then** acesso é negado e sistema exibe instrução para confirmar e-mail, com opção de reenviar o link.
4. **Given** um responsável autenticado, **When** efetua logout, **Then** sessão é encerrada e acesso a rotas protegidas é bloqueado.

---

### User Story 2 — Login do Administrador Escolar (Priority: P1)

Um administrador escolar acessa o painel de gestão da escola via browser. Utiliza e-mail e senha para autenticar. As mesmas regras de verificação de e-mail e bloqueio por tentativas se aplicam.

**Why this priority**: Sem login do admin, a gestão da escola (cadastros, finanças, contratos) é inacessível. Prioridade equivalente ao login do responsável.

**Independent Test**: Pode ser testado de forma isolada criando um usuário admin pré-cadastrado, acessando o painel e verificando o acesso ao dashboard após autenticação.

**Acceptance Scenarios**:

1. **Given** um admin com e-mail confirmado e senha cadastrada, **When** informa credenciais corretas, **Then** acessa o painel de gestão e recebe sessão autenticada.
2. **Given** um admin, **When** informa senha incorreta por 5 vezes consecutivas, **Then** sistema bloqueia novas tentativas temporariamente.
3. **Given** um admin com e-mail não confirmado, **When** tenta fazer login, **Then** acesso é negado com instrução para confirmar e-mail.
4. **Given** um admin autenticado, **When** efetua logout, **Then** sessão encerrada e acesso bloqueado.

---

### User Story 3 — Confirmação de E-mail (Priority: P2)

Quando um responsável ou administrador é cadastrado no sistema (por um admin escolar), recebe um e-mail de boas-vindas com link para confirmar seu endereço. Sem essa confirmação, o acesso ao sistema permanece bloqueado. O link expira em 144 horas. Caso o link expire ou não chegue, o usuário pode solicitar reenvio.

**Why this priority**: Pré-requisito para o login. Sem confirmação de e-mail, o P1 não está completo. Deve ser implementado junto ou antes do login entrar em produção.

**Independent Test**: Pode ser testado criando um usuário, capturando o link de verificação no e-mail de desenvolvimento, acessando o link e verificando que o login passa a funcionar.

**Acceptance Scenarios**:

1. **Given** um usuário recém-cadastrado, **When** clica no link de verificação enviado por e-mail dentro de 144 horas, **Then** e-mail é confirmado e login passa a ser permitido.
2. **Given** um usuário com link expirado (após 144 horas), **When** tenta usar o link, **Then** sistema informa expiração e oferece opção de solicitar novo link.
3. **Given** um usuário já com e-mail confirmado, **When** tenta usar link de verificação novamente, **Then** sistema informa que e-mail já foi verificado (sem erro).
4. **Given** um usuário não confirmado autenticado na tela de espera, **When** solicita reenvio do link, **Then** novo e-mail é enviado e sistema confirma o envio.

---

### User Story 4 — Recuperação de Senha (Priority: P3)

Um usuário (responsável ou admin) que esqueceu a senha acessa a tela de "esqueci minha senha", informa seu e-mail cadastrado, e recebe um link para redefinir. O link expira em 60 minutos. Após definir nova senha, todas as sessões ativas do usuário são encerradas por segurança.

**Why this priority**: Funcionalidade de suporte crítica para a experiência. Sem ela, um usuário que perde a senha fica permanentemente bloqueado. Mas não bloqueia o MVP inicial com usuários de teste.

**Independent Test**: Pode ser testado de forma isolada: solicitar reset, capturar link no e-mail de desenvolvimento, redefinir senha, verificar que login com nova senha funciona e que sessões anteriores foram encerradas.

**Acceptance Scenarios**:

1. **Given** um usuário com e-mail cadastrado, **When** solicita recuperação de senha informando seu e-mail, **Then** recebe e-mail com link de redefinição válido por 60 minutos.
2. **Given** um e-mail não cadastrado, **When** usuário solicita recuperação, **Then** sistema responde com mensagem genérica (não revela se o e-mail existe ou não).
3. **Given** um link de reset válido, **When** usuário define nova senha, **Then** senha é atualizada, todas as sessões ativas são encerradas e usuário é redirecionado para login.
4. **Given** um link de reset expirado (após 60 minutos), **When** usuário tenta usá-lo, **Then** sistema informa expiração e instrui solicitar novo link.
5. **Given** um link de reset já utilizado, **When** usuário tenta reutilizá-lo, **Then** sistema rejeita e informa que o link não é mais válido.

---

### Edge Cases

- Usuário tenta fazer login com e-mail em formato inválido: sistema valida formato antes de consultar.
- Usuário solicita múltiplos e-mails de recuperação de senha: apenas o último link gerado é válido.
- Usuário solicita reenvio de verificação de e-mail em loop: sistema deve ter proteção contra spam (rate limit no reenvio).
- Responsável e admin com mesmo e-mail cadastrado em ambos os sistemas: tratados como contas independentes por tabelas separadas, login em endpoints distintos.
- Usuário com conta desativada tenta login: deve receber mensagem de conta inativa (sem revelar detalhes de segurança).

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistema DEVE permitir autenticação de responsáveis via e-mail e senha no endpoint dedicado a responsáveis.
- **FR-002**: Sistema DEVE permitir autenticação de administradores escolares via e-mail e senha no endpoint dedicado a administradores.
- **FR-003**: Sistema DEVE exigir confirmação de e-mail antes de permitir login para qualquer tipo de usuário.
- **FR-004**: Sistema DEVE bloquear tentativas de login após 5 falhas consecutivas para o mesmo e-mail/IP.
- **FR-005**: Sistema DEVE enviar e-mail de verificação ao criar novo usuário (responsável ou admin), com link válido por 144 horas.
- **FR-006**: Sistema DEVE disponibilizar endpoint para reenvio do e-mail de verificação, com proteção contra uso abusivo.
- **FR-007**: Sistema DEVE permitir solicitação de recuperação de senha via e-mail cadastrado, respondendo de forma genérica independente de o e-mail existir.
- **FR-008**: Sistema DEVE enviar link de redefinição de senha com validade de 60 minutos.
- **FR-009**: Sistema DEVE invalidar todas as sessões ativas do usuário ao redefinir a senha com sucesso.
- **FR-010**: Sistema DEVE invalidar link de reset após uso único.
- **FR-011**: Sistema DEVE permitir logout que encerre a sessão corrente.
- **FR-012**: Sistema DEVE utilizar e-mails HTML personalizados (identidade visual do produto) para verificação e recuperação de senha.
- **FR-013**: Responsáveis e administradores DEVEM ter endpoints de autenticação distintos, refletindo suas identidades separadas no sistema.
- **FR-014**: Todo tráfego de autenticação das interfaces web e mobile DEVE passar pelo BFF correspondente antes de chegar à API.
- **FR-015**: Interfaces web (guardian-web, school-web) e mobile (guardian-mobile) DEVEM armazenar e transmitir sessão via Bearer token.

### Key Entities *(include if feature involves data)*

- **Responsável (Guardian)**: Usuário do app do responsável — e-mail, senha, status de confirmação de e-mail, data de criação. Pré-cadastrado por administrador escolar.
- **Administrador Escolar (Admin)**: Usuário do painel de gestão — e-mail, senha, status de confirmação de e-mail. Pré-cadastrado no sistema.
- **Sessão Autenticada**: Credencial temporária emitida ao logar com sucesso. Vinculada ao usuário. Invalidada no logout ou reset de senha.
- **Token de Verificação de E-mail**: Referência gerada no cadastro, válida por 144 horas, usada para confirmar o endereço de e-mail.
- **Token de Reset de Senha**: Referência temporária válida por 60 minutos, de uso único, enviada por e-mail para redefinição de senha.
- **BFF Guardian**: Camada intermediária entre os clientes do responsável (web e mobile) e a API Core.
- **BFF School**: Camada intermediária entre o cliente do administrador (web) e a API Core.

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Responsável com conta válida consegue completar o login em menos de 30 segundos do início ao acesso ao dashboard.
- **SC-002**: Admin com conta válida consegue completar o login em menos de 30 segundos.
- **SC-003**: 100% dos novos usuários recebem e-mail de verificação em até 2 minutos após o cadastro.
- **SC-004**: Usuário consegue concluir o fluxo de recuperação de senha (do clique em "esqueci minha senha" até o login com nova senha) em menos de 5 minutos.
- **SC-005**: Após 5 tentativas de login incorretas, novas tentativas são bloqueadas imediatamente sem afetar outros usuários.
- **SC-006**: Link de reset de senha expirado ou já utilizado é rejeitado em 100% dos casos, sem exposição de dados do usuário.
- **SC-007**: Logout encerra a sessão imediatamente — qualquer requisição subsequente com o token antigo é rejeitada.
- **SC-008**: Reenvio de e-mail de verificação chega ao usuário em até 2 minutos.

---

## Assumptions

- Usuários (responsáveis e administradores) são sempre pré-cadastrados por um administrador — não há fluxo de auto-registro neste epic.
- Usuários recém-criados têm e-mail marcado como não confirmado por padrão, independente de quem os cadastrou.
- O ambiente de desenvolvimento utiliza Mailpit como receptor de e-mails; produção usará outro provedor (fora do escopo deste epic).
- Os templates HTML de e-mail terão identidade visual do produto, mas o design exato está fora do escopo do spec — será definido durante implementação.
- Guardian-web e school-web são Single Page Applications (Next.js); guardian-mobile é React Native.
- BFF Guardian e BFF School são implementados com NestJS (Node.js), atuando como proxy entre clientes e API Core.
- Responsáveis e administradores escolares são entidades completamente separadas no sistema (tabelas distintas, endpoints distintos, sessões independentes).
- Responsáveis têm acesso via guardian-web (browser) e guardian-mobile (app). Administradores têm acesso apenas via school-web (browser).
- Sessões são representadas por Bearer tokens; não há uso de cookies de sessão.
- O sistema pode ter múltiplos administradores escolares ativos simultaneamente.
- A invalidação de todas as sessões ativas no reset de senha é um requisito de segurança não negociável.
