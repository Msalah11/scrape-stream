package main

import (
	"errors"
	"net"
	"net/http"
	"net/url"
	"time"
)

// Define errors
var (
	ErrNoProxiesAvailable = errors.New("no proxies available")
	ErrProxyValidation    = errors.New("proxy validation failed")
)

// ProxyService defines the interface for proxy business logic
type ProxyService interface {
	GetProxy(validate bool) (string, error)
	ValidateProxy(proxy string) bool
	ReloadProxies() error
}

// proxyService implements the ProxyService interface
type proxyService struct {
	repository ProxyRepository
}

// NewProxyService creates a new proxy service
func NewProxyService(repo ProxyRepository) ProxyService {
	return &proxyService{
		repository: repo,
	}
}

// GetProxy returns a random proxy, optionally validating it first
func (s *proxyService) GetProxy(validate bool) (string, error) {
	// Get a random proxy from the repository
	proxy, err := s.repository.GetRandomProxy()
	if err != nil {
		return "", err
	}
	
	// If validation is not required, return the proxy
	if !validate {
		return proxy, nil
	}
	
	// Validate the proxy
	if s.ValidateProxy(proxy) {
		return proxy, nil
	}
	
	// Try up to 3 more times to get a valid proxy
	for i := 0; i < 3; i++ {
		proxy, err = s.repository.GetRandomProxy()
		if err != nil {
			return "", err
		}
		
		if s.ValidateProxy(proxy) {
			return proxy, nil
		}
	}
	
	return "", ErrProxyValidation
}

// ValidateProxy checks if a proxy is working
func (s *proxyService) ValidateProxy(proxyStr string) bool {
	// Parse the proxy URL
	proxyURL, err := url.Parse(proxyStr)
	if err != nil {
		return false
	}
	
	// Create a custom HTTP client with the proxy
	client := &http.Client{
		Transport: &http.Transport{
			Proxy: http.ProxyURL(proxyURL),
			DialContext: (&net.Dialer{
				Timeout: 5 * time.Second,
			}).DialContext,
		},
		Timeout: 10 * time.Second,
	}
	
	// Try to connect to a reliable website
	resp, err := client.Get("https://www.google.com")
	if err != nil {
		return false
	}
	defer resp.Body.Close()
	
	// Check if the response is successful
	return resp.StatusCode >= 200 && resp.StatusCode < 300
}

// ReloadProxies reloads the proxies from the source
func (s *proxyService) ReloadProxies() error {
	return s.repository.LoadProxies()
}
