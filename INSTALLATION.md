# Installation Guide

This guide explains how to install and run the **AI-Powered Student Survey and Continuous Quality Improvement (CQI) System** locally.

---

## Table of Contents
1. [System Requirements](#1-system-requirements)
2. [Clone the Repository](#2-clone-the-repository)
3. [Install Laravel Dependencies](#3-install-laravel-dependencies)
4. [Install Frontend Dependencies](#4-install-frontend-dependencies)
5. [Configure Environment File](#5-configure-environment-file)
6. [Python Environment Setup](#6-python-environment-setup)
7. [Database Migration](#7-database-migration)
8. [Run the Application](#8-run-the-application)
9. [PHP Configuration](#9-php-configuration)
10. [Troubleshooting](#troubleshooting)

---

## 1. System Requirements
Ensure the following software is installed on your system:

* **PHP:** 8.2 – 8.4
* **Composer**
* **Node.js (LTS)** and **npm**
* **Python:** 3.x
* **MySQL Server:** 8.0
* **Git**

**Optional but recommended:**
* MySQL Workbench
* VS Code

---

## 2. Clone the Repository
Clone the project from GitHub and navigate into the directory:

```bash
git clone [https://github.com/SamSamSamSamSamSamSamSamSamSamSamSamSam/AI-Survey-Capstone.git](https://github.com/SamSamSamSamSamSamSamSamSamSamSamSamSam/AI-Survey-Capstone.git)
cd AI-Survey-Capstone
```

---

## 3. Install Laravel Dependencies
Install PHP dependencies using Composer:

```bash
composer install
```

## 4. Install Frontend Dependencies
Install Node dependencies.

```bash
npm install
```

Compile frontend assets.

```bash
npm run dev
```

---

## 5. Configure Environment File
Copy the example environment file.

```bash
cp .env.example .env
```

Generate the Laravel application key.

```bash
php artisan key:generate
```

Edit the .env file and configure your database credentials.

```ini
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Add your Gemini API Key.

```ini
GEMINI_API_KEY=your_api_key
```

---

## 6. Python Environment Setup
Navigate to the Python scripts directory.

```bash
cd resources/python
```

Create a virtual environment.

```bash
python -m venv venv
```

Activate the virtual environment.

Windows:

```bash
venv\Scripts\activate
```

When activated, your terminal should show:

```bash
(venv)
```

Install required Python packages.

```bash
pip install -r requirements.txt
```

---

## 7. Database Migration
Run database migrations and seeders.

```bash
php artisan migrate --seed
```

---

## 8. Run the Application
Start the Laravel development server.

```bash
php artisan serve
```

Vite server.

```bash
npm run dev
```

Sentiment Analysis Server.

```bash
start_sentiment_server.bat
```

Access the system in your browser:

http://127.0.0.1:8000

---

## 9. PHP Configuration
Open your `php.ini` file and ensure the following extensions are enabled.

```ini
extension=curl
extension=fileinfo
extension=mbstring
extension=exif
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sockets
extension=zip
```

Restart your web server or terminal after making changes.

---

## Troubleshooting
### Composer Issues
Clear composer cache.

```bash
composer clear-cache
```

### Node Issues
Reinstall node modules.

```bash
rm -rf node_modules
npm install
```

### Python Issues
Ensure Python version is detected.

```bash
python --version
```
