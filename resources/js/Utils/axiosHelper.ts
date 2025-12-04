import axios, { AxiosError, AxiosResponse } from 'axios';
import { useToast } from 'primevue';

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
 * Show error toast with formatted message
 * @param title - Toast title/summary
 * @param errors - Error object or message string
 * @param duration - Toast duration in ms (default: 3000)
 */
export const showErrorToast = (
    title: string,
    errors: any,
    duration: number = 3000
) => {
    try {
        const toast = useToast();
        const errorMessage = typeof errors === 'string'
            ? errors
            : formatValidationErrors(errors);

        toast.add({
            severity: "error",
            summary: title,
            detail: errorMessage,
            life: duration,
        });
    } catch (e) {
        // Toast not available, log to console
        console.error(`${title}:`, typeof errors === 'string' ? errors : formatValidationErrors(errors));
    }
};

/**
 * Show success toast
 * @param title - Toast title/summary
 * @param message - Success message
 * @param duration - Toast duration in ms (default: 3000)
 */
export const showSuccessToast = (
    title: string,
    message: string,
    duration: number = 3000
) => {
    try {
        const toast = useToast();
        toast.add({
            severity: "success",
            summary: title,
            detail: message,
            life: duration,
        });
    } catch (e) {
        // Toast not available, log to console
        console.log(`${title}: ${message}`);
    }
};

/**
 * Generic POST request with error handling
 * @param url - API endpoint URL
 * @param data - Request payload
 * @param options - Additional options (showToast, successCallback, errorCallback)
 */
export const httpPost = async <T = any>(
    url: string,
    data: any = {},
    options?: {
        showToast?: boolean;
        successTitle?: string;
        errorTitle?: string;
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.post<T>(url, data);

        if (options?.showToast) {
            showSuccessToast(
                options.successTitle || 'Success',
                'Operation completed successfully'
            );
        }

        if (options?.successCallback) {
            options.successCallback(response.data);
        }

        return { success: true, data: response.data };
    } catch (error: any) {
        let errorMessage = 'An error occurred.';

        if (error.response?.data?.errors) {
            errorMessage = formatValidationErrors(error.response.data.errors);
        } else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        if (options?.showToast) {
            showErrorToast(options.errorTitle || 'Error', errorMessage);
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
 * @param options - Additional options (showToast, successCallback, errorCallback)
 */
export const httpGet = async <T = any>(
    url: string,
    options?: {
        showToast?: boolean;
        successTitle?: string;
        errorTitle?: string;
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.get<T>(url);

        if (options?.successCallback) {
            options.successCallback(response.data);
        }

        return { success: true, data: response.data };
    } catch (error: any) {
        let errorMessage = 'An error occurred.';

        if (error.response?.data?.errors) {
            errorMessage = formatValidationErrors(error.response.data.errors);
        } else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        if (options?.showToast) {
            showErrorToast(options.errorTitle || 'Error', errorMessage);
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
 * @param options - Additional options (showToast, successCallback, errorCallback)
 */
export const httpPut = async <T = any>(
    url: string,
    data: any = {},
    options?: {
        showToast?: boolean;
        successTitle?: string;
        errorTitle?: string;
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.put<T>(url, data);

        if (options?.showToast) {
            showSuccessToast(
                options.successTitle || 'Success',
                'Operation completed successfully'
            );
        }

        if (options?.successCallback) {
            options.successCallback(response.data);
        }

        return { success: true, data: response.data };
    } catch (error: any) {
        let errorMessage = 'An error occurred.';

        if (error.response?.data?.errors) {
            errorMessage = formatValidationErrors(error.response.data.errors);
        } else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        if (options?.showToast) {
            showErrorToast(options.errorTitle || 'Error', errorMessage);
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
 * @param options - Additional options (showToast, successCallback, errorCallback)
 */
export const httpPatch = async <T = any>(
    url: string,
    data: any = {},
    options?: {
        showToast?: boolean;
        successTitle?: string;
        errorTitle?: string;
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.patch<T>(url, data);

        if (options?.showToast) {
            showSuccessToast(
                options.successTitle || 'Success',
                'Operation completed successfully'
            );
        }

        if (options?.successCallback) {
            options.successCallback(response.data);
        }

        return { success: true, data: response.data };
    } catch (error: any) {
        let errorMessage = 'An error occurred.';

        if (error.response?.data?.errors) {
            errorMessage = formatValidationErrors(error.response.data.errors);
        } else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        if (options?.showToast) {
            showErrorToast(options.errorTitle || 'Error', errorMessage);
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
 * @param options - Additional options (showToast, successCallback, errorCallback)
 */
export const httpDelete = async <T = any>(
    url: string,
    options?: {
        showToast?: boolean;
        successTitle?: string;
        errorTitle?: string;
        successCallback?: (response: T) => void;
        errorCallback?: (error: any) => void;
    }
): Promise<{ success: boolean; data?: T; error?: string }> => {
    try {
        const response = await axios.delete<T>(url);

        if (options?.showToast) {
            showSuccessToast(
                options.successTitle || 'Success',
                'Operation completed successfully'
            );
        }

        if (options?.successCallback) {
            options.successCallback(response.data);
        }

        return { success: true, data: response.data };
    } catch (error: any) {
        let errorMessage = 'An error occurred.';

        if (error.response?.data?.errors) {
            errorMessage = formatValidationErrors(error.response.data.errors);
        } else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        if (options?.showToast) {
            showErrorToast(options.errorTitle || 'Error', errorMessage);
        }

        if (options?.errorCallback) {
            options.errorCallback(error);
        }

        return { success: false, error: errorMessage };
    }
};
