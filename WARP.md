# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with Filament 4 admin panel for spare parts inventory management. The application is primarily in Arabic and manages spare parts (المهمات) with categories, types, statuses, and locations. It uses Filament Shield for role-based permissions.

## Key Architecture

### Core Domain Models
- **SparePart**: Main entity with relationships to City, SparePartType, and SparePartCategory
- **SparePartStatusEnum**: Enum with Arabic labels (New, Used-Good, Used-Needs-Maintenance, Maintained)
- **City**, **SparePartType**, **SparePartCategory**: Supporting entities
- **Document/DocumentType**: Document management system
- **User**: Authentication with role-based permissions

### Filament Structure
- Admin panel at `/admin` with Arabic branding "بيانات بيت الوطن"
- Resources follow pattern: `app/Filament/Resources/{Entity}/{EntityResource.php}`
- Form schemas and table configurations are separated into dedicated classes
- Uses Filament Shield for permissions
- Custom avatar provider (BoringAvatarsProvider)
- SPA mode enabled

## Development Commands

### Setup and Installation
```bash
# Copy environment file and generate key
php artisan key:generate

# Install dependencies
composer install
npm install

# Database setup
php artisan migrate
php artisan db:seed
```

### Development Server
```bash
# Full development environment (server + queue + logs + vite)
composer dev

# Individual services
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run Pest tests directly
./vendor/bin/pest
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run Pint with specific configuration
./vendor/bin/pint --config pint.json
```

### Asset Building
```bash
# Development build
npm run dev

# Production build
npm run build
```

### Database Management
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create model with migration
php artisan make:model ModelName -m
```

### Filament Commands
```bash
# Create Filament resource
php artisan make:filament-resource ModelName

# Create Filament page
php artisan make:filament-page PageName

# Upgrade Filament (runs automatically after composer install)
php artisan filament:upgrade

# Generate permissions for Shield
php artisan shield:generate
```

## Code Architecture Patterns

### Filament Resources
- Resources are organized by entity in subdirectories
- Form schemas are extracted to separate classes (e.g., `SparePartForm`)
- Table configurations are extracted to separate classes (e.g., `SparePartsTable`)
- Use Arabic labels for navigation and model names

### Enums
- Status enums include both English values and Arabic labels
- Implement `labels()` static method for dropdown options
- Implement `label()` instance method for display

### Database Design
- Foreign key relationships use `constrained()` for automatic references
- Decimal fields for costs use precision 12,2
- Enum values stored as strings in database
- Uses standard Laravel timestamp fields

### Testing Configuration
- Pest PHP as testing framework
- SQLite in-memory database for tests
- Separate Unit and Feature test suites
- Array drivers for cache/mail/queue in testing

## File Organization

### Key Directories
- `app/Filament/Resources/` - Filament admin resources
- `app/Models/` - Eloquent models
- `app/Enums/` - Project enums
- `database/migrations/` - Database schema
- `resources/css/filament/admin/` - Custom Filament theme
- `tests/` - Pest test files

### Configuration
- Uses Vite for asset bundling with Tailwind CSS
- Custom Filament theme with Cairo font
- SQLite database for development (configurable)
- Queue system configured but using sync driver by default

## Important Notes

- The application interface is primarily in Arabic
- Spare parts are referred to as "المهمات" (equipment/supplies)
- Uses Cairo font family for proper Arabic text rendering
- Filament Shield handles role-based access control
- SPA mode is enabled for better user experience
- Custom boring avatars provider for user profiles

<citations>
<document>
<document_type>WARP_DOCUMENTATION</document_type>
<document_id>getting-started/quickstart-guide/coding-in-warp</document_id>
</document>
</citations>