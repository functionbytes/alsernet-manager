# API Documentation Guide

## Overview

This guide covers best practices for documenting REST APIs in Alsernet.

## OpenAPI/Swagger Structure

```yaml
openapi: 3.0.3
info:
  title: Alsernet API
  version: 1.0.0
paths:
  /api/v1/documents:
    get:
      summary: List documents
      parameters:
        - name: status
          in: query
          schema:
            type: string
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/DocumentList'
```

## Endpoint Documentation Pattern

### Required Elements
1. **HTTP Method & Path**: `GET /api/v1/documents/{id}`
2. **Description**: What the endpoint does
3. **Parameters**: Path, query, body parameters
4. **Request Example**: Sample request body/headers
5. **Response Example**: Sample successful response
6. **Error Codes**: Possible error responses

### Example

```markdown
## Get Document

Retrieves a single document by ID.

**Endpoint:** `GET /api/v1/documents/{id}`

**Authentication:** Bearer Token (required)

**Parameters:**
| Name | Type | Location | Required | Description |
|------|------|----------|----------|-------------|
| id | string | path | yes | Document UID |

**Response (200):**
```json
{
  "data": {
    "id": "abc123",
    "name": "Invoice.pdf",
    "status": "approved"
  }
}
```

**Errors:**
- `404` - Document not found
- `401` - Unauthorized
```

## Laravel Route Documentation

Document routes in `routes/api.php`:

```php
/**
 * Document Management API
 *
 * @group Documents
 */
Route::prefix('documents')->group(function () {
    /**
     * List all documents
     *
     * @queryParam status string Filter by status. Example: approved
     * @queryParam page int Page number. Example: 1
     */
    Route::get('/', [DocumentController::class, 'index']);
});
```
