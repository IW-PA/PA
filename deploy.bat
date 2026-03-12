@echo off
REM Budgie Deployment Script for Windows
REM This script sets up the Budgie application for production deployment

echo 🐦 Budgie Deployment Script
echo ==============================

REM Check if Docker is installed
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not installed. Please install Docker Desktop first.
    pause
    exit /b 1
)

docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Compose is not installed. Please install Docker Compose first.
    pause
    exit /b 1
)

REM Create necessary directories
echo 📁 Creating directories...
if not exist logs mkdir logs
if not exist public\uploads mkdir public\uploads
if not exist backups mkdir backups

REM Copy environment file if it doesn't exist
if not exist .env (
    echo 📝 Creating environment file...
    copy env.example .env
    echo ⚠️  Please edit .env file with your configuration before starting the application.
)

REM Build and start containers
echo 🚀 Building and starting containers...
docker-compose down
docker-compose build --no-cache
docker-compose up -d

REM Wait for database to be ready
echo ⏳ Waiting for database to be ready...
timeout /t 30 /nobreak >nul

REM Check if containers are running
echo 🔍 Checking container status...
docker-compose ps

REM Display access information
echo.
echo ✅ Deployment completed!
echo.
echo 🌐 Access your application:
echo    Application: http://localhost:8082
echo    phpMyAdmin:  http://localhost:8083
echo.
echo 📊 Default credentials:
echo    Database: budgie_db
echo    Username: budgie_user
echo    Password: budgie_password
echo.
echo 🔧 To stop the application:
echo    docker-compose down
echo.
echo 📝 To view logs:
echo    docker-compose logs -f
echo.
echo ⚠️  Remember to:
echo    1. Update .env file with your production settings
echo    2. Change default passwords
echo    3. Set up SSL certificates
echo    4. Configure your domain name
echo.
pause
