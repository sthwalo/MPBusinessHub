# MPBusinessHub Backend

## Overview

This is the Laravel backend for the Mpumalanga Business Hub platform. It provides a robust API for business registration, authentication, and business directory management. The backend follows the repository pattern with a service layer for better separation of concerns and testability.

## Architecture

### Core Components

1. **Controllers**
   - `AuthController`: Handles user authentication (login, logout)
   - `BusinessController`: Manages business data (listing, details, updates)
   - `BusinessRegistrationController`: Handles business registration process

2. **Models**
   - `User`: Represents user accounts with authentication details
   - `Business`: Stores business information including tier level and contact details

3. **Repositories**
   - `UserRepository`: Data access layer for user-related operations
   - `BusinessRepository`: Data access layer for business-related operations

4. **Services**
   - `BusinessRegistrationService`: Business logic for the registration process

5. **Requests**
   - `BusinessRegistrationRequest`: Validation rules for business registration

## API Endpoints

### Public Endpoints

#### Authentication
- `POST /api/auth/login` - Authenticate user and get token
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```

#### Business Registration
- `POST /api/businesses/register` - Register a new business
  ```json
  {
    "name": "Business Name",
    "email": "business@example.com",
    "password": "password",
    "password_confirmation": "password",
    "business_type": "Retail",
    "category": "Food & Beverage",
    "district": "Ehlanzeni",
    "description": "Business description"
  }
  ```

#### Business Directory
- `GET /api/businesses/check` - Get businesses for the directory
  - Query parameters: `category`, `district`

- `GET /api/businesses/{id}` - Get details for a specific business

### Protected Endpoints (require authentication)

#### Authentication
- `POST /api/auth/logout` - Logout and invalidate token

#### Business Management
- `GET /api/business/details` - Get authenticated user's business details
- `PUT /api/business/update` - Update authenticated user's business profile
  ```json
  {
    "name": "Updated Business Name",
    "description": "Updated description",
    "website": "https://example.com",
    "contact": {
      "email": "contact@example.com",
      "phone": "+27123456789",
      "whatsapp": "+27123456789"
    },
    "location": {
      "address": "123 Main St",
      "city": "Nelspruit",
      "district": "Ehlanzeni",
      "province": "Mpumalanga",
      "coordinates": {
        "lat": -25.465,
        "lng": 30.985
      }
    }
  }
  ```

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User's name
- `email` - User's email (unique)
- `password` - Hashed password
- `remember_token` - Token for "remember me" functionality
- `timestamps` - Created and updated timestamps

### Businesses Table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `name` - Business name
- `description` - Business description
- `business_type` - Type of business
- `category` - Business category
- `district` - Geographic district
- `tier` - Subscription tier (basic, bronze, silver, gold)
- `website` - Business website URL
- `contact` - JSON object with contact information
- `location` - JSON object with location details
- `operating_hours` - JSON object with operating hours
- `status` - Business status (active, pending, suspended)
- `timestamps` - Created and updated timestamps

## Authentication

The backend uses Laravel Sanctum for API token authentication. Tokens are created upon successful login and must be included in the Authorization header for protected routes:

```
Authorization: Bearer {token}
```

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Create and configure the .env file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Configure database connection in .env
6. Run migrations:
   ```bash
   php artisan migrate
   ```
7. Start the development server:
   ```bash
   php artisan serve
   ```

## Testing

The backend includes comprehensive unit and feature tests for the business registration process:

```bash
php artisan test
```

## Recent Improvements

1. **API Endpoints**
   - Added `/api/businesses/check` endpoint to fix 500 error in business directory
   - Implemented `/api/businesses/{id}` endpoint for viewing specific business details
   - Created robust error handling for all API responses

2. **Authentication**
   - Implemented Laravel Sanctum for secure API token authentication
   - Created AuthController with login and logout methods
   - Added middleware protection for sensitive routes

3. **Business Management**
   - Added BusinessController with methods for retrieving and updating business profiles
   - Implemented validation for all business data updates
   - Created repository pattern for better code organization

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
