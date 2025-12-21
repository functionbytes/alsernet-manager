# Documentation Agent Capabilities

## Overview
The Documentation Agent specializes in generating and maintaining technical documentation for the Alsernet project. It uses the efficient Haiku model for fast, cost-effective documentation tasks.

## Capabilities (28 total)

### API Documentation (7)
1. Generate OpenAPI/Swagger specifications
2. Document REST endpoints with examples
3. Create request/response schemas
4. Document authentication flows
5. Generate API versioning docs
6. Create error code references
7. Build API changelog documentation

### Code Documentation (7)
1. Generate PHPDoc blocks for classes/methods
2. Create JSDoc comments for JavaScript
3. Document Eloquent models and relationships
4. Add inline comments for complex logic
5. Document Laravel service providers
6. Create trait and interface documentation
7. Generate enum documentation

### Architecture Documentation (6)
1. Create system architecture diagrams (Mermaid)
2. Document module dependencies
3. Map data flow diagrams
4. Document database schemas
5. Create sequence diagrams
6. Document event/listener flows

### Project Documentation (8)
1. Generate README files
2. Create installation guides
3. Write configuration documentation
4. Build troubleshooting guides
5. Create onboarding documentation
6. Document environment setup
7. Generate changelog entries
8. Create release notes

## Model Configuration

```json
{
  "model": "haiku",
  "reasoning": "Documentation tasks are well-suited for Haiku due to:",
  "benefits": [
    "Fast response times for iterative documentation",
    "Cost-effective for high-volume documentation tasks",
    "Sufficient capability for structured text generation",
    "Quick turnaround for doc updates"
  ]
}
```

## Usage Examples

### Generate API Documentation
```
"Document the DocumentController endpoints including request/response examples"
```

### Create PHPDoc Blocks
```
"Add PHPDoc blocks to all public methods in the CarrierService class"
```

### Architecture Diagram
```
"Create a Mermaid diagram showing the document upload workflow"
```

## Integration Points

- **Context7**: Fetches current Laravel/PHP documentation standards
- **Laravel Boost**: Uses `search-docs` for ecosystem conventions
- **Glob/Grep**: Finds undocumented code sections
