# Installation Guide

This guide explains how to install and run the project locally.

---

## 1. System Requirements

Make sure the following are installed:

- PHP == 8.4x
- Composer
- Node.js & npm
- Python >= 3.x
- MySQL Server 8.0 & MySQL Workbench
- Git

---

## 2. Clone the Repository

```bash
git clone [https://github.com/your-repository/project-name.git](https://github.com/SamSamSamSamSamSamSamSamSamSamSamSamSam/AI-Survey-Capstone.git)
cd project-name
```

## 3. Install Laravel Dependencies

```bash
composer install
```

## 4. Install Frontend Dependencies

```bash
npm install
```
Compile frontend assets:
```bash
npm run dev
```

## 5. Configure Environment File

Copy the example environment file.
```bash
cp .env.example .env
```
Generate the application key.
```bash
php artisan key:generate
```
Edit the .env file and configure the database:
```bash
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 6. php.ini configuration (for newly installed php)

uncomment the following extensions in php.ini

extension=curl
extension=fileinfo
extension=mbstring
extension=exif      
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sockets
extension=zip