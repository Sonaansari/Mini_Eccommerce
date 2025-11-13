#  Mini E-Commerce REST API (Laravel 10 + Sanctum)

A **Mini E-Commerce API** built using **Laravel 10** and **Sanctum Authentication**, demonstrating real-world API design, database relationships, and role-based access (Admin/User).

---

##  Objective

To develop a modular and secure E-commerce REST API covering:
- Authentication
- Product management
- Cart operations
- Order processing
- Role-based access control (Admin/User)

---

##  Tech Stack

- **Laravel 10+**
- **PHP 8.1+**
- **Laravel Sanctum** (for token-based auth)
- **MySQL** (or SQLite)
- **Postman** (for testing)

---

##  Setup Instructions

### 1️ Clone and Install

```bash

git clone <your_repo_url>
cd mini-ecommerce-api
composer install
cp .env.example .env
php artisan key:generate
