import Category from '@/Types/Category';
import Brand from './Brand';

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
    category: Category;
    brand: Brand;
    options?: ProductOption[];
}

export interface ProductOption {
    id: number;
    name: string;
    option_items: OptionItem[];
}

export interface OptionItem {
    id: number;
    name: string;
    price: string;
}
