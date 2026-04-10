# System Path Configuration Guide
To ensure the AI-Survey-CQI-System runs correctly, you must update the following files to point to your local project directories.

---

## 1. Laravel PDF Service
The system needs to know where your Python environment and PDF scripts are located to generate the CQI reports.

* File: app/Services/CqiPdfServices.php

* Line: 34

* Change:

```php
// From:
$pythonPath = base_path('your_path');

// To (Example):
$pythonPath = base_path('venv/Scripts/python.exe'); 
// Note: Ensure this points to the actual Python executable inside your virtual environment.
```
---

## 2. Sentiment Server (Windows)
This batch file keeps the NLP server running in the background.

* *ile: start_sentiment_server.bat

* Change:

```bash
### From:
cd /d "your_path"
call "your_path\scripts\activate"

### To (Example):
cd /d "C:\Users\Sam\Documents\ai-survey-cqi-system\nlp_engine"
call "venv\Scripts\activate"
```
---

## 3. Sentiment Server (Linux/macOS)
This shell script is used for production or Unix-based development environments.

File: start_sentiment_server.sh

Change:

```Bash
### From:
cd "your_path"
source "your_path/bin/activate"

### To (Example):
cd "/var/www/ai-survey-cqi-system/nlp_engine"
source "venv/bin/activate"
```
## Post-Configuration Check
After updating these paths, verify the connection by running:

* Start the NLP Server: Run the .bat or .sh file.

* Check Laravel Logs: Ensure storage/logs/laravel.log does not show "File not found" errors when a survey is submitted.

* Queue Worker: Ensure your queue is active to process the background jobs:

```Bash
php artisan queue:work
```