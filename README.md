# Mpumalanga Business Hub

A web platform connecting businesses in Mpumalanga Province with potential customers through a comprehensive business directory and resource center.

## Overview

The Mpumalanga Business Hub is designed to promote economic growth in the region by providing businesses with online visibility and connecting them with customers. The platform features a tiered membership system with increasing benefits based on subscription level.

## Features

- **User Registration & Authentication**: Secure token-based authentication with Laravel Sanctum
- **Business Profiles**: Comprehensive business information including contact details, operating hours, and descriptions
- **Business Directory**: Searchable and filterable listing of local businesses by category and district
- **Business Dashboard**: Statistics and profile management for business owners
- **Tiered Memberships**: Different packages with varying benefits (Basic, Bronze, Silver, Gold)

## Technology Stack

### Frontend
- **Framework**: React 18 with Vite
- **Styling**: Tailwind CSS with custom brand theming
- **State Management**: React Hooks and Context API
- **Routing**: React Router v6
- **HTTP Client**: Axios for API communication

### Backend
- **Framework**: Laravel 10
- **Language**: PHP 8.2
- **Database**: PostgreSQL
- **Authentication**: Laravel Sanctum for API token authentication
- **Architecture**: Repository pattern with service layer
- **Testing**: PHPUnit for unit and feature tests

## Project Structure

The project follows a client-server architecture with separate directories for the frontend and backend:

```
MPBusinessHub/
├── client/               # React frontend
├── server-new/           # Laravel backend (active)
├── server/               # Original server files (reference only)
└── PROGRESS_REPORT.md    # Detailed progress report
```

## Membership Tiers

| Feature                    | Basic (Free) | Bronze (R200) | Silver (R500) | Gold (R1000) |
|----------------------------|-------------|--------------|--------------|-------------|
| **Business Name Listing**  | ✅          | ✅           | ✅           | ✅          |
| **Area of Operation**      | ✅          | ✅           | ✅           | ✅          |
| **Website Link**           | ❌          | ✅           | ✅           | ✅          |
| **WhatsApp Number**        | ❌          | ✅           | ✅           | ✅          |
| **Email Contact**          | ❌          | ❌           | ✅           | ✅          |
| **Star Ratings**           | ❌          | ✅           | ✅           | ✅          |
| **Product Catalog**        | ❌          | ❌           | ✅           | ✅          |
| **Monthly Adverts**        | 0           | 0            | 1            | 4           |
| **Social Media Feature**   | ❌          | ❌           | ❌           | 1/month     |

**Annual Pricing**: Bronze: R2,000/year, Silver: R5,000/year, Gold: R10,000/year

## Installation and Setup

### Prerequisites
- Node.js (>= 14.x)
- PHP 8.1 or higher
- Composer
- PostgreSQL

### Backend Setup (Laravel)

1. Navigate to the server directory:
   ```bash
   cd server-new
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Create a copy of the environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate an application key:
   ```bash
   php artisan key:generate
   ```

5. Configure your database in the .env file:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=mpbusinesshub
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. Run database migrations:
   ```bash
   php artisan migrate
   ```

7. Start the Laravel development server:
   ```bash
   php artisan serve --port=8080
   ```

### Frontend Setup (React)

1. Navigate to the client directory:
   ```bash
   cd client
   ```

2. Install JavaScript dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm run dev
   ```

4. The application will be available at `http://localhost:3000`

## API Endpoints

### Public Endpoints
- `POST /api/businesses/register` - Register a new business
- `GET /api/businesses` - Get all businesses with optional filtering
- `GET /api/businesses/check` - Get businesses for the directory
- `GET /api/businesses/{id}` - Get details for a specific business
- `POST /api/auth/login` - Authenticate user and get token

### Protected Endpoints (require authentication)
- `POST /api/auth/logout` - Logout and invalidate token
- `GET /api/business/details` - Get authenticated user's business details
- `PUT /api/business/update` - Update authenticated user's business profile

## Testing

### Backend Tests
```bash
cd server-new
php artisan test
```

### Frontend Tests
```bash
cd client
npm test
```

## Recent Improvements

See the [PROGRESS_REPORT.md](./PROGRESS_REPORT.md) file for a detailed overview of recent improvements and current project status.
- PHP (>= 8.1)
- PostgreSQL (>= 13.0)
- Composer

### Installation

1. Clone the repository
```bash
git clone <repository-url>
cd MPBH
```

2. Install backend dependencies
```bash
cd server
composer install
```

3. Configure environment variables
```bash
cp .env.example .env
# Edit .env with your database credentials
```

4. Set up the database
```bash
php setup-database.php
```

5. Install frontend dependencies
```bash
cd ../client
npm install
```

### Running the Development Server

1. Start the PHP backend server
```bash
cd server
php -S localhost:8080 -t public
```

2. Start the frontend development server
```bash
cd client
npm run dev
```

3. Access the application at http://localhost:3000

## API Endpoints

The API documentation is available in the [docs/api-reference.md](./docs/api-reference.md) file.

## Deployment

Deployment instructions for Afrihost shared hosting are available in the [docs/deployment.md](./docs/deployment.md) file.

## Documentation

Comprehensive documentation is available in the [docs](./docs) directory:

- [Project Overview](./docs/project-overview.md)
- [API Reference](./docs/api-reference.md)
- [Database Schema](./docs/database-schema.md)
- [Deployment Guide](./docs/deployment.md)
- [Membership Tiers](./docs/membership-tiers.md)

Frontend:
User Form → React State → Axios POST → Backend API

Backend:
API Route → Middleware → AuthController → User Model → Database

Response:
Database → User Model → AuthController → JSON Response → Frontend