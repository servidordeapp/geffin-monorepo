# GFN Monorepo - LLM Guidance & Project Structure

This file provides foundational mandates for AI agents working in this repository. All agents MUST adhere to the standards and workflows defined here.

## 📜 Core Mandate: The Constitution
Before performing any task, read and internalize the project principles in:
👉 `.specify/memory/constitution.md`

**Key Priorities**: Code Quality, Testing Standards (TDD), UX Consistency, and Performance.

## 🏗️ Project Structure

All architectural definitions, domain documentation, and specifications MUST follow this directory structure:

```text
GFN/
├── specs/                      # ALL technical and business documentation
│   ├── architecture/           # Core system design and high-level decisions
│   │   ├── system-overview.md  # Global architecture diagram and context
│   │   ├── tech-stack.md       # Approved languages, frameworks, and versions
│   │   ├── communication.md    # Sync/Async patterns (REST, gRPC, PubSub)
│   │   └── principles.md       # Derived from Constitution (Local guidance)
│   ├── domains/                # Business domain boundaries and logic
│   │   ├── financial.md        # Core financial logic and ledgers
│   │   ├── billing.md          # Invoicing and recurrence rules
│   │   ├── contracts.md        # Student/School agreement management
│   │   ├── ai.md               # AI integration and strategy
│   │   └── [domain-name].md    # Students, Guardian, Canteen, etc.
│   ├── services/               # Specific service specifications
│   │   ├── api-laravel.md      # Core Legacy/Modern API definitions
│   │   ├── bff-guardian.md     # Backend-for-Frontend (Guardian App)
│   │   ├── workers-go.md       # High-performance background processing
│   │   └── ai-gateway.md       # AI Orchestration layer
│   ├── interactions/           # Multi-service flows and user journeys
│   │   ├── payment-flow.md     # Cross-service payment orchestration
│   │   ├── billing-flow.md     # Billing generation lifecycle
│   │   └── overdue-flow.md     # Delinquency and recovery logic
│   ├── events/                 # Message schemas and event definitions
│   │   ├── invoice-generated.json
│   │   ├── payment-approved.json
│   │   └── debt-registered.json
│   ├── rules/                  # Cross-cutting engineering standards
│   │   ├── naming.md           # Naming conventions (code, events, DB)
│   │   ├── event-conventions.md # PubSub/Event header and payload standards
│   │   └── api-guidelines.md   # REST/GraphQL design patterns
│   └── [id]-[feature]/         # Temporary feature-specific specs (if active)
├── .specify/                   # SpecKit internal tooling
├── src/                        # Implementation (following /services structure)
└── tests/                      # Testing (following /services structure)
```

## 🔄 SpecKit Workflow

Agents MUST follow the **Research -> Strategy -> Execution** lifecycle using the SpecKit toolset:

1.  **Specification (`/speckit.specify`)**:
    *   Location: `specs/[id]-[feature]/spec.md`
    *   Goal: Define *what* to build and *how* to measure success.
2.  **Planning (`/speckit.plan`)**:
    *   Location: `specs/[id]-[feature]/plan.md`
    *   Goal: Define *how* to build it, including directory structure and tech stack.
    *   *Check*: Verify against the Constitution before proceeding.
3.  **Task Generation (`/speckit.tasks`)**:
    *   Location: `specs/[id]-[feature]/tasks.md`
    *   Goal: Break down the plan into atomic, testable tasks.
4.  **Implementation (`/speckit.implement`)**:
    *   Process: Iterative **Plan -> Act -> Validate** cycle.
    *   Requirement: Mandatory TDD. Write failing tests before implementation.
    *   Requirement: Surgical updates only. No unrelated refactoring.

## 🛠️ Execution Rules

*   **Validation is Mandatory**: After every code change, execute the project-specific build and test commands.
*   **Context Efficiency**: Use `grep_search` to find symbols. Only read what is necessary.
*   **No Hidden Logic**: Avoid hacks or complex inheritance. Prefer composition and explicit type safety.
*   **Documentation**: Keep `specs/` artifacts updated as the implementation evolves.

---
*Note: This file is a foundational mandate. If its instructions conflict with general defaults, these instructions take precedence.*
