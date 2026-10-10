# Employee Management System

## Project Overview
This project is a Core PHP Practical Assessment demonstrating the implementation of a fully functional Employee Registration and Listing System using modern best practices for a junior developer.

It avoids frameworks and instead relies strictly on **Core PHP**, **MySQL (PDO)**, **jQuery AJAX**, and **Bootstrap 5**.

## Features and Technologies
- **Core PHP**: Handles backend API operations safely.
- **MySQL via PDO**: Secure prepared statements prevent SQL injection.
- **Token-based Authentication**: Custom Bearer token logic generated using `random_bytes()`, securely hashed (SHA-256) inside the database.
- **Password Security**: Uses `password_hash()` and `password_verify()` for storing administrative passwords.
- **RESTful JSON API**: Validates and returns proper JSON payloads and HTTP status codes (200, 201, 400, 401, 405, 409, 500).
- **Frontend SPA Experience**: Fully AJAX-driven user experience using jQuery, utilizing `sessionStorage` for storing tokens.
- **Bootstrap 5**: Responsive layout, styled forms, and tables.

## Software Prerequisites
- **XAMPP (or similar WAMP/LAMP stack)** running on Windows.
- **PHP 7.4+** (Uses modern functions like `random_bytes`, `password_hash`).
- **MySQL/MariaDB** database server.

## Installation & Setup Instructions

### 1. XAMPP Setup
1. Clone or extract this repository into your XAMPP `htdocs` directory.
2. The folder structure should look exactly like: `d:\xampp\htdocs\own\employee-management\`

### 2. Database Import
You can automatically import the database using your MySQL CLI or via phpMyAdmin:
1. Open phpMyAdmin (`http://localhost/phpmyadmin`).
2. Create a database (or simply import the file and it will be created automatically).
3. Import the `database.sql` file located in the root of the project.

### 3. Demo Administrator Setup
Once the database is imported, you must run the secure seeding script to generate an admin user with a hashed password:
1. Open your terminal or browser.
2. If using browser: Navigate to `http://localhost/own/employee-management/seed_admin.php`.
3. If using CLI: Run `php seed_admin.php` from the project root.
4. You will see a success message indicating the admin was seeded:
   - **Email:** admin@example.com
   - **Password:** 123456

> **Important**: Delete `seed_admin.php` before deploying to production.

---

## API Endpoints Documentation

### 1. Login API
**POST** `/api/login.php`
- **Description:** Authenticates an admin user and returns a Bearer token.
- **Request Body (JSON):**
  ```json
  {
      "email": "admin@example.com",
      "password": "123456"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
      "status": true,
      "token": "generated_hex_token_string"
  }
  ```
- **Error Response (401 Unauthorized):**
  ```json
  {
      "status": false,
      "message": "Invalid credentials"
  }
  ```

### 2. Register Employee API
**POST** `/api/employees.php`
- **Description:** Adds a new employee to the database.
- **Headers Required:** `Authorization: Bearer <TOKEN>`
- **Request Body (JSON):**
  ```json
  {
      "name": "Jane Doe",
      "email": "jane@example.com",
      "mobile": "0123456789",
      "department": "IT",
      "designation": "Developer",
      "salary": "65000",
      "joining_date": "2026-10-15",
      "status": "Active"
  }
  ```
- **Success Response (201 Created):**
  ```json
  {
      "status": true,
      "message": "Employee registered successfully."
  }
  ```
- **Validation Error (400 Bad Request):**
  ```json
  {
      "status": false,
      "message": "Validation failed.",
      "errors": ["Salary must be a non-negative number."]
  }
  ```
- **Duplicate Conflict Error (409 Conflict):**
  ```json
  {
      "status": false,
      "message": "Email already exists for another employee."
  }
  ```

### 3. List Employees API
**GET** `/api/employees.php`
- **Description:** Fetches employee records. Supports pagination and searching.
- **Headers Required:** `Authorization: Bearer <TOKEN>`
- **Query Parameters (Optional):**
  - `page`: Page number (default: 1)
  - `limit`: Records per page (default: 10)
  - `search`: Search term matching name or email
- **Success Response (200 OK):**
  ```json
  {
      "status": true,
      "data": [
          {
              "id": 1,
              "name": "Jane Doe",
              "email": "jane@example.com",
              "department": "IT",
              "designation": "Developer"
          }
      ],
      "pagination": {
          "total": 1,
          "page": 1,
          "limit": 5,
          "total_pages": 1
      }
  }
  ```
- **Missing/Invalid Token Error (401 Unauthorized):**
  ```json
  {
      "status": false,
      "message": "Unauthorized: Missing or invalid Authorization header."
  }
  ```

## Security Considerations & Limitations
- **Token Storage**: Tokens are kept in `sessionStorage` (purged when the browser tab closes) and hashed securely (`SHA-256`) in the database before storage to prevent leakage in case of database dumps.
- **XSS Protection**: Employee data rendered in HTML is sanitized using jQuery's `.text()` encoding approach before `.html()` rendering, eliminating script injections.
- **SQL Injection**: Prevented using PDO Prepared Statements consistently. Emulated prepares are turned off in `config/database.php`.
- **Known Limitations**:
  - The login mechanism issues a new token without invalidating old ones manually unless they expire. A complete production system would have token revocation endpoints (Logout API).
  - No rate limiting is implemented natively.

## Testing Instructions

### Using Postman
1. **Login**: 
   - Method: `POST`
   - URL: `http://localhost/own/employee-management/api/login.php`
   - Body: Select `raw` -> `JSON`, and paste:
     ```json
     {
         "email": "admin@example.com",
         "password": "123456"
     }
     ```
2. **Use API**:
   - For any `api/employees.php` request, go to the **Authorization** tab.
   - Select **Bearer Token** and paste the token from the login response.
   - For GET (Listing), add query params (`page`, `limit`, `search`) in the **Params** tab.
   - For POST (Registration), add the JSON payload in the **Body** tab.

### Using cURL
**Login to get a token:**
```bash
curl -X POST -H "Content-Type: application/json" -d "{\"email\":\"admin@example.com\",\"password\":\"123456\"}" http://localhost/own/employee-management/api/login.php
```

**Fetch Employees (Replace TOKEN string):**
```bash
curl -X GET -H "Authorization: Bearer TOKEN" http://localhost/own/employee-management/api/employees.php?page=1&limit=5
```

## Troubleshooting
- **"Invalid credentials" on Login**: Ensure you have imported the `database.sql` file and subsequently run `php seed_admin.php` to populate the `admin_users` table with the securely hashed default password.
- **"Internal Server Error" when searching**: Ensure you are using the updated `api/employees.php` file which handles strict PDO parameters without emulation (using `:search1` and `:search2`).
- **"Unauthorized" or Token missing**: Ensure you are passing the token as a `Bearer` token in the `Authorization` header, and that the token hasn't expired (tokens last 2 hours).
