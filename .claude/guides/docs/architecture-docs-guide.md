# Architecture Documentation Guide

## Mermaid Diagrams

### System Architecture

```mermaid
graph TB
    subgraph Frontend
        UI[Web UI]
        API_Client[API Client]
    end

    subgraph Backend
        Controller[Controllers]
        Service[Services]
        Model[Models]
    end

    subgraph Storage
        DB[(PostgreSQL)]
        Redis[(Redis)]
        S3[File Storage]
    end

    UI --> API_Client
    API_Client --> Controller
    Controller --> Service
    Service --> Model
    Model --> DB
    Service --> Redis
    Service --> S3
```

### Sequence Diagrams

```mermaid
sequenceDiagram
    participant U as User
    participant C as Controller
    participant S as Service
    participant D as Database

    U->>C: Upload Document
    C->>S: validateAndStore()
    S->>D: Save metadata
    D-->>S: Document ID
    S->>S: Generate preview
    S-->>C: ProcessingResult
    C-->>U: Success response
```

### Entity Relationships

```mermaid
erDiagram
    DOCUMENT ||--o{ DOCUMENT_NOTE : has
    DOCUMENT ||--|| DOCUMENT_STATUS : has
    DOCUMENT }|--|| CUSTOMER : belongs_to
    CUSTOMER ||--o{ REQUEST_DOCUMENT : requests
```

## Module Documentation Template

```markdown
# [Module Name]

## Overview
Brief description of the module's purpose.

## Components

### Models
- `Document` - Core document entity
- `DocumentStatus` - Status tracking

### Services
- `DocumentService` - Business logic
- `ValidationService` - Document validation

### Controllers
- `DocumentController` - API endpoints
- `AdminDocumentController` - Admin operations

## Data Flow

[Mermaid diagram here]

## Configuration

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `documents.max_size` | int | 10MB | Maximum file size |

## Events

| Event | When | Payload |
|-------|------|---------|
| `DocumentUploaded` | After upload | `Document $document` |

## Dependencies
- `spatie/laravel-medialibrary` for file storage
- `intervention/image` for thumbnails
```

## File Structure Documentation

```markdown
## Directory Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Documents/           # Document-related controllers
├── Models/
│   └── Document/               # Document models
├── Services/
│   └── Documents/              # Document business logic
└── Events/
    └── Document/               # Document events
```
```
