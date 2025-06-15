# Web Scraping Service

A full-stack web scraping application that collects product data from eCommerce platforms, stores it in a MySQL database, and displays it on a React-based frontend. Built with Laravel 12 and PHP 8.2, featuring a modular and extensible spider architecture.

## System Architecture Overview

The Scrape Stream application is built on a modern, microservices-oriented architecture that separates concerns while maintaining high performance and scalability. The system consists of three primary components working in harmony:

### Backend (Laravel 12)

The Laravel backend serves as the core of the application, handling data processing, storage, and API endpoints. Key features include:

- **Modular Spider Architecture**: Built on RoachPHP with a custom `BaseSpider` abstract class and `SpiderHelpers` trait for code reuse and standardization
- **Asynchronous Processing**: Spiders run as background jobs using Laravel's queue system, preventing timeouts and improving throughput
- **Type-Safe API**: Uses PHP 8.2 features like enums and typed properties for robust code
- **RESTful API**: Provides paginated product data and spider control endpoints

### Frontend (Next.js)

The React-based frontend delivers a responsive user interface with:

- **Component-Based Design**: Follows SOLID principles with separated concerns
- **Real-Time Updates**: Auto-refreshes product data every 30 seconds
- **Optimized Images**: Uses Next.js Image component for performance
- **Type Safety**: Built with TypeScript for better developer experience

### Screenshot

![Products Page Screenshot](/Create-Next-App-06-15-2025_06_39_PM.png)

*The products page showing the responsive grid layout with product cards*

### Proxy Manager (Golang)

A dedicated service written in Go handles proxy rotation and management:

- **High Performance**: Go's concurrency model enables efficient proxy handling
- **IP Rotation**: Prevents rate limiting and blocking during scraping operations
- **Health Monitoring**: Automatically tests and verifies proxy availability

## Key Design Decisions

### 1. Modular Spider Architecture

One of the most important design decisions was implementing a modular spider architecture with inheritance and traits:

- **BaseSpider Abstract Class**: Centralizes common configuration and middleware, reducing code duplication and ensuring consistent behavior across all spiders
- **SpiderHelpers Trait**: Provides reusable utility methods for text cleaning, price normalization, and URL validation
- **Enum-Based Spider Management**: Uses PHP 8.2 enums for type-safe spider selection and configuration

### 2. Asynchronous Processing

To handle the potentially time-consuming nature of web scraping:

- **Queue-Based Job System**: Spiders run as background jobs to prevent timeout issues
- **Serializable Configuration**: Spider settings are properly serialized for queue processing
- **Scheduled Execution**: Uses Laravel's scheduler for regular data collection

### 3. Component-Based Frontend

The frontend follows modern React best practices:

- **Custom Hooks**: Separates data fetching logic from UI components
- **Service Layer**: Isolates API communication in dedicated service modules
- **Responsive Design**: Grid-based layout adapts to different screen sizes

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

# Seed the database
php artisan db:seed
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
RunSpiderJob::dispatch(SpiderType::AMAZON);
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

### Get Products

Retrieve scraped products with pagination:

```http
GET /api/products
```

Query Parameters:

| Parameter | Type    | Description                     |
|-----------|--------|---------------------------------|
| page      | integer | Page number (default: 1)        |
| per_page  | integer | Items per page (default: 15)    |
| search    | string  | Search query                    |
| min_price | float   | Minimum price                   |
| max_price | float   | Maximum price                   |

Response:

```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "products": [
      {
        "id": 1,
        "title": "Product Title",
        "price": 99.99,
        "image_url": "https://example.com/image.jpg",
        "created_at": "2 days ago",
        "updated_at": "1 day ago"
      }
    ],
    "meta": {
      "total": 100,
      "count": 15,
      "per_page": 15,
      "current_page": 1,
      "total_pages": 7
    }
  },
  "links": {
    "first": "http://localhost:8000/api/products?page=1",
    "last": "http://localhost:8000/api/products?page=7",
    "prev": null,
    "next": "http://localhost:8000/api/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 7,
    "path": "http://localhost:8000/api/products",
    "per_page": 15,
    "to": 15,
    "total": 100
  }
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
