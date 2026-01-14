# Compliance Module

GDPR cookie consent, privacy policies, and user consent management.

## Overview

The Compliance module provides cookie consent management and GDPR-compliant tools for handling user privacy preferences and legal compliance requirements.

## Features

- **Cookie Consent Banner** - GDPR-compliant cookie consent UI
- **Cookie Categories** - Manage different types of cookies (essential, analytics, marketing)
- **User Preferences** - Store and retrieve user cookie preferences
- **Cookie Policy** - Link to privacy and cookie policy pages
- **Consent Tracking** - Track which users have consented to cookie usage

## Installation

The module is automatically included via Composer's merge-plugin system.

Dependencies:
- `spatie/laravel-cookie-consent` (^3.2|^4.0)

## Configuration

Publish the configuration:

```bash
php artisan vendor:publish --tag=compliance-config
```

The configuration file is located at `config/compliance.php`.

## Usage

### Display Cookie Consent Banner

Add to your layout blade template:

```blade
@if (config('compliance.enabled'))
    <x:cookie-consent></x:cookie-consent>
@endif
```

### Check User Consent

```php
use Spatie\CookieConsent\Facades\CookieConsent;

if (CookieConsent::hasConsented('analytics')) {
    // User consented to analytics cookies
}
```

### Set Cookie Preferences

```php
CookieConsent::accept('marketing');
CookieConsent::decline('analytics');
```

## Cookie Categories

Configure cookie categories in `config/compliance.php`:

```php
'categories' => [
    'essential' => [
        'required' => true,  // Cannot be declined
        'description' => 'Essential for functionality',
    ],
    'analytics' => [
        'required' => false,
        'description' => 'Analytics and tracking',
    ],
    'marketing' => [
        'required' => false,
        'description' => 'Marketing and advertising',
    ],
],
```

## GDPR Compliance

This module helps comply with GDPR and other privacy regulations by:

- Obtaining explicit user consent before using tracking cookies
- Providing clear information about cookie usage
- Allowing users to manage their preferences
- Storing consent preferences securely

## References

- [Spatie Laravel Cookie Consent](https://github.com/spatie/laravel-cookie-consent)
- [GDPR Cookie Compliance](https://gdpr-info.eu/)
- [Privacy by Design](https://www.iab.com/articles/gdpr/)

## Authors

Alsernet Development Team
