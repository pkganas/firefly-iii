# API-Only Authentication for Firefly III

This document explains how to use Firefly III purely via API calls without requiring the web interface for user registration or authentication.

## Overview

The modifications made to Firefly III enable complete API-only usage by:

1. **User Registration via API**: Users can register directly through the API without needing to visit the web interface
2. **Proper User Setup**: API-created users get the same setup as web-created users (user groups, roles, exchange rates, etc.)
3. **OAuth Token Authentication**: Users can authenticate and get access tokens via API calls

## API Endpoints

### 1. User Registration

**Endpoint**: `POST /api/v1/register`

**Description**: Register a new user account via API.

**Request Body**:
```json
{
    "email": "user@example.com",
    "password": "securepassword123",
    "password_confirmation": "securepassword123"
}
```

**Response**: Returns the created user object with all necessary setup completed.

**Example**:
```bash
curl -X POST http://your-firefly-instance.com/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/vnd.api+json" \
  -d '{
    "email": "user@example.com",
    "password": "securepassword123",
    "password_confirmation": "securepassword123"
  }'
```

### 2. OAuth Token Authentication

**Endpoint**: `POST /api/v1/oauth/token`

**Description**: Get an OAuth access token for API authentication.

**Request Body**:
```json
{
    "grant_type": "password",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret",
    "username": "user@example.com",
    "password": "securepassword123",
    "scope": "*"
}
```

**Response**: Returns access token and refresh token.

**Example**:
```bash
curl -X POST http://your-firefly-instance.com/api/v1/oauth/token \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "grant_type": "password",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret",
    "username": "user@example.com",
    "password": "securepassword123",
    "scope": "*"
  }'
```

### 3. Using Access Tokens

Once you have an access token, use it in the `Authorization` header for all API requests:

```bash
curl -X GET http://your-firefly-instance.com/api/v1/accounts \
  -H "Authorization: Bearer your-access-token" \
  -H "Accept: application/vnd.api+json"
```

## Setup Requirements

### 1. OAuth Client Setup

Before users can authenticate, you need to set up OAuth clients. This can be done via the web interface or by running the following command:

```bash
php artisan passport:client --personal
```

### 2. Configuration

Ensure the following configuration is set in your `.env` file:

```env
# Enable registration
ALLOW_REGISTRATION=true

# Use web authentication guard
AUTH_GUARD=web

# OAuth settings
PASSPORT_PRIVATE_KEY=
PASSPORT_PUBLIC_KEY=
```

## Complete API-Only Workflow

1. **Register a user**:
   ```bash
   curl -X POST http://your-firefly-instance.com/api/v1/register \
     -H "Content-Type: application/json" \
     -H "Accept: application/vnd.api+json" \
     -d '{"email": "user@example.com", "password": "password123", "password_confirmation": "password123"}'
   ```

2. **Get an access token**:
   ```bash
   curl -X POST http://your-firefly-instance.com/api/v1/oauth/token \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"grant_type": "password", "client_id": "your-client-id", "client_secret": "your-client-secret", "username": "user@example.com", "password": "password123", "scope": "*"}'
   ```

3. **Use the API with the token**:
   ```bash
   curl -X GET http://your-firefly-instance.com/api/v1/accounts \
     -H "Authorization: Bearer your-access-token" \
     -H "Accept: application/vnd.api+json"
   ```

## What Was Modified

The following files were modified to enable API-only authentication:

1. **`app/Api/V1/Controllers/System/UserController.php`**: Added `RegisteredUser` event firing to ensure proper user setup
2. **`app/Repositories/User/UserRepository.php`**: Modified to handle password input for API-created users
3. **`app/Api/V1/Requests/System/UserStoreRequest.php`**: Added password field support
4. **`routes/api.php`**: Added registration and OAuth token endpoints
5. **New files created**:
   - `app/Api/V1/Controllers/System/RegisterController.php`: Handles user registration via API
   - `app/Api/V1/Controllers/System/OAuthController.php`: Handles OAuth token requests via API
   - `app/Api/V1/Requests/System/ApiUserRegistrationRequest.php`: Validation for API registration
   - `app/Api/V1/Requests/System/OAuthTokenRequest.php`: Validation for OAuth token requests

## Benefits

- **Complete API-only usage**: No need to visit the web interface for user registration or authentication
- **Proper user setup**: API-created users get the same setup as web-created users
- **OAuth compliance**: Uses standard OAuth 2.0 flow for authentication
- **Backward compatibility**: Existing web interface functionality remains unchanged

## Security Considerations

- Ensure OAuth clients are properly configured
- Use HTTPS in production
- Implement proper rate limiting
- Consider implementing additional validation for registration if needed
- Monitor for suspicious registration patterns

## Troubleshooting

1. **Registration fails**: Check that `ALLOW_REGISTRATION=true` in your configuration
2. **Token requests fail**: Ensure OAuth clients are properly set up
3. **User setup incomplete**: Verify that the `RegisteredUser` event is being fired and handled properly
4. **Authentication fails**: Check that the access token is valid and not expired
