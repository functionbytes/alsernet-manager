# Documentation Agent - Quick Start Guide

## When to Use

Use the Documentation Agent when you need to:
- Generate or update technical documentation
- Add PHPDoc/JSDoc comments to code
- Create API documentation
- Build architecture diagrams
- Write README files or guides

## Basic Usage

### Document a Controller
```
"Document the OrderController with PHPDoc blocks and endpoint descriptions"
```

### Generate API Docs
```
"Create OpenAPI documentation for the /api/v1/documents endpoints"
```

### Create a README
```
"Generate a README for the Returns module explaining its purpose and usage"
```

## Output Locations

| Type | Location |
|------|----------|
| Backend docs | `docs/backend/` |
| Frontend docs | `docs/frontend/` |
| API specs | `docs/api/` |
| Guides | `docs/guides/` |

## Tips

1. **Be specific**: Mention the exact file, class, or module to document
2. **Provide context**: Explain the intended audience (developers, users, etc.)
3. **Request format**: Specify Markdown, PHPDoc, JSDoc, or OpenAPI as needed
4. **Include examples**: Ask for code examples when relevant

## Model: Haiku

This agent uses Claude Haiku for:
- Fast iteration on documentation drafts
- Cost-effective bulk documentation
- Quick updates and fixes
