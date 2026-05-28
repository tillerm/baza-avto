/**
 * v-reveal — adds an `is-revealed` class once the element scrolls into view.
 * Pair it with the .reveal-on-scroll utility (defined in app.css) to fade-in/slide-up.
 *
 * Usage:
 *   <section v-reveal class="reveal-on-scroll">…</section>
 *   <div v-reveal:0.5 class="reveal-on-scroll">…</div>   // 50% threshold
 *   <div v-reveal.once="false" class="reveal-on-scroll">…</div> // re-trigger on each entry
 */

const REVEALED = 'is-revealed';

const observerCache = new WeakMap();

const createObserver = (el, { threshold = 0.15, once = true } = {}) => {
    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
        // SSR / unsupported — show immediately.
        el.classList.add(REVEALED);
        return null;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add(REVEALED);
                    if (once) observer.unobserve(entry.target);
                } else if (!once) {
                    entry.target.classList.remove(REVEALED);
                }
            });
        },
        { threshold, rootMargin: '0px 0px -40px 0px' }
    );

    observer.observe(el);
    return observer;
};

export default {
    mounted(el, binding) {
        const threshold = binding.arg ? Number(binding.arg) : 0.15;
        const once = binding.modifiers.once !== false; // default true
        const observer = createObserver(el, { threshold, once });
        observerCache.set(el, observer);
    },
    unmounted(el) {
        const observer = observerCache.get(el);
        if (observer) {
            observer.disconnect();
            observerCache.delete(el);
        }
    },
};
