# MPBusinessHub - Laravel Backend

## Business Registration Feature

This repository contains the Laravel backend implementation for the MPBusinessHub application. Currently, it implements the business registration feature that works with the React frontend.

### Project Structure

The project follows a layered architecture with the following components:

- **Controllers**: Handle HTTP requests and responses
- **Services**: Contain business logic
- **Repositories**: Handle data access and persistence
- **Models**: Define database entities and relationships
- **Requests**: Validate incoming data

### Database Schema

The database includes the following tables:

- **users**: Stores user authentication information
- **businesses**: Stores business details linked to users

### Testing the Business Registration Feature

To test the business registration feature, you can use the following methods:

#### Running Unit Tests

The project includes comprehensive tests for all components of the business registration feature:

```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test --filter=BusinessRegistrationTest
```

#### Manual API Testing

You can test the API endpoint manually using tools like Postman or cURL:

```bash
# Example cURL request
curl -X POST http://localhost:8080/api/businesses/register \
  -H "Content-Type: application/json" \
  -d '{
    "businessName": "Test Business",
    "category": "Tourism",
    "district": "Mbombela",
    "description": "This is a test business description with more than fifty characters to pass validation.",
    "phone": "+27123456789",
    "email": "test@example.com",
    "website": "https://testbusiness.co.za",
    "address": "123 Test Street, Mbombela",
    "password": "password123"
  }'
```

### API Endpoint

#### POST /api/businesses/register

Registers a new business with a user account.

**Request Body:**

```json
{
  "businessName": "Test Business",
  "category": "Tourism",
  "district": "Mbombela",
  "description": "This is a test business description with more than fifty characters to pass validation.",
  "phone": "+27123456789",
  "email": "test@example.com",
  "website": "https://testbusiness.co.za",
  "address": "123 Test Street, Mbombela",
  "password": "password123"
}
```

**Successful Response (201 Created):**

```json
{
  "success": true,
  "message": "Business registered successfully",
  "data": {
    "token": "your_auth_token",
    "user_id": 1,
    "business_id": 1,
    "status": "pending"
  }
}
```

**Error Response (422 Validation Error):**

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "This email is already registered. Please use a different email or login."
    ]
  }
}
```

### Implementation Notes

- New businesses are created with a default status of "pending" which requires admin approval
- The API returns a Sanctum authentication token that should be used for subsequent authenticated requests
- Password is automatically hashed before storing in the database
- Email addresses must be unique across all users
