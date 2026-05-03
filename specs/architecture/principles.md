# Principles

Derived from the Project Constitution (`.specify/memory/constitution.md`).

1. **Domain Isolation**: Even within the Laravel core, maintain clear boundaries between domains (Billing, Payments, Financial).
2. **Contract-First**: Define API contracts and event schemas before implementation.
3. **Idempotency**: All asynchronous event consumers MUST be idempotent.
4. **Auditability**: Every financial transaction MUST be traceable and audited.
5. **Fail-Fast**: Validate input and constraints as early as possible.
