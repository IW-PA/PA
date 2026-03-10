#!/bin/bash

# Budgie Deployment Script
# This script sets up the Budgie application for production deployment

echo "🐦 Budgie Deployment Script"
echo "=============================="

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p logs
mkdir -p public/uploads
mkdir -p backups

# Set proper permissions
echo "🔐 Setting permissions..."
chmod 755 logs public/uploads backups
chmod 644 public/.htaccess
chmod 644 docker-compose.yml

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating environment file..."
    cp env.example .env
    echo "⚠️  Please edit .env file with your configuration before starting the application."
fi

# Build and start containers
echo "🚀 Building and starting containers..."
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 30

# Check if containers are running
echo "🔍 Checking container status..."
docker-compose ps

# Display access information
echo ""
echo "✅ Deployment completed!"
echo ""
echo "🌐 Access your application:"
echo "   Application: http://localhost:8082"
echo "   phpMyAdmin:  http://localhost:8083"
echo ""
echo "📊 Default credentials:"
echo "   Database: budgie_db"
echo "   Username: budgie_user"
echo "   Password: budgie_password"
echo ""
echo "🔧 To stop the application:"
echo "   docker-compose down"
echo ""
echo "📝 To view logs:"
echo "   docker-compose logs -f"
echo ""
echo "⚠️  Remember to:"
echo "   1. Update .env file with your production settings"
echo "   2. Change default passwords"
echo "   3. Set up SSL certificates"
echo "   4. Configure your domain name"
