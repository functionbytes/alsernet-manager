# FAQ Module

Frequently Asked Questions (FAQ) module for Alsernet. Provides comprehensive FAQ management with categories and CRUD operations.

## Features

- **FAQ Management** - Create, read, update, delete FAQ entries
- **FAQ Categories** - Organize FAQs by categories
- **Search & Filter** - Find FAQs easily
- **Pagination** - Browse FAQs with pagination

## Routes

Manager Routes (`/manager/faqs/`):
- GET / - List all FAQs
- GET /create - Create form
- POST /store - Store new FAQ
- GET /edit/{uid} - Edit form
- POST /update - Update FAQ
- GET /destroy/{uid} - Delete FAQ

Manager Routes (`/manager/faqs/categories/`):
- GET / - List all categories
- GET /create - Create form
- POST /store - Store new category
- GET /edit/{uid} - Edit form
- POST /update - Update category
- GET /destroy/{uid} - Delete category

## Architecture

```
Modules/Faq/
├── app/
│   ├── Http/
│   │   ├── Controllers/Managers/
│   │   │   ├── FaqsController.php
│   │   │   └── CategoriesController.php
│   │   └── Requests/
│   ├── Models/Faq/
│   │   ├── Faq.php
│   │   └── FaqCategorie.php
│   └── Providers/
│       └── FaqServiceProvider.php
├── config/
│   └── faq.php
├── routes/
│   └── managers.php
├── resources/views/managers/
└── README.md
```

## License

Proprietary - Alsernet
