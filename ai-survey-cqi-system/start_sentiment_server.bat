@echo off
echo Starting DistilBERT Sentiment Server...
echo Model will load once, then stay ready in memory.
echo Keep this window open while using the app.
echo.
@REM Activate the virtual environment and run the sentiment server
@REM Make sure to adjust the path to your virtual environment and sentiment server script if necessary
@REM if you have a different setup, update the paths accordingly
resources\python\myvenv\Scripts\python.exe resources\python\sentiment_server.py
pause