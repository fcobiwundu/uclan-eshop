# UCLan E-Shop

A redesigned, database-driven e-commerce web application based on the UCLan Student Union Shop concept.

The project focuses on improving the storefront design, user experience, responsive layouts and presentation of an existing PHP and MySQL e-commerce application.

## Project Overview

UCLan E-Shop is a full-stack web application that allows users to browse products, create accounts, log in, manage a shopping basket and interact with product reviews.

The application combines a responsive frontend with a PHP backend and a MySQL database to provide dynamic product information and user-related functionality.

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Server-side application logic |
| MySQL | Database management and storage |
| PDO | Database connectivity and prepared statements |
| HTML5 | Page structure |
| CSS3 | Styling and responsive layouts |
| JavaScript | Client-side interactions and validation |

## Key Features

### Storefront and Product Catalogue
- Responsive homepage with database-driven special offers.
- Dynamic product catalogue.
- Product filtering by category.
- Product search functionality.
- Individual product detail pages.
- Product images, descriptions and pricing.

### User Authentication
- User registration and login.
- Secure password hashing using PHP `password_hash()`.
- Password verification using `password_verify()`.
- PHP session-based authentication.
- Registration form validation on both the client and server sides.
- Password confirmation and password complexity checks.

### Shopping Basket and Orders
- Add products to the shopping basket.
- Session-based basket management.
- Product quantities and calculated totals.
- Checkout functionality.
- Order records stored in the MySQL database.

### Product Reviews
- Submit product reviews.
- Provide ratings and written feedback.
- Display existing customer reviews.
- Calculate average product ratings.

### Interface and User Experience
- Redesigned page layouts and visual presentation.
- Responsive layouts for desktop and mobile devices.
- Mobile navigation.
- Consistent form styling.
- Differentiated button styles and interface elements.

## Technical Implementation

### Database Connectivity

The application uses PHP Data Objects (PDO) to connect to MySQL.

A central `connect.php` file manages the database connection, allowing application pages to reuse the same database connection.

Prepared statements are used for database queries involving user input.

**Security:** Database credentials are stored in the private `connect.php` file and are excluded from the public Git repository.

### User Authentication

The registration system validates submitted information on the server before creating an account.

Passwords are hashed before being stored in the database. During login, `password_verify()` checks the submitted password against the stored password hash.

PHP sessions are used to maintain authenticated user state.

### Product and Order Management

Product information is retrieved dynamically from the database. Users can browse, search and filter products, add items to their basket and proceed through the checkout process.

Order information is stored in the database using the existing application structure.

## Project Structure

```text
uclan-eshop/
│
├── css/
│   ├── style.css
│   └── auth.css
│
├── js/
│   ├── script.js
│   └── auth.js
│
├── resources/
│
├── index.php
├── products.php
├── item.php
├── cart.php
├── login.php
├── register.php
├── logout.php
│
├── connect.php
├── .gitignore
└── README.md
```

*The directory listing represents the main application structure. Additional assets and files may be present.*

## Local Setup

### Requirements
- PHP
- MySQL
- A local PHP development environment such as XAMPP
- A web browser

### Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/fcobiwundu/uclan-eshop.git
   ```

2. Place the project in your local web server's document directory.

3. Configure the MySQL database connection in `connect.php`.

4. Ensure the required database and existing application tables are available.

5. Start your local web server and MySQL service.

6. Open the project through your local server address.

   Example:

   ```text
   http://localhost/uclan-eshop/
   ```

**Note:** The database connection file and credentials are not included in the public repository. The existing database structure must be available for the application to operate correctly.

## Security Considerations

- Passwords are stored using PHP's password hashing functions.
- Database queries use prepared statements.
- User authentication is managed through PHP sessions.
- Database credentials are excluded from version control.
- Client-side validation is supported by server-side validation.

## Project Background

This project is a professional redesign and further development of a UCLan Student Union Shop concept, building on an existing PHP and MySQL application.

The focus is on demonstrating practical full-stack web development, database integration, authentication, responsive interface design and improvements to the overall shopping experience.

## Developer

**Festus Obiwundu**

GitHub: [@fcobiwundu](https://github.com/fcobiwundu)

Project Repository: [UCLan E-Shop](https://github.com/fcobiwundu/uclan-eshop)
