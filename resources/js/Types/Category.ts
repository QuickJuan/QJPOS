import Product from './Product';

export default interface Category {
    id: number;
    name: string;
    featured_image_url?: string;
    products?: Product[];
    products_count?: number;
}
