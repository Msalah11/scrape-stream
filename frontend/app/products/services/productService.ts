import { Product } from '../types';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/products';

interface PaginatedResponse {
  success: boolean;
  message: string;
  data: {
    products: Product[];
    meta: {
      total: number;
      count: number;
      per_page: number;
      current_page: number;
      total_pages: number;
    };
  };
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
    links: Array<{
      url: string | null;
      label: string;
      active: boolean;
    }>;
  };
}

// Type guard to check if response matches our expected format
function isPaginatedResponse(data: unknown): data is PaginatedResponse {
  return (
    typeof data === 'object' && 
    data !== null && 
    'success' in data && 
    'data' in data && 
    typeof data.data === 'object' && 
    data.data !== null && 
    'products' in data.data && 
    Array.isArray((data.data as { products: unknown }).products)
  );
}

export const productService = {
  async getProducts(): Promise<Product[]> {
    try {
      const response = await fetch(API_URL);
      
      if (!response.ok) {
        throw new Error(`Error fetching products: ${response.status}`);
      }
      
      const data: unknown = await response.json();
      
      // Handle the specific API response format from our Laravel backend
      if (isPaginatedResponse(data)) {
        return data.data.products;
      } 
      // Fallback for direct array response
      else if (Array.isArray(data)) {
        return data as Product[];
      } 
      // Fallback for simple wrapped response
      else if (
        typeof data === 'object' && 
        data !== null && 
        'data' in data && 
        Array.isArray((data as { data: unknown }).data)
      ) {
        return (data as { data: Product[] }).data;
      } 
      else {
        console.warn('Unexpected API response format:', data);
        return [];
      }
    } catch (error) {
      console.error('Failed to fetch products:', error);
      throw error;
    }
  }
};
