# MPBusinessHub Frontend

## Overview

This is the React frontend for the Mpumalanga Business Hub platform. It provides a modern, responsive user interface for business registration, directory browsing, and business profile management. The frontend is built with React and Vite, styled with Tailwind CSS, and communicates with the Laravel backend via RESTful API calls.

## Features

### Public Features

- **Home Page**: Introduction to the platform with featured businesses
- **Business Directory**: Searchable and filterable listing of businesses
- **Business Details**: Detailed view of individual business profiles
- **Registration**: Business registration form with validation
- **Login**: User authentication with token-based sessions

### Authenticated Features

- **Dashboard Home**: Overview with key metrics and quick links
- **Business Profile Management**: Edit business details and contact information
- **Tier Management**: View current tier benefits and upgrade options
- **Product Catalog**: Manage products (Silver and Gold tiers)
- **Advertisement Management**: Create and manage advertisements (Silver and Gold tiers)

## Component Structure

### Pages

- `Home.jsx`: Landing page with introduction and featured businesses
- `BusinessDirectory.jsx`: Directory listing with search and filter functionality
- `BusinessDetails.jsx`: Detailed view of a specific business
- `Login.jsx`: User login form
- `Register.jsx`: Business registration form
- `Dashboard.jsx`: Main dashboard container with navigation
- `NotFound.jsx`: 404 error page

### Components

#### Core Components
- `Header.jsx`: Navigation header with responsive menu
- `Footer.jsx`: Site footer with links and information
- `BusinessCard.jsx`: Card display for businesses in the directory

#### Dashboard Components
- `DashboardHome.jsx`: Dashboard overview with statistics
- `BusinessProfile.jsx`: Edit business profile information
- `AdvertsManagement.jsx`: Create and manage advertisements
- `ManageProducts.jsx`: Add and edit product listings
- `PaymentHistory.jsx`: View payment records
- `UpgradePlan.jsx`: Upgrade subscription tier

#### UI Components
- `IconBar.jsx`: Social media and contact icons
- `LazyLoadImage.jsx`: Image component with lazy loading

## State Management

The application uses React Context API and hooks for state management. Key contexts include:

- **AuthContext**: Manages user authentication state
- **BusinessContext**: Provides business data across components
- **NotificationContext**: Handles application notifications and alerts

## API Integration

The frontend communicates with the Laravel backend using Axios for HTTP requests. API calls are organized in the `services` directory:

```javascript
// Example API call for business registration
const registerBusiness = async (businessData) => {
  try {
    const response = await axios.post('/api/businesses/register', businessData);
    return response.data;
  } catch (error) {
    throw error.response?.data || error;
  }
};
```

## Recent Improvements

1. **Business Directory**
   - Fixed data fetching to use the correct API endpoint
   - Added proper error handling and loading states
   - Implemented filtering by category and district

2. **Dashboard**
   - Updated to fetch real business data instead of mock data
   - Implemented profile editing functionality
   - Fixed "View Public Profile" link to use the actual business ID

3. **Business Details**
   - Added null checks to prevent errors when data is missing
   - Improved error handling for API requests
   - Enhanced display of business information based on tier level

## Installation

1. Clone the repository
2. Navigate to the client directory:
   ```bash
   cd client
   ```
3. Install dependencies:
   ```bash
   npm install
   ```
4. Start the development server:
   ```bash
   npm run dev
   ```
5. The application will be available at `http://localhost:3000`

## Building for Production

```bash
npm run build
```

This will create a production-ready build in the `dist` directory.

## Testing

```bash
npm test
```

## Styling

The application uses Tailwind CSS for styling with a custom configuration defined in `tailwind.config.js`. The main color scheme is black and white with accent colors for specific elements.

```javascript
// tailwind.config.js example
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: '#000000',
        secondary: '#ffffff',
        accent: '#f3f4f6',
      },
    },
  },
  // ...
};
```
