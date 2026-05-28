const getCounterId = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    const counterId = Number(window.yandexMetrikaCounterId);

    return Number.isFinite(counterId) ? counterId : null;
};

let lastTrackedUrl = typeof window !== 'undefined' ? window.document.referrer || '' : '';

const camelToSnake = (value) =>
    value
        .replace(/^[A-Z]/, (match) => match.toLowerCase())
        .replace(/[A-Z]/g, (match) => `_${match.toLowerCase()}`);

const normalizeParamValue = (value) => {
    if (value === 'true') return true;
    if (value === 'false') return false;
    if (value !== '' && !Number.isNaN(Number(value))) return Number(value);

    return value;
};

const extractParamsFromDataset = (dataset) => {
    const params = {};

    Object.entries(dataset).forEach(([key, value]) => {
        if (!key.startsWith('metrika') || key === 'metrikaGoal') {
            return;
        }

        const paramName = camelToSnake(key.slice('metrika'.length));

        if (!paramName) {
            return;
        }

        params[paramName] = normalizeParamValue(value);
    });

    return params;
};

const inferGoalByHref = (href) => {
    if (!href) {
        return null;
    }

    const normalizedHref = href.toLowerCase();

    if (normalizedHref.startsWith('tel:')) {
        return 'phone_click';
    }

    if (normalizedHref.startsWith('mailto:')) {
        return 'email_click';
    }

    if (normalizedHref.includes('t.me')) {
        return 'telegram_click';
    }

    return null;
};

export const reachGoal = (goal, params = {}) => {
    const counterId = getCounterId();

    if (!counterId || typeof window === 'undefined' || typeof window.ym !== 'function' || !goal) {
        return;
    }

    window.ym(counterId, 'reachGoal', goal, params);
};

export const trackYandexPageView = () => {
    const counterId = getCounterId();

    if (!counterId || typeof window === 'undefined' || typeof window.ym !== 'function') {
        return;
    }

    const currentUrl = window.location.href;

    window.ym(counterId, 'hit', currentUrl, {
        title: document.title,
        referer: lastTrackedUrl || document.referrer || undefined,
    });

    lastTrackedUrl = currentUrl;
};

export const setupMetrikaTracking = () => {
    if (typeof document === 'undefined') {
        return;
    }

    document.addEventListener(
        'click',
        (event) => {
            const target = event.target instanceof Element ? event.target.closest('[data-metrika-goal], a[href]') : null;

            if (!target) {
                return;
            }

            const goal = target.dataset.metrikaGoal || inferGoalByHref(target.getAttribute('href') || '');

            if (!goal) {
                return;
            }

            const params = {
                page: window.location.pathname,
                ...extractParamsFromDataset(target.dataset),
            };

            if (target instanceof HTMLAnchorElement && target.href) {
                params.href = target.href;
            }

            reachGoal(goal, params);
        },
        true,
    );
};
