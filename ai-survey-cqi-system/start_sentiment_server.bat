@echo off
echo Starting DistilBERT Sentiment Server...
echo Model will load once, then stay ready in memory.
echo Keep this window open while using the app.
echo.
resources\python\myenv\Scripts\python.exe resources\python\sentiment_server.py
pause