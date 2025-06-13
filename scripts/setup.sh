#!/bin/bash
# Setup script for scrape-stream project
# This script helps set up the development environment

echo "Setting up Scrape Stream development environment..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Docker is not installed. Please install Docker and Docker Compose first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Start the Docker containers
echo "Starting Docker containers..."
docker-compose up -d

# Wait for services to be ready
echo "Waiting for services to be ready..."
sleep 10

# Set up Laravel backend
echo "Setting up Laravel backend..."
docker exec -it scrape-stream-backend composer install
docker exec -it scrape-stream-backend php artisan key:generate
docker exec -it scrape-stream-backend php artisan migrate

echo "Setup completed successfully!"
echo "You can access the services at:"
echo "- Frontend: http://localhost:3000"
echo "- Backend API: http://localhost:8000/api"
echo "- Proxy Manager is accessible within the Docker network"
