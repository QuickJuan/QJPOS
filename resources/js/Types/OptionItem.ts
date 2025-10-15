import Option from "./Option";
import Product from "./Product";

export default interface OptionItem {
    id: number;
    option_id: number;
    product_id: number;
    product_packaging_id: number;
    price: number;
    option: Option;
    product: Product;
}
