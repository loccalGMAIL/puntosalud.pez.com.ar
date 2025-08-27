# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PuntoSalud2 is a Laravel 12 healthcare management application for medical appointments, professional scheduling, and patient management. It uses PHP 8.2, Vite for frontend build, and TailwindCSS for styling.

## Key Commands

### Development
- `composer dev` - Start all services (server, queue worker, Vite dev)
- `php artisan serve` - Start Laravel development server
- `npm run dev` - Start Vite development server
- `npm run build` - Build frontend assets for production

### Testing & Quality
- `composer test` or `php artisan test` - Run PHPUnit tests
- `./vendor/bin/pint` - Run Laravel Pint (code formatter)
- `php artisan config:clear` - Clear configuration cache

### Database
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Fresh migration with seeders
- `php artisan db:seed` - Run database seeders

## Architecture Overview

### Core Domain Models
- **Appointment**: Central entity managing patient appointments with professionals
- **Patient**: Patient information and health insurance details
- **Professional**: Healthcare providers with specialties and commission structures
- **Office**: Physical locations where appointments take place
- **Specialty**: Medical specialties for professionals

### Financial System
- **Payment**: Handles individual payments and package deals
- **PaymentAppointment**: Links payments to specific appointments
- **ProfessionalLiquidation**: Commission calculations and payouts
- **LiquidationDetail**: Detailed breakdown of professional earnings
- **CashMovement**: Tracks all cash flow in/out of the system

### Scheduling System
- **ProfessionalSchedule**: Defines working hours per professional per day
- **ScheduleException**: Handles holidays and special schedule changes
- **AppointmentSetting**: Duration and pricing configuration per professional

### Key Business Logic
- Appointments track estimated vs final amounts
- Professional commission percentages are configurable
- Payment system supports both individual sessions and packages
- Schedule conflicts are prevented through model methods
- Comprehensive scopes for filtering appointments by status/date/professional

### Frontend Stack
- Vite build system with Laravel plugin
- TailwindCSS 4.0 for styling
- Frontend assets in `resources/css/app.css` and `resources/js/app.js`

### Database Design
All models use Eloquent relationships with comprehensive foreign key constraints. Migration files follow Laravel naming conventions with timestamps. Seeders populate initial data for all entities.

## Development Notes
- Models include extensive scopes, accessors, and helper methods
- Spanish comments and method names used throughout (e.g., "Relaciones" for relationships)
- Money values stored as decimals with 2 decimal places
- Status-based workflows for appointments (scheduled → attended/absent/cancelled)
- Commission calculations built into Professional model