# Web Scraping Service

A full-stack web scraping application that collects product data from eCommerce platforms, stores it in a MySQL database, and displays it on a React-based frontend.

## Architecture

- **Backend**: Laravel (PHP 8+), MySQL
- **Frontend**: Next.js (React 18+)
- **Proxy Manager**: Golang
- **API**: REST (JSON responses)

## Project Structure

```
scrape-stream/
├── backend/             # Laravel PHP backend
├── frontend/            # Next.js React frontend
├── proxy-manager/       # Golang proxy rotation service
├── docker/              # Docker configuration files
│   └── nginx/
│       └── conf.d/      # Nginx configuration
└── docker-compose.yml   # Docker Compose configuration
```

## Docker Setup

This project is fully containerized using Docker and Docker Compose, making it easy to set up and run in any environment.

### Prerequisites

- Docker
- Docker Compose

### Running the Application

1. Clone the repository:

```bash
git clone <repository-url>
cd scrape-stream
```

2. Start the Docker containers:

```bash
docker-compose up -d
```

This will start all the services:

- Laravel Backend (<http://localhost:8000/api>)
- Next.js Frontend (<http://localhost:3000>)
- Golang Proxy Manager (internal network only)
- MySQL Database
- Nginx Web Server

3. Set up the Laravel application:

```bash
# Enter the Laravel container
docker exec -it scrape-stream-backend bash

# Install dependencies
composer install

# Run migrations
php artisan migrate

# Generate application key
php artisan key:generate
```

### Accessing the Services

- **Frontend**: <http://localhost:3000>
- **Backend API**: <http://localhost:8000/api>
- **Proxy Manager**: Only accessible from within the Docker network
