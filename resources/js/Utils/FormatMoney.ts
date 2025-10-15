export const formatMoney = (price: number | string,
    currency: string = "PHP",
    locale: string = "en-US"): any => {
    if (!price) return "";

    try {
        return new Intl.NumberFormat(locale, {
            style: "currency",
            currency,
        }).format(price);
    } catch {
        return price;
    }
}
