import { useState, useEffect } from 'react';
import { Product } from '../types';
import { productService } from '../services/productService';

export const useProducts = (refreshInterval = 30000) => {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [lastUpdated, setLastUpdated] = useState<Date | null>(null);

  const fetchProducts = async () => {
    try {
      setLoading(true);
      const data = await productService.getProducts();
      setProducts(data);
      setLastUpdated(new Date());
      setError(null);
    } catch (error) {
      console.error('Error fetching products:', error);
      setError('Failed to load products. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProducts();
    
    // Set up auto-refresh
    const intervalId = setInterval(fetchProducts, refreshInterval);
    
    // Clean up interval on component unmount
    return () => clearInterval(intervalId);
  }, [refreshInterval]);

  return {
    products,
    loading,
    error,
    lastUpdated,
    refetch: fetchProducts
  };
};
