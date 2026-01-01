# Mail Module

Mail Configuration and Management module for Alsernet. Provides comprehensive email settings management including incoming/outgoing email configuration, mailers, templates, and email endpoints.

## Features

- **Email Settings** - Configure primary email settings
- **Incoming Email** - IMAP/POP email configuration
- **Outgoing Email** - SMTP email configuration
- **Mailers Management** - Configure multiple mailers
- **Email Templates** - Email template management
- **Email Endpoints** - Configure email webhooks and endpoints
- **Stage Email Actions** - Configure email actions and workflows

## Routes

Manager Routes (`/manager/settings/email/`):
- GET / - View email settings
- POST /update - Update email settings

Manager Routes (`/manager/settings/incoming-email/`):
- GET / - View incoming email settings
- POST /update - Update incoming email settings

Manager Routes (`/manager/settings/outgoing-email/`):
- GET / - View outgoing email settings
- POST /update - Update outgoing email settings

Manager Routes (`/manager/settings/stage-email-action/`):
- GET / - List email actions
- POST /store - Create email action
- POST /update - Update email action
- DELETE /{id} - Delete email action

## Architecture

```
Modules/Mail/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Managers/Settings/
│   │   │       ├── EmailSettingsController.php
│   │   │       ├── IncomingEmailSettingsController.php
│   │   │       ├── OutgoingEmailSettingsController.php
│   │   │       ├── StageEmailActionController.php
│   │   │       └── Mails/
│   │   └── Requests/
│   └── Providers/
│       └── MailServiceProvider.php
├── config/
│   └── mail.php
├── routes/
│   └── managers.php
├── resources/views/
│   └── managers/settings/
│       ├── mailers/
│       ├── email/
│       └── etc.
└── README.md
```

## License

Proprietary - Alsernet
