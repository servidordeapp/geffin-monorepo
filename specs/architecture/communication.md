# Communication Patterns

## Synchronous (REST)
- Used for operations requiring immediate feedback (e.g., login, balance check).
- **Standards**: Follow `specs/rules/api-guidelines.md`.

## Asynchronous (Event-Driven)
- Used for background processing, notifications, and cross-domain synchronization.
- **Broker**: RabbitMQ.
- **Standards**: Follow `specs/rules/event-conventions.md`.

## Data Exchange Formats
- **JSON**: Default for REST APIs and Event payloads.
- **Protobuf**: (Potential) for internal service-to-service communication if performance warrants.
