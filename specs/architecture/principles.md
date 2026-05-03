# Architecture Principles

## 1. Separation of Concerns
Each service must have a single clear responsibility.

## 2. Event-Driven First
All cross-domain actions must be triggered via events.

## 3. Financial Consistency
Financial data must be accurate and auditable.

## 4. AI as Observer
AI must not own business logic. It only observes and enriches.

## 5. BFF Pattern
Frontends must not directly consume core APIs.

## 6. Stateless Services
Services must not rely on in-memory state.

## 7. Idempotency
All operations must be safe for retries.

## 8. Auditability
Every financial transaction MUST be traceable and audited.
