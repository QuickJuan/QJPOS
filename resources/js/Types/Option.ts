import OptionItem from "./OptionItem";

export default interface Option {
    id: number;
    option_name: string;
    optionImage: string;
    optionItems: OptionItem[];
}
