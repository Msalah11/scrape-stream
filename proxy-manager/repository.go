package main

import (
	"bufio"
	"log"
	"math/rand"
	"os"
	"strings"
	"sync"
	"time"
)

type ProxyRepository interface {
	GetRandomProxy() (string, error)
	LoadProxies() error
	AddProxy(proxy string) error
	GetAllProxies() []string
}

type proxyRepository struct {
	proxies []string
	mutex   sync.RWMutex
}

func NewProxyRepository() ProxyRepository {
	repo := &proxyRepository{
		proxies: []string{},
	}
	
	if err := repo.LoadProxies(); err != nil {
		// Add some default free proxies as fallback
		repo.proxies = []string{
			"http://103.152.112.162:80",
			"http://103.83.232.122:80",
			"http://51.159.115.233:3128",
			"http://20.111.54.16:80",
			"http://103.117.192.14:80",
		}
		
		if _, ok := err.(*os.PathError); !ok {
			log.Printf("Error loading proxies: %v. Using default proxies.", err)
		}
	}
	
	return repo
}

func (r *proxyRepository) GetRandomProxy() (string, error) {
	r.mutex.RLock()
	defer r.mutex.RUnlock()
	
	if len(r.proxies) == 0 {
		return "", ErrNoProxiesAvailable
	}
	
	// Get a random proxy
	rand.Seed(time.Now().UnixNano())
	randomIndex := rand.Intn(len(r.proxies))
	return r.proxies[randomIndex], nil
}

func (r *proxyRepository) LoadProxies() error {
	proxyFilePath := os.Getenv("PROXY_FILE")
	if proxyFilePath == "" {
		proxyFilePath = "proxies.txt"
	}
	
	file, err := os.Open(proxyFilePath)
	if err != nil {
		return err
	}
	defer file.Close()
	
	var proxies []string
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		proxy := strings.TrimSpace(scanner.Text())
		if proxy != "" {
			proxies = append(proxies, proxy)
		}
	}
	
	if err := scanner.Err(); err != nil {
		return err
	}
	
	r.mutex.Lock()
	r.proxies = proxies
	r.mutex.Unlock()
	
	return nil
}

func (r *proxyRepository) AddProxy(proxy string) error {
	r.mutex.Lock()
	defer r.mutex.Unlock()
	
	for _, p := range r.proxies {
		if p == proxy {
			return nil
		}
	}
	
	r.proxies = append(r.proxies, proxy)
	return nil
}

func (r *proxyRepository) GetAllProxies() []string {
	r.mutex.RLock()
	defer r.mutex.RUnlock()
	
	proxiesCopy := make([]string, len(r.proxies))
	copy(proxiesCopy, r.proxies)
	
	return proxiesCopy
}
