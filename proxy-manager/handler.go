package main

import (
	"encoding/json"
	"net/http"
	"os"
	"strconv"
)

type Response struct {
	Success bool        `json:"success"`
	Data    interface{} `json:"data,omitempty"`
	Error   string      `json:"error,omitempty"`
}

type ProxyHandler struct {
	service ProxyService
}

func NewProxyHandler(service ProxyService) *ProxyHandler {
	return &ProxyHandler{
		service: service,
	}
}

func (h *ProxyHandler) GetProxy(w http.ResponseWriter, r *http.Request) {
	// Set content type
	w.Header().Set("Content-Type", "application/json")
	
	// Only allow GET requests
	if r.Method != http.MethodGet {
		w.WriteHeader(http.StatusMethodNotAllowed)
		json.NewEncoder(w).Encode(Response{
			Success: false,
			Error:   "Method not allowed",
		})
		return
	}
	
	// Check if validation is required
	validateStr := r.URL.Query().Get("validate")
	validate := false
	if validateStr != "" {
		var err error
		validate, err = strconv.ParseBool(validateStr)
		if err != nil {
			validate = false
		}
	}
	
	// Get a proxy
	proxy, err := h.service.GetProxy(validate)
	if err != nil {
		statusCode := http.StatusInternalServerError
		if err == ErrNoProxiesAvailable {
			statusCode = http.StatusNotFound
		}
		
		w.WriteHeader(statusCode)
		json.NewEncoder(w).Encode(Response{
			Success: false,
			Error:   err.Error(),
		})
		return
	}
	
	// Return the proxy
	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(Response{
		Success: true,
		Data:    proxy,
	})
}

func (h *ProxyHandler) HealthCheck(w http.ResponseWriter, r *http.Request) {
	// Set content type
	w.Header().Set("Content-Type", "application/json")
	
	// Return health status
	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(Response{
		Success: true,
		Data: map[string]string{
			"status": "healthy",
			"version": os.Getenv("VERSION"),
		},
	})
}
