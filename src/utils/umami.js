export const loadUmami = () => {
    if (typeof window === 'undefined') return;
    if (document.getElementById('umami-script')) return;
    
    const consent = localStorage.getItem('umami-consent') === 'true';
    if (!consent) return;

    const script = document.createElement('script');
    script.id = 'umami-script';
    script.async = true;
    script.src = 'https://cloud.umami.is/script.js';
    script.setAttribute('data-website-id', import.meta.env.VITE_UMAMI_ID);
    document.head.appendChild(script);
};

export const removeUmami = () => {
    if (typeof window === 'undefined') return;
    const script = document.getElementById('umami-script');
    if (script) {
        script.remove();
    }
};

export const toggleUmamiConsent = (enabled) => {
    localStorage.setItem('umami-consent', enabled);
    if (enabled) {
        loadUmami();
    } else {
        removeUmami();
    }
};
