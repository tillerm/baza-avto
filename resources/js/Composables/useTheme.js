import { ref, computed, watch } from 'vue';

const STORAGE_KEY = 'theme';

const isBrowser = typeof window !== 'undefined' && typeof document !== 'undefined';

const detectAreaFromPath = (pathname) => {
    if (!pathname) return 'public';
    const crmRoots = ['/crm', '/dashboard', '/profile', '/login', '/register',
        '/forgot-password', '/reset-password', '/two-factor', '/user/', '/api/'];
    return crmRoots.some((p) => pathname === p || pathname.indexOf(p) === 0) ? 'crm' : 'public';
};

const readStoredTheme = () => {
    if (!isBrowser) return null;
    try {
        const v = window.localStorage.getItem(STORAGE_KEY);
        return v === 'dark' || v === 'light' ? v : null;
    } catch (_) {
        return null;
    }
};

const writeStoredTheme = (theme) => {
    if (!isBrowser) return;
    try { window.localStorage.setItem(STORAGE_KEY, theme); } catch (_) {}
};

const detectInitialTheme = () => {
    if (!isBrowser) return 'dark';
    const stored = readStoredTheme();
    if (stored) return stored;
    // Public default: dark (matches the brand premium direction).
    return 'dark';
};

// Singleton state — shared across all useTheme() consumers in the app.
const theme = ref(detectInitialTheme());
const area = ref(isBrowser ? detectAreaFromPath(window.location.pathname) : 'public');
let initialized = false;

const applyTheme = () => {
    if (!isBrowser) return;
    const html = document.documentElement;
    if (area.value === 'crm') {
        html.classList.remove('dark');
        html.classList.add('light');
        html.setAttribute('data-theme', 'light');
        html.setAttribute('data-area', 'crm');
        return;
    }
    html.classList.remove('light');
    html.classList.toggle('dark', theme.value === 'dark');
    html.setAttribute('data-theme', theme.value);
    html.setAttribute('data-area', 'public');
};

watch(theme, (next) => {
    if (area.value === 'crm') return;
    writeStoredTheme(next);
    applyTheme();
}, { flush: 'post' });

watch(area, () => applyTheme(), { flush: 'post' });

export function syncThemeWithRoute(pathname) {
    if (!isBrowser) return;
    area.value = detectAreaFromPath(pathname || window.location.pathname);
}

export function useTheme() {
    if (isBrowser && !initialized) {
        initialized = true;

        // React to OS-level theme change ONLY when user has not explicitly chosen.
        const mql = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = (e) => {
            if (readStoredTheme()) return; // user choice wins
            if (area.value === 'crm') return;
            theme.value = e.matches ? 'dark' : 'light';
        };
        try {
            mql.addEventListener('change', handler);
        } catch (_) {
            // Safari < 14 fallback
            mql.addListener && mql.addListener(handler);
        }

        applyTheme();
    }

    const isDark = computed(() => area.value !== 'crm' && theme.value === 'dark');
    const isPublic = computed(() => area.value === 'public');

    const toggle = () => {
        if (area.value === 'crm') return;
        theme.value = theme.value === 'dark' ? 'light' : 'dark';
    };

    const setTheme = (next) => {
        if (area.value === 'crm') return;
        if (next !== 'dark' && next !== 'light') return;
        theme.value = next;
    };

    return { theme, isDark, isPublic, area, toggle, setTheme };
}
