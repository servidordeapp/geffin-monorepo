# System Overview

## Purpose
A multi-tenant financial hub for schools, enabling billing, payments, wallet usage, and financial management for institutions and guardians.

## Architecture Style
- **Modular Monolith (Laravel Core)**: Centralized business logic with modular boundaries.
- **Event-Driven Architecture (EDA)**: Decoupled communication through RabbitMQ.
- **Backend for Frontend (BFF)**: Specialized backends for different client types.
- **AI-First**: Decoupled AI Gateway for LLM orchestration and insights.

## Core Components

### API Core (Laravel)
- Handles domain logic
- Emits domain events
- Ensures financial consistency

### BFF Guardian
- Optimized for mobile
- Aggregates data for guardians

### BFF School
- Optimized backend for school dashboards and admin workflows and operations.

### Workers (Go)
- Consume RabbitMQ events
- Execute async processing

### AI Gateway (Python)
- LLM orchestration
- Insight generation

### API Gateway (Python)
Primary entry point for external requests, handling routing, rate limiting, and authentication.

## Communication

- **Sync**: HTTP/REST for immediate request-response interactions.
- **Async**: RabbitMQ for event-driven, decoupled communication.

## Storage

- **PostgreSQL**: Primary transactional database for financial integrity.
- **Redis**: Caching and session management.
- **MinIO**: Object storage for invoices, reports, and documents.

## Frontends

- **Next.js**: Modern web interfaces for schools and admin.
- **Next.js**: Modern web interface for guardians
- **React Native**: Cross-platform mobile applications for guardians.
