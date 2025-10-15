export default interface PageProps {
    flash: {
        success?: string;
        error?: string;
    };
    [key: string]: any;
}
