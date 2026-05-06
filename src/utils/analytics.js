export const loadAnalytics = () => {
    if (typeof window === 'undefined') return;
    if (document.getElementById('analytics-script')) return;

    const consent = localStorage.getItem('analytics-consent') === 'true';
    if (!consent) return;

    const script = document.createElement('script');
    script.id = 'analytics-script';
    script.async = true;
    script.src = 'https://cloud.umami.is/script.js';
    script.setAttribute('data-website-id', import.meta.env.VITE_UMAMI_ID);
    document.head.appendChild(script);
};

export const removeAnalytics = () => {
    if (typeof window === 'undefined') return;
    const script = document.getElementById('analytics-script');
    if (script) {
        script.remove();
    }
};

export const toggleAnalyticsConsent = (enabled) => {
    localStorage.setItem('analytics-consent', enabled);
    if (enabled) {
        loadAnalytics();
    } else {
        removeAnalytics();
    }
};
