# Notification Module

Notification Module for Alsernet. Manages system notifications, user notifications, and notification preferences.

## Features

- **Notification Management** - Create, read, update, delete notifications
- **User Notifications** - Send notifications to users
- **Notification Preferences** - Allow users to manage notification preferences
- **API Endpoints** - REST API for notification management
- **Real-time Notifications** - Support for real-time notification delivery

## Routes

Manager Routes (`/manager/notifications/`):
- GET / - List all notifications
- GET /create - Create form
- POST /store - Store new notification
- GET /{id}/edit - Edit form
- POST /{id}/update - Update notification
- DELETE /{id} - Delete notification

API Routes (authenticated):
- GET /api/notifications - List user notifications
- POST /api/notifications/{id}/read - Mark as read
- POST /api/notifications/mark-all-read - Mark all as read

## Models

- **Notification** - Main notification model
- **Notifications** - Notification details model (if separate)

## Architecture

```
Modules/Notification/
├── app/
│   ├── Http/
│   │   ├── Controllers/Managers/
│   │   │   ├── NotificationController.php
│   │   │   └── NotificationsController.php
│   │   └── Requests/
│   ├── Models/
│   │   ├── Notification.php
│   │   └── Notifications.php (if applicable)
│   └── Providers/
│       └── NotificationServiceProvider.php
├── config/
│   └── notification.php
├── routes/
│   └── managers.php
├── resources/views/managers/
└── README.md
```

## License

Proprietary - Alsernet
