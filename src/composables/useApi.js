import { ref } from 'vue';

export function useApi() {
    const loading = ref(false);
    const error = ref(null);

    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

    const request = async (endpoint, options = {}) => {
        loading.value = true;
        error.value = null;

        const defaultHeaders = {
            'Accept': 'application/json',
        };

        const token = localStorage.getItem('token');
        if (token) {
            defaultHeaders['Authorization'] = `Bearer ${token}`;
        }

        const baseUrl = apiBase.endsWith('/') ? apiBase.slice(0, -1) : apiBase;
        const url = `${baseUrl}/${endpoint}`;
        
        console.log(`[useApi] Requesting: ${options.method || 'GET'} ${url}`);

        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    ...defaultHeaders,
                    ...options.headers,
                },
            });

            let data = null;
            const text = await response.text();
            
            if (text) {
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    data = text;
                }
            }

            if (!response.ok) {
                const errorMessage = (data && data.message) || `Error ${response.status}: ${response.statusText}`;
                throw new Error(errorMessage);
            }

            return data || {};
        } catch (err) {
            error.value = err.message;
            console.error(`API Request Error [${options.method || 'GET'} ${endpoint}]:`, err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const get = (endpoint) => request(endpoint, { method: 'GET' });
    
    const post = (endpoint, data) => {
        const isFormData = data instanceof FormData;
        return request(endpoint, {
            method: 'POST',
            body: isFormData ? data : JSON.stringify(data),
            headers: isFormData ? {} : { 'Content-Type': 'application/json' },
        });
    };

    const del = (endpoint) => request(endpoint, { method: 'DELETE' });

    return {
        loading,
        error,
        get,
        post,
        del
    };
}
