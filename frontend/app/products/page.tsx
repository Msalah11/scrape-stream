'use client';

import Link from 'next/link';
import { useProducts } from './hooks/useProducts';
import { ProductGrid } from './components/ProductGrid';
import { LoadingSpinner } from './components/LoadingSpinner';
import { ErrorMessage } from './components/ErrorMessage';

export default function ProductsPage() {
  const { products, loading, error, lastUpdated, refetch } = useProducts();

  return (
    <div className="container mx-auto px-4 py-8">
      <header className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold">Scraped Products</h1>
        <div className="flex gap-4">
          <button
            onClick={() => refetch()}
            className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
            disabled={loading}
          >
            Refresh Now
          </button>
          <Link 
            href="/"
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
          >
            Back to Home
          </Link>
        </div>
      </header>

      {loading && products.length === 0 && <LoadingSpinner />}

      {error && <ErrorMessage message={error} />}

      <ProductGrid products={products} />

      {products.length > 0 && lastUpdated && (
        <div className="mt-4 text-center text-sm text-gray-500">
          Last updated: {lastUpdated.toLocaleTimeString()}
          <div className="text-xs text-gray-400">
            Auto-refreshes every 30 seconds
          </div>
        </div>
      )}
    </div>
  );
}
