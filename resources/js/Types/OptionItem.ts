import Option from "./Option";
import Product, { ProductPackaging } from "./Product";

export default interface OptionItem {
    id: number;
    option_id: number;
    product_id: number | null;
    product_packaging_id: number | null;
    price: number | string | null;
    quantity?: number | null;
    option?: Option;
    product?: Product;
    product_packaging?: ProductPackaging;
}
