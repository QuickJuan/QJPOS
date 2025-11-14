import Category from '@/Types/Category';
import Brand from './Brand';
import Option from './Option';

export default interface Product {
    id: number;
    uuid: string;
    name: string;
    receipt_alias: string;
    description: string;
    category_id: number;
    brand_id: number;
    average_cost: string;
    total_onhand: string;
    price: number;
    multiple_packaging: boolean;
    unit_type: string;
    category: Category;
    brand: Brand;
    options?: Option[];
    product_packagings?: ProductPackaging[];
    featured_image_url?: string;
    product_images_urls?: string[];
}

export interface ProductPackaging {
    id: number;
    product_id: number;
    barcode: string;
    cost: string;
    price: string;
    unit_measure: string;
    qty: number;
    featured_image?: string;
    featured_image_url?: string;
}
