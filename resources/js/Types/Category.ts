import Product from './Product';

export default interface Category {
    id: number;
    name: string;
    slug: string;
    featured_image_url?: string;
    products?: Product[];
    products_count?: number;
}
