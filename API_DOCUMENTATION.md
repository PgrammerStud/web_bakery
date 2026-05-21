# Web Bakery - Complete API Documentation

## Table of Contents
1. [Authentication Endpoints](#authentication-endpoints)
2. [Product Endpoints](#product-endpoints)
3. [Cart Endpoints](#cart-endpoints)
4. [Order Endpoints](#order-endpoints)
5. [Payment Endpoints](#payment-endpoints)
6. [User Management Endpoints](#user-management-endpoints)
7. [Error Handling](#error-handling)
8. [Authentication Methods](#authentication-methods)

---

## Authentication Endpoints

### 1. User Login
**Endpoint:** `POST /api/login`

**Authentication:** JWT Bearer Token (Header) or Form Login

**Request Headers:**
```json
{
  "Content-Type": "application/json",
  "Authorization": "Bearer <jwt_token>"
}
```

**Request Body:**
```json
{
  "username": "john_doe",
  "password": "secure_password123"
}
```

**Success Response (200):**
```json
{
  "user": "john_doe",
  "roles": ["ROLE_USER"],
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Error Response (401):**
```json
{
  "message": "missing credentials"
}
```

---

### 2. User Registration
**Endpoint:** `POST /api/register`

**Authentication:** None (Public)

**Request Body:**
```json
{
  "username": "new_user",
  "email": "user@example.com",
  "password": "secure_password123",
  "firstname": "John",
  "lastname": "Doe"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful. Please verify your email.",
  "user": {
    "id": 123,
    "username": "new_user",
    "email": "user@example.com"
  }
}
```

**Error Responses:**

**Validation Error (400):**
```json
{
  "success": false,
  "message": "Username must be at least 3 characters long"
}
```

**Duplicate Email (409):**
```json
{
  "success": false,
  "message": "Email already registered"
}
```

**Duplicate Username (409):**
```json
{
  "success": false,
  "message": "Username already exists"
}
```

---

### 3. Email Verification
**Endpoint:** `POST /api/verify-email`

**Authentication:** None (Public)

**Request Body:**
```json
{
  "token": "verification_token_from_email"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Invalid or expired verification token"
}
```

---

### 4. Resend Verification Email
**Endpoint:** `POST /api/resend-verification`

**Authentication:** None (Public)

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Verification email sent"
}
```

---

### 5. Check Verification Status
**Endpoint:** `GET /api/verification-status`

**Authentication:** None (Public)

**Query Parameters:**
```
email=user@example.com
```

**Success Response (200):**
```json
{
  "email": "user@example.com",
  "verified": true,
  "verified_at": "2024-05-21T10:30:00Z"
}
```

---

## Product Endpoints

### 1. Get All Products
**Endpoint:** `GET /api/products`

**Authentication:** None (Public)

**Query Parameters:**
```
category=breads&limit=20&page=1
```

**Success Response (200):**
```json
{
  "products": [
    {
      "id": 1,
      "name": "Sourdough Bread",
      "description": "Fresh sourdough baked daily",
      "price": 5.99,
      "category": "breads",
      "image": "/images/sourdough.jpg",
      "stock": 50,
      "availability": true
    }
  ],
  "total": 10,
  "page": 1,
  "limit": 20
}
```

---

### 2. Get Product Details
**Endpoint:** `GET /api/products/{id}`

**Authentication:** None (Public)

**URL Parameters:**
- `id` (integer) - Product ID

**Success Response (200):**
```json
{
  "id": 1,
  "name": "Sourdough Bread",
  "description": "Fresh sourdough baked daily",
  "price": 5.99,
  "category": {
    "id": 1,
    "name": "Breads"
  },
  "image": "/images/sourdough.jpg",
  "stock": 50,
  "availability": true,
  "created_at": "2024-01-15T08:00:00Z"
}
```

**Error Response (404):**
```json
{
  "error": "Product not found"
}
```

---

## Cart Endpoints

### 1. Add to Cart
**Endpoint:** `POST /api/add-to-cart/{id}`

**Authentication:** Required (JWT Token)

**URL Parameters:**
- `id` (integer) - Product ID

**Request Body:**
```json
{
  "quantity": 2
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Sourdough Bread added to cart",
  "cart": {
    "items": 3,
    "total": 25.50
  }
}
```

**Error Responses:**

**Product Not Found (404):**
```json
{
  "error": "Product not found"
}
```

**Unauthorized (401):**
```json
{
  "error": "Unauthorized"
}
```

---

### 2. View Cart
**Endpoint:** `GET /api/cart`

**Authentication:** Required (JWT Token)

**Request Headers:**
```json
{
  "Authorization": "Bearer <jwt_token>"
}
```

**Success Response (200):**
```json
{
  "items": [
    {
      "id": 1,
      "product": {
        "id": 1,
        "name": "Sourdough Bread",
        "price": 5.99
      },
      "quantity": 2,
      "subtotal": 11.98
    }
  ],
  "total": 25.50,
  "itemCount": 3
}
```

---

### 3. Update Cart Item Quantity
**Endpoint:** `POST /api/cart/update/{id}`

**Authentication:** Required (JWT Token)

**URL Parameters:**
- `id` (integer) - Cart Item ID

**Request Body:**
```json
{
  "quantity": 5
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Cart updated"
}
```

---

### 4. Remove from Cart
**Endpoint:** `POST /api/remove-from-cart/{id}`

**Authentication:** Required (JWT Token)

**URL Parameters:**
- `id` (integer) - Cart Item ID

**Success Response (200):**
```json
{
  "success": true,
  "message": "Item removed"
}
```

---

### 5. Clear Cart
**Endpoint:** `POST /api/cart/clear`

**Authentication:** Required (JWT Token)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Cart cleared"
}
```

---

## Order Endpoints

### 1. Create Order from Cart
**Endpoint:** `POST /api/order/create`

**Authentication:** Required (ROLE_USER)

**Request Body:**
```json
{}
```

**Success Response (200):**
```json
{
  "orderId": 123,
  "orderNumber": "ORD-ABC12345",
  "total": 45.99
}
```

**Error Responses:**

**Empty Cart (400):**
```json
{
  "error": "Cart is empty"
}
```

---

### 2. Get Order Details
**Endpoint:** `GET /api/orders/{id}`

**Authentication:** Required (ROLE_USER or ROLE_ADMIN)

**URL Parameters:**
- `id` (integer) - Order ID

**Success Response (200):**
```json
{
  "id": 123,
  "orderNumber": "ORD-ABC12345",
  "status": "PENDING",
  "totalAmount": 45.99,
  "items": [
    {
      "product": "Sourdough Bread",
      "quantity": 3,
      "price": 5.99,
      "subtotal": 17.97
    }
  ],
  "created_at": "2024-05-21T10:30:00Z"
}
```

---

## Payment Endpoints

### 1. Create Payment Intent (Stripe)
**Endpoint:** `POST /api/payment/create-intent`

**Authentication:** Required (ROLE_USER)

**Request Body:**
```json
{
  "amount": 4599
}
```

**Success Response (200):**
```json
{
  "clientSecret": "pi_1234567890_secret_1234567890"
}
```

**Error Response (400):**
```json
{
  "error": "Invalid amount"
}
```

---

### 2. Confirm Payment
**Endpoint:** `POST /api/payment/confirm`

**Authentication:** Required (ROLE_USER)

**Request Body:**
```json
{
  "orderId": 123,
  "paymentMethod": "stripe",
  "paymentIntentId": "pi_1234567890_secret_1234567890"
}
```

**Alternative - Cash on Delivery:**
```json
{
  "orderId": 123,
  "paymentMethod": "cod"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payment confirmed",
  "order": {
    "id": 123,
    "orderNumber": "ORD-ABC12345",
    "status": "CONFIRMED",
    "paymentMethod": "stripe"
  }
}
```

**Error Responses:**

**Missing Order ID (400):**
```json
{
  "error": "Order ID is required"
}
```

**Invalid Payment Method (400):**
```json
{
  "error": "Invalid payment method"
}
```

**Order Not Found (404):**
```json
{
  "error": "Order not found"
}
```

---

## User Management Endpoints

### 1. Save FCM Token (Firebase Cloud Messaging)
**Endpoint:** `POST /api/user/fcm-token`

**Authentication:** Required (ROLE_USER)

**Request Body:**
```json
{
  "fcmToken": "device_fcm_token_here"
}
```

**Success Response (200):**
```json
{
  "message": "FCM token saved"
}
```

---

### 2. List All Users
**Endpoint:** `GET /api/users`

**Authentication:** Required (ROLE_ADMIN)

**Success Response (200):**
```json
[
  {
    "id": "1",
    "name": "John Doe",
    "email": "john@example.com",
    "roles": ["ROLE_USER"],
    "photo": "https://example.com/photo.jpg"
  },
  {
    "id": "2",
    "name": "Jane Smith",
    "email": "jane@example.com",
    "roles": ["ROLE_STAFF", "ROLE_ADMIN"],
    "photo": null
  }
]
```

---

### 3. Assign User Role
**Endpoint:** `POST /api/users/{id}/role`

**Authentication:** Required (ROLE_ADMIN)

**URL Parameters:**
- `id` (integer) - User ID

**Request Body:**
```json
{
  "role": "ROLE_STAFF"
}
```

**Valid Roles:**
- `ROLE_CUSTOMER` - Regular customer
- `ROLE_STAFF` - Staff member
- `ROLE_ADMIN` - Administrator

**Success Response (200):**
```json
{
  "message": "Role assigned successfully",
  "user": {
    "id": 1,
    "username": "user123",
    "roles": ["ROLE_STAFF"]
  }
}
```

**Error Responses:**

**User Not Found (404):**
```json
{
  "message": "User not found"
}
```

**Invalid Role (400):**
```json
{
  "message": "Invalid role"
}
```

---

## Notification Endpoints

### 1. Send Notification
**Endpoint:** `POST /api/notifications/send`

**Authentication:** Required (ROLE_USER)

**Request Body:**
```json
{
  "topic": "staff",
  "title": "New Order",
  "body": "Order #123 has been placed",
  "data": {
    "orderId": 123,
    "priority": "high"
  }
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Notification sent",
  "recipientCount": 5
}
```

**Error Response (400):**
```json
{
  "message": "topic is required"
}
```

---

## Error Handling

### Standard Error Response Format

**400 - Bad Request:**
```json
{
  "error": "Description of the error",
  "code": 400
}
```

**401 - Unauthorized:**
```json
{
  "message": "Unauthorized - Missing or invalid JWT token"
}
```

**403 - Forbidden:**
```json
{
  "message": "Access denied - Insufficient permissions"
}
```

**404 - Not Found:**
```json
{
  "error": "Resource not found"
}
```

**409 - Conflict:**
```json
{
  "error": "Duplicate entry - Resource already exists"
}
```

**500 - Internal Server Error:**
```json
{
  "error": "Internal server error",
  "message": "Something went wrong"
}
```

---

## Authentication Methods

### JWT Bearer Token Authentication

**Header Format:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Token Expiration:**
- Access Token: 1 hour
- Refresh Token: 7 days

**How to Obtain Token:**
1. Call `/api/login` with valid credentials
2. JWT token returned in response or Authorization header
3. Include token in all subsequent requests

### OAuth 2.0 (Google Authentication)

**Endpoint:** `POST /api/auth/google`

**Request Body:**
```json
{
  "googleToken": "id_token_from_google"
}
```

**Success Response (200):**
```json
{
  "user": {
    "id": 1,
    "email": "user@gmail.com",
    "name": "John Doe"
  },
  "token": "jwt_token_here"
}
```

---

## Rate Limiting

**Limits Applied:**
- Login attempts: 5 per minute
- API requests: 100 per hour per user
- Registration: 3 per 24 hours per IP

**Response when rate limited (429):**
```json
{
  "message": "Too many requests",
  "retryAfter": 60
}
```

---

## CORS Configuration

**Allowed Origins:**
- http://localhost:8000
- http://localhost:3000
- http://localhost:4200
- https://example.com (production)

**Allowed Methods:** GET, POST, PUT, DELETE, OPTIONS, PATCH

**Allowed Headers:** Content-Type, Authorization

---

## Testing the API

### Using cURL

**Example: Create an Order**
```bash
curl -X POST http://localhost:8000/api/order/create \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -d '{}'
```

**Example: Get Products**
```bash
curl -X GET http://localhost:8000/api/products?limit=10
```

### Using Postman

1. Create new collection "Web Bakery API"
2. Set base URL: `http://localhost:8000`
3. Create requests for each endpoint
4. For authenticated endpoints, add JWT token to Authorization tab

### Using JavaScript/Fetch

```javascript
// Get Products
fetch('/api/products')
  .then(res => res.json())
  .then(data => console.log(data));

// Create Order (with auth)
fetch('/api/order/create', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({})
})
  .then(res => res.json())
  .then(data => console.log(data));
```

---

## API Versioning

Current API Version: **v1.0**

Future versions will be available at:
- `/api/v2/...`
- `/api/v3/...`

---

## Support & Documentation

For additional help:
- GitHub Issues: [project-repo/issues](https://github.com/project/issues)
- Documentation: [Full Documentation](./INSTALLATION_GUIDE.md)
- Contact: support@example.com

Last Updated: May 21, 2024
