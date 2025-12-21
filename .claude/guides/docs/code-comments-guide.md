# Code Comments Guide

## PHPDoc Standards

### Class Documentation

```php
/**
 * Handles document processing and validation.
 *
 * This service manages the lifecycle of documents including
 * upload, validation, approval, and archival.
 *
 * @package App\Services\Documents
 * @author Alsernet Team
 */
class DocumentService
{
}
```

### Method Documentation

```php
/**
 * Process and validate an uploaded document.
 *
 * Validates the document against configured rules,
 * generates a preview, and stores metadata.
 *
 * @param Document $document The document to process
 * @param array<string, mixed> $options Processing options
 * @return ProcessingResult The result including status and errors
 *
 * @throws DocumentValidationException When validation fails
 * @throws StorageException When file cannot be stored
 *
 * @example
 * $result = $service->process($document, ['generateThumbnail' => true]);
 * if ($result->isSuccessful()) {
 *     // Document processed successfully
 * }
 */
public function process(Document $document, array $options = []): ProcessingResult
{
}
```

### Property Documentation

```php
/**
 * @var Collection<int, Document> Cached documents for the current request
 */
protected Collection $cachedDocuments;

/**
 * @var array{host: string, port: int, timeout: int} Storage configuration
 */
protected array $storageConfig;
```

## JSDoc Standards

### Function Documentation

```javascript
/**
 * Uploads a file to the document storage.
 *
 * @param {File} file - The file to upload
 * @param {Object} options - Upload options
 * @param {boolean} [options.generatePreview=true] - Generate preview image
 * @param {function(number): void} [options.onProgress] - Progress callback
 * @returns {Promise<UploadResult>} The upload result
 * @throws {ValidationError} When file type is not allowed
 *
 * @example
 * const result = await uploadFile(file, { onProgress: (p) => console.log(p) });
 */
async function uploadFile(file, options = {}) {
}
```

### Class Documentation

```javascript
/**
 * Manages document state and interactions.
 *
 * @class DocumentManager
 * @property {Document[]} documents - Loaded documents
 * @property {boolean} isLoading - Loading state
 */
class DocumentManager {
}
```

## When to Comment

**DO comment:**
- Complex algorithms or business logic
- Non-obvious workarounds or edge cases
- API contracts and interfaces
- Configuration options

**DON'T comment:**
- Obvious code (`i++; // increment i`)
- Duplicating method names
- Commented-out code (delete it)
