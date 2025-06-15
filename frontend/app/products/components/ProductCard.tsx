import { Product } from '../types';
import Image from 'next/image';

interface ProductCardProps {
  product: Product;
}

export const ProductCard = ({ product }: ProductCardProps) => {
  return (
    <div 
      className="border rounded-lg overflow-hidden shadow-md hover:shadow-lg transition"
    >
      <div className="h-48 overflow-hidden bg-gray-100 flex items-center justify-center">
        {product.image_url ? (
          <Image 
            src={'https://cdn.stocksnap.io/img-thumbs/960w/chalet-wood_7PBFL1ERJT.jpg'} 
            alt={product.title} 
            width={200}
            height={200}
            className="w-full h-full object-cover"
            placeholder="blur"
            blurDataURL="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFdgJCIiGjWwAAAABJRU5ErkJggg=="
          />
        ) : (
          <div className="text-gray-400 text-center p-4">No image available</div>
        )}
      </div>
      <div className="p-4">
        <h2 className="font-semibold text-lg mb-2 line-clamp-2">{product.title}</h2>
        <p className="text-blue-600 font-bold">${product.price.toFixed(2)}</p>
        <p className="text-gray-500 text-sm mt-2">
          Added: {product.created_at}
        </p>
      </div>
    </div>
  );
};
