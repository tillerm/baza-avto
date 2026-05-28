<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const year = computed(() => new Date().getFullYear());

const safeRoute = (name, fallback = '#') => {
    try { return route(name); } catch (_) { return fallback; }
};
</script>

<template>
    <footer class="site-footer">
        <div class="hairline" aria-hidden="true"></div>

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-10 lg:gap-12">
                <!-- Brand column -->
                <div class="col-span-2 md:col-span-1 flex flex-col gap-5">
                    <Link :href="safeRoute('home', '/')" class="footer-brand" aria-label="БазаАвто">
                        <img
                            src="/images/logo-wide.png"
                            alt="БазаАвто"
                            class="footer-brand__mark"
                            loading="lazy"
                        />
                    </Link>
                    <p class="text-sm text-ink-secondary leading-relaxed max-w-xs">
                        Премиальный подбор и доставка автомобилей под заказ. Прозрачная цена, проверенные источники, личный менеджер.
                    </p>
                    <div class="flex items-center gap-2 mt-2">
                        <a
                            href="https://t.me/+uXvETOFXQ99iMjUy"
                            target="_blank"
                            rel="noopener"
                            class="footer-social"
                            aria-label="Telegram"
                        >
                            <i class="pi pi-telegram"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <div class="footer-eyebrow">Каталог</div>
                    <ul class="footer-list">
                        <li><Link :href="safeRoute('catalog.index')" class="footer-link">Все авто</Link></li>
                        <li><Link :href="safeRoute('catalog.calculator')" class="footer-link">Калькулятор доставки</Link></li>
                        <li><Link :href="safeRoute('news.index')" class="footer-link">Новости</Link></li>
                    </ul>
                </div>

                <div>
                    <div class="footer-eyebrow">Компания</div>
                    <ul class="footer-list">
                        <li><Link :href="safeRoute('about')" class="footer-link">О нас</Link></li>
                        <li><Link :href="safeRoute('testimonials.index')" class="footer-link">Отзывы</Link></li>
                        <li><Link :href="safeRoute('faq')" class="footer-link">FAQ</Link></li>
                    </ul>
                </div>

                <div>
                    <div class="footer-eyebrow">Связь</div>
                    <ul class="footer-list">
                        <li><a href="tel:+74951780750" class="footer-link">+7 495 178-07-50</a></li>
                        <li><a href="https://t.me/+uXvETOFXQ99iMjUy" target="_blank" rel="noopener" class="footer-link">Telegram-чат</a></li>
                    </ul>
                </div>
            </div>

            <div class="hairline mt-12 lg:mt-16" aria-hidden="true"></div>

            <div class="pt-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <p class="text-xs text-ink-muted">
                    © {{ year }} БазаАвто. Все права защищены.
                </p>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-ink-muted">
                    <Link :href="safeRoute('privacy.policy')" class="hover:text-ink-primary transition">Политика конфиденциальности</Link>
                    <Link :href="safeRoute('terms.of.service')" class="hover:text-ink-primary transition">Условия использования</Link>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.site-footer {
    margin-top: 6rem;
    background: rgb(var(--bg-elevated));
    color: rgb(var(--text-primary));
    position: relative;
}

.footer-brand {
    display: inline-flex;
    align-items: center;
    color: rgb(var(--text-primary));
    width: fit-content;
}

.footer-brand__mark {
    display: block;
    height: 56px;
    width: auto;
    transition: transform var(--dur-base) ease;
}

.footer-brand:hover .footer-brand__mark {
    transform: scale(1.03);
}

.footer-eyebrow {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgb(var(--text-muted));
    margin-bottom: 1rem;
}

.footer-list {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.footer-link {
    display: inline-flex;
    align-items: center;
    font-size: 0.9375rem;
    color: rgb(var(--text-secondary));
    transition: color var(--dur-base) ease, transform var(--dur-base) ease;
}

.footer-link:hover {
    color: rgb(var(--accent));
    transform: translateX(2px);
}

.footer-social {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 9999px;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    color: rgb(var(--text-secondary));
    transition: color var(--dur-base) ease, border-color var(--dur-base) ease, transform var(--dur-base) ease;
}

.footer-social:hover {
    color: rgb(var(--accent));
    border-color: rgb(var(--accent) / 0.4);
    transform: translateY(-2px);
}
</style>
