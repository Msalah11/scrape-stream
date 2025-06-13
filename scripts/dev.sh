#!/bin/bash
# Development script for scrape-stream project
# This script helps manage the development environment

function show_help {
  echo "Scrape Stream Development Helper"
  echo ""
  echo "Usage: ./scripts/dev.sh [command]"
  echo ""
  echo "Commands:"
  echo "  start       - Start all containers"
  echo "  stop        - Stop all containers"
  echo "  restart     - Restart all containers"
  echo "  rebuild     - Rebuild and start all containers"
  echo "  logs        - Show logs from all containers"
  echo "  backend     - Enter backend container shell"
  echo "  frontend    - Enter frontend container shell"
  echo "  proxy       - Enter proxy-manager container shell"
  echo "  migrate     - Run Laravel migrations"
  echo "  help        - Show this help message"
}

case "$1" in
  start)
    echo "Starting all containers..."
    docker-compose up -d
    ;;
  stop)
    echo "Stopping all containers..."
    docker-compose down
    ;;
  restart)
    echo "Restarting all containers..."
    docker-compose down && docker-compose up -d
    ;;
  rebuild)
    echo "Rebuilding and starting all containers..."
    docker-compose down && docker-compose up -d --build
    ;;
  logs)
    echo "Showing logs from all containers..."
    docker-compose logs -f
    ;;
  backend)
    echo "Entering backend container shell..."
    docker exec -it scrape-stream-backend bash
    ;;
  frontend)
    echo "Entering frontend container shell..."
    docker exec -it scrape-stream-frontend sh
    ;;
  proxy)
    echo "Entering proxy-manager container shell..."
    docker exec -it scrape-stream-proxy-manager sh
    ;;
  migrate)
    echo "Running Laravel migrations..."
    docker exec -it scrape-stream-backend php artisan migrate
    ;;
  *)
    show_help
    ;;
esac
