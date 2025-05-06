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
- **Social Media Integration**: Display and management of business social media profiles with tier-based allocation
- **WhatsApp Integration**: Direct WhatsApp contact button for easier customer communication

## Technology Stack

### Frontend
- **Framework**: React 18 with Vite
- **Styling**: Tailwind CSS with custom brand theming
- **State Management**: React Hooks and Context API
- **Routing**: React Router v6
- **HTTP Client**: Axios for API communication
- **Icons**: React Icons for social media and UI elements

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
├── server/           # Laravel backend (active)
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
| **Monthly Adverts**        | 0           | 1           | 2           | 4           |
| **Social Media Links**     | ❌          | ✅           | ✅           | ✅          |
| **Social Media Features**  | 0           | 0           | 1           | 2           |

**Annual Pricing**: Bronze: R2,000/year, Silver: R5,000/year, Gold: R10,000/year

## Social Media Features

The platform includes comprehensive social media integration:

- **Social Media Links**: Businesses can add links to their social media profiles (Facebook, Instagram, Twitter, LinkedIn, YouTube, TikTok)
- **Social Media Management**: Dashboard interface for managing social media links
- **Tier-Based Allocation**: 
  - Bronze: Display social media links
  - Silver: Display links + 1 social media feature per month
  - Gold: Display links + 2 social media features per month
- **WhatsApp Integration**: Direct WhatsApp contact button using the business phone number

## Installation and Setup

### Prerequisites
- Node.js (>= 14.x)
- PHP 8.1 or higher
- Composer
- PostgreSQL

### Backend Setup (Laravel)

1. Navigate to the server directory:
   ```bash
   cd server
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
cd server
php artisan test
```

### Frontend Tests
```bash
cd client
npm test
```

## Recent Improvements

See the [PROGRESS_REPORT.md](./PROGRESS_REPORT.md) file for a detailed overview of recent improvements and current project status.