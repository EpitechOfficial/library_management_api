# Library Management System - REST API

This is a RESTful API for a Library Management System, built using Laravel. The system allows managing books, authors, and users, as well as handling borrowing and returning books. It also includes role-based access control, search functionality, pagination, input validation, error handling, and API rate limiting.

## Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Setup](#environment-setup)
- [Running the Application](#running-the-application)
- [API Endpoints](#api-endpoints)
- [Role-Based Access Control](#role-based-access-control)
- [Rate Limiting](#rate-limiting)
- [Testing](#testing)
- [Postman Documentation](#postman-documentation)

## Features

- **Role-Based Access Control (RBAC)**:
  - Admin: Full access to manage users, books, authors, and borrowing records.
  - Librarian: Can manage books, authors, and view borrowing records.
  - Member: Can view books and authors, borrow/return books, and update their profile.
  
- **API Features**:
  - Book, Author, and User management.
  - Borrowing and returning books.
  - Search books by title, author, or ISBN.
  - Pagination for large data sets.
  - Input validation and error handling with proper status codes.
  - Rate limiting for API requests.

- **Security**:
  - Authentication using Laravel Sanctum.
  - Bearer tokens for API requests.
  
## Requirements

- PHP >= 8.1
- Laravel 11
- MySQL or any other relational database (configured in `.env`)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/EpitechOfficial/library_management_api.git
   cd library_management_api
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Set up the environment:
   Copy the `.env.example` file to `.env`:
   ```bash
   cp .env.example .env
   ```

4. Generate the application key:
   ```bash
   php artisan key:generate
   ```

## Environment Setup

Configure the database in the `.env` file:
```plaintext
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_management
DB_USERNAME=root
DB_PASSWORD=
```

Make sure Sanctum is configured in the `.env`:
```plaintext
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_DOMAIN=localhost
```

## Running the Application

1. Run migrations to create the necessary database tables:
   ```bash
   php artisan migrate
   ```

2. Optionally, seed the database:
   ```bash
   php artisan db:seed
   ```

3. Run the Laravel development server:
   ```bash
   php artisan serve
   ```


## Role-Based Access Control

- **Admin**: Has full access to manage users, books, authors, and borrow records.
- **Librarian**: Can manage books, authors, and view borrow records.
- **Member**: Can view books, authors, borrow/return books, and update their own profile.

## Rate Limiting

Rate limiting has been implemented to prevent abuse:
- **Registration & Login**: 10 requests per minute.
- **Admin Routes**: 50 requests per minute.
- **Librarian & Member Routes**: 60-100 requests per minute depending on the routes.


## Postman Collection

A Postman collection is included in the project to simplify API testing.

### How to Use:
1. Download the Postman collection file located at `postman/library_management_api.postman_collection.json`.
2. Open Postman and go to the **Collections** tab.
3. Click on the **Import** button and select the downloaded `.json` file.
4. The collection will now appear in your Postman under **Collections**.
5. You can test the API endpoints directly from Postman.

Make sure you have the API running locally (see setup instructions) and update the environment variables in Postman (like `base_url`, `auth_token`) as needed.


