# Proxy Manager Service

A lightweight Golang microservice for proxy rotation that provides HTTP proxies for web scraping applications.

## Features

- Random proxy rotation from a configurable pool
- Optional proxy validation
- Simple REST API
- Health check endpoint
- Environment variable configuration

## API Endpoints

- `GET /proxy` - Returns a random proxy from the pool
  - Query parameters:
    - `validate` (boolean, optional): Validates the proxy before returning it
- `GET /health` - Health check endpoint

## Project Structure

```
proxy-manager/
├── main.go         # Entry point and server setup
├── repository.go   # Data access layer for proxies
├── service.go      # Business logic layer
├── handler.go      # HTTP request handlers
├── proxies.txt     # List of proxies (one per line)
├── .env            # Environment variables
└── README.md       # Documentation
```

## Getting Started

### Prerequisites

- Go 1.16 or higher

### Installation

1. Clone the repository
2. Navigate to the proxy-manager directory
3. Install dependencies:

```bash
go mod init proxy-manager
go get github.com/joho/godotenv
```

### Configuration

Create a `.env` file in the root directory with the following variables:

```
PORT=8080              # Port to run the server on
PROXY_FILE=proxies.txt # Path to the proxy list file
VERSION=1.0.0          # Version of the service
```

### Running the Service

```bash
go run .
```

Or build and run:

```bash
go build -o proxy-manager
./proxy-manager
```

## Usage Examples

### Get a random proxy

```bash
curl http://localhost:8080/proxy
```

Response:
```json
{
  "success": true,
  "data": "http://103.152.112.162:80"
}
```

### Get a validated proxy

```bash
curl http://localhost:8080/proxy?validate=true
```

Response:
```json
{
  "success": true,
  "data": "http://103.152.112.162:80"
}
```

### Health check

```bash
curl http://localhost:8080/health
```

Response:
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "version": "1.0.0"
  }
}
```

## Adding More Proxies

Simply add more proxies to the `proxies.txt` file, one per line in the format:

```
http://ip:port
```

The service will automatically load them when restarted.

## Integration with Laravel Backend

In your Laravel application, you can fetch a proxy from this service using:

```php
$client = new \GuzzleHttp\Client();
$response = $client->get('http://localhost:8080/proxy');
$data = json_decode($response->getBody(), true);
$proxy = $data['data'];

// Use the proxy in your scraping requests
$client = new \GuzzleHttp\Client([
    'proxy' => $proxy
]);
$response = $client->get('https://example.com');
```
