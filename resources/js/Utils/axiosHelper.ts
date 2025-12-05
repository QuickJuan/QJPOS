import axios, { AxiosError, AxiosResponse } from 'axios';

/**
 * Format validation errors from server response
 * @param errors - Errors object from server
 * @returns Formatted error string
 */
export const formatValidationErrors = (errors: any): string => {
    if (!errors || typeof errors !== 'object') {
        return 'An error occurred.';
    }

    const errorMessages: string[] = [];

    Object.entries(errors).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            errorMessages.push(...value.map(msg => String(msg)));
        } else if (typeof value === 'string') {
            errorMessages.push(value);
        }
    });

    return errorMessages.length > 0
        ? errorMessages.join(', ')
        : 'An error occurred.';
};

/**
 * Generic POST request with error handling
 * @param url - API endpoint URL
 * @param data - Request payload
 * @param options - Additional options (showToast, successCallback, errorCallback)
 */
/**
 * Generic POST request with error handling
 * @param url - API endpoint URL
 * @param data - Request payload
 * @param options - Additional options (successCallback, errorCallback)
 */
export const httpPost = async <T = any>(
    url: string,
    data: any = {},
    options?: {
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<any> => {
    try {
        const response = await axios.post<T>(url, data, {
            validateStatus: () => true,
        });

        if (response.status >= 200 && response.status < 300) {
            if (options?.successCallback) {
                options.successCallback(response.data);
            }
            // If response already has success property, return it directly
            if (response.data?.success !== undefined) {
                return response.data;
            }
            return { success: true, data: response.data };
        } else {
            let errorMessage = 'An error occurred.';
            if (response.data?.errors) {
                errorMessage = formatValidationErrors(response.data.errors);
            } else if (response.data?.message) {
                errorMessage = response.data.message;
            }

            if (options?.errorCallback) {
                options.errorCallback(response.data);
            }

            return { success: false, error: errorMessage };
        }
    } catch (error: any) {
        let errorMessage = 'An error occurred.';
        if (error.message) {
            errorMessage = error.message;
        }

        if (options?.errorCallback) {
            options.errorCallback(error);
        }

        return { success: false, error: errorMessage };
    }
};

/**
 * Generic GET request with error handling
 * @param url - API endpoint URL
 * @param options - Additional options (successCallback, errorCallback)
 */
export const httpGet = async <T = any>(
    url: string,
    options?: {
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.get<T>(url, {
            validateStatus: () => true,
        });

        if (response.status >= 200 && response.status < 300) {
            if (options?.successCallback) {
                options.successCallback(response.data);
            }
            return { success: true, data: response.data };
        } else {
            let errorMessage = 'An error occurred.';
            if (response.data?.errors) {
                errorMessage = formatValidationErrors(response.data.errors);
            } else if (response.data?.message) {
                errorMessage = response.data.message;
            }

            if (options?.errorCallback) {
                options.errorCallback(response.data);
            }

            return { success: false, error: errorMessage };
        }
    } catch (error: any) {
        let errorMessage = 'An error occurred.';
        if (error.message) {
            errorMessage = error.message;
        }

        if (options?.errorCallback) {
            options.errorCallback(error);
        }

        return { success: false, error: errorMessage };
    }
};

/**
 * Generic PUT request with error handling
 * @param url - API endpoint URL
 * @param data - Request payload
 * @param options - Additional options (successCallback, errorCallback)
 */
export const httpPut = async <T = any>(
    url: string,
    data: any = {},
    options?: {
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.put<T>(url, data, {
            validateStatus: () => true,
        });

        if (response.status >= 200 && response.status < 300) {
            if (options?.successCallback) {
                options.successCallback(response.data);
            }
            return { success: true, data: response.data };
        } else {
            let errorMessage = 'An error occurred.';
            if (response.data?.errors) {
                errorMessage = formatValidationErrors(response.data.errors);
            } else if (response.data?.message) {
                errorMessage = response.data.message;
            }

            if (options?.errorCallback) {
                options.errorCallback(response.data);
            }

            return { success: false, error: errorMessage };
        }
    } catch (error: any) {
        let errorMessage = 'An error occurred.';
        if (error.message) {
            errorMessage = error.message;
        }

        if (options?.errorCallback) {
            options.errorCallback(error);
        }

        return { success: false, error: errorMessage };
    }
};

/**
 * Generic PATCH request with error handling
 * @param url - API endpoint URL
 * @param data - Request payload
 * @param options - Additional options (successCallback, errorCallback)
 */
export const httpPatch = async <T = any>(
    url: string,
    data: any = {},
    options?: {
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.patch<T>(url, data, {
            validateStatus: () => true,
        });

        if (response.status >= 200 && response.status < 300) {
            if (options?.successCallback) {
                options.successCallback(response.data);
            }
            return { success: true, data: response.data };
        } else {
            let errorMessage = 'An error occurred.';
            if (response.data?.errors) {
                errorMessage = formatValidationErrors(response.data.errors);
            } else if (response.data?.message) {
                errorMessage = response.data.message;
            }

            if (options?.errorCallback) {
                options.errorCallback(response.data);
            }

            return { success: false, error: errorMessage };
        }
    } catch (error: any) {
        let errorMessage = 'An error occurred.';
        if (error.message) {
            errorMessage = error.message;
        }

        if (options?.errorCallback) {
            options.errorCallback(error);
        }

        return { success: false, error: errorMessage };
    }
};

/**
 * Generic DELETE request with error handling
 * @param url - API endpoint URL
 * @param options - Additional options (successCallback, errorCallback)
 */
export const httpDelete = async <T = any>(
    url: string,
    options?: {
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.delete<T>(url, {
            validateStatus: () => true,
        });

        if (response.status >= 200 && response.status < 300) {
            if (options?.successCallback) {
                options.successCallback(response.data);
            }
            return { success: true, data: response.data };
        } else {
            let errorMessage = 'An error occurred.';
            if (response.data?.errors) {
                errorMessage = formatValidationErrors(response.data.errors);
            } else if (response.data?.message) {
                errorMessage = response.data.message;
            }

            if (options?.errorCallback) {
                options.errorCallback(response.data);
            }

            return { success: false, error: errorMessage };
        }
    } catch (error: any) {
        let errorMessage = 'An error occurred.';
        if (error.message) {
            errorMessage = error.message;
        }

        if (options?.errorCallback) {
            options.errorCallback(error);
        }

        return { success: false, error: errorMessage };
    }
};
