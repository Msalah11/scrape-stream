package main

import (
	"log"
	"net/http"
	"os"

	"github.com/joho/godotenv"
)

func main() {
	if err := godotenv.Load(); err != nil {
		log.Println("No .env file found, using system environment variables")
	}

	port := os.Getenv("PORT")
	if port == "" {
		port = "8080"
	}

	proxyRepo := NewProxyRepository()
	proxyService := NewProxyService(proxyRepo)
	proxyHandler := NewProxyHandler(proxyService)

	http.HandleFunc("/proxy", proxyHandler.GetProxy)
	http.HandleFunc("/health", proxyHandler.HealthCheck)

	log.Printf("Proxy Manager server starting on port %s...\n", port)
	if err := http.ListenAndServe(":"+port, nil); err != nil {
		log.Fatalf("Server failed to start: %v", err)
	}
}
