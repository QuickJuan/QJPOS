import OptionItem from "./OptionItem";

export default interface Option {
    id: number;
    option_name: string;
    product_id: number;
    max_quantity: number;
    is_default: boolean;
    optionImage?: string;
    optionItems: OptionItem[];
}
