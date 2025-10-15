export default interface Branch {
    id: number;
    branch_code: string;
    name: string;
    address: string;
    phone: string;
    email: string;
    contact_person: string;
    long_lat: string;
    is_active: boolean | string;
    tin: string;
    registration_number: string;
}
