# Web Scraping Service

A full-stack web scraping application that collects product data from eCommerce platforms, stores it in a MySQL database, and displays it on a React-based frontend. Built with Laravel 12 and PHP 8.2, featuring a modular and extensible spider architecture.

## Architecture

- **Backend**: Laravel 12 (PHP 8.2+), MySQL
- **Frontend**: Next.js (React 18+)
- **Proxy Manager**: Golang
- **API**: REST (JSON responses)
- **Scraping Engine**: RoachPHP with custom spider architecture
- **Job System**: Laravel Queue for asynchronous spider execution

## Project Structure

```plaintext
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
git clone git@github.com:Msalah11/scrape-stream.git
cd scrape-stream
```

1. Start the Docker containers:

```bash
docker-compose up -d
```

This will start all the services:

- Laravel Backend (<http://localhost:8000/api>)
- Next.js Frontend (<http://localhost:3000>)
- Golang Proxy Manager (internal network only)
- MySQL Database
- Nginx Web Server

1. Set up the Laravel application:

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

## Spider Architecture

The application uses a modular spider architecture built on RoachPHP:

### Base Classes and Traits

- **BaseSpider**: Abstract class extending RoachPHP's BasicSpider with common configuration
- **SpiderHelpers**: Trait with reusable methods for text cleaning, URL validation, etc.
- **SpiderType**: Enum for type-safe spider management

### Available Spiders

- **AmazonSpider**: Scrapes product listings from Amazon
- **ProductPageSpider**: Scrapes product details from product pages

### Job System

Spiders run asynchronously via Laravel's queue system:

```php
RunSpiderJob::dispatch(SpiderType::AMAZON, ['startUrls' => ['https://example.com']]);
```

## API Endpoints

### Trigger Spider

```http
POST /api/scrape
```

Payload:

```json
{
  "spider_type": "amazon",
}
```

## Commands

The application includes several Artisan commands:

```bash
# Run a spider directly
php artisan spider:run
# Process queued spider jobs
php artisan queue:work
```

## Scheduling

Spiders are scheduled to run daily using Laravel's scheduler. Configure crontab to run:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
