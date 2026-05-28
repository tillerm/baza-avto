<script setup>
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Section from '@/Components/Site/Section.vue';
import CarCard from '@/Components/Site/CarCard.vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { reachGoal } from '@/lib/metrika';

const props = defineProps({
    popularCars: { type: Array, default: () => [] },
    teamMembers: { type: Array, default: () => [] },
});

/* ----------- Hero slider state ----------- */
// Use local images only — external CDNs (pngmart, pngimg) sometimes get
// blocked / time out which makes the slide invisible.
const heroSlidesRaw = [
    { key: 'hero-1', src: '/images/hero-image.png', alt: 'Премиум авто под ключ' },
    { key: 'hero-2', src: '/images/main.png', alt: 'Премиум авто под ключ' },
];

const failedSlides = ref(new Set());
const heroSlides = computed(() => heroSlidesRaw.filter(s => !failedSlides.value.has(s.key)));

const onSlideError = (key) => {
    failedSlides.value.add(key);
    if (heroIndex.value >= heroSlides.value.length) heroIndex.value = 0;
};

const heroIndex = ref(0);
const heroDragging = ref(false);
const heroDragAxis = ref(null);
const heroPointerId = ref(null);
const heroDragStartX = ref(0);
const heroDragStartY = ref(0);
const heroDragDeltaX = ref(0);
const HERO_AUTOPLAY_MS = 4500;
const HERO_SWIPE_THRESHOLD_PX = 70;
let heroAutoplayTimer = null;

const stopHeroAutoplay = () => {
    if (heroAutoplayTimer) { clearInterval(heroAutoplayTimer); heroAutoplayTimer = null; }
};
const startHeroAutoplay = () => {
    stopHeroAutoplay();
    if (typeof window === 'undefined') return;
    if (heroSlides.value.length <= 1) return;
    heroAutoplayTimer = window.setInterval(() => {
        if (typeof document !== 'undefined' && document.hidden) return;
        heroIndex.value = (heroIndex.value + 1) % heroSlides.value.length;
    }, HERO_AUTOPLAY_MS);
};
const heroPrev = () => { if (heroSlides.value.length > 1) heroIndex.value = (heroIndex.value - 1 + heroSlides.value.length) % heroSlides.value.length; };
const heroNext = () => { if (heroSlides.value.length > 1) heroIndex.value = (heroIndex.value + 1) % heroSlides.value.length; };

onMounted(() => startHeroAutoplay());
onUnmounted(() => stopHeroAutoplay());

const onHeroPointerDown = (event) => {
    if (heroSlides.value.length <= 1) return;
    if (event.pointerType === 'mouse' && event.button !== 0) return;
    heroDragging.value = true;
    heroDragAxis.value = null;
    heroPointerId.value = event.pointerId ?? null;
    heroDragStartX.value = event.clientX ?? 0;
    heroDragStartY.value = event.clientY ?? 0;
    heroDragDeltaX.value = 0;
    stopHeroAutoplay();
};
const onHeroPointerMove = (event) => {
    if (!heroDragging.value) return;
    if (heroPointerId.value !== null && event.pointerId !== heroPointerId.value) return;
    const dx = (event.clientX ?? 0) - heroDragStartX.value;
    const dy = (event.clientY ?? 0) - heroDragStartY.value;
    if (!heroDragAxis.value) {
        if (Math.abs(dx) < 6 && Math.abs(dy) < 6) return;
        heroDragAxis.value = Math.abs(dx) > Math.abs(dy) ? 'x' : 'y';
        if (heroDragAxis.value === 'y') {
            heroDragging.value = false; heroDragDeltaX.value = 0; heroPointerId.value = null;
            startHeroAutoplay(); return;
        }
        try { event.currentTarget?.setPointerCapture?.(event.pointerId); } catch (_) {}
    }
    if (heroDragAxis.value !== 'x') return;
    heroDragDeltaX.value = dx;
};
const finishHeroSwipe = (event) => {
    if (!heroDragging.value) return;
    if (heroPointerId.value !== null && event?.pointerId && event.pointerId !== heroPointerId.value) return;
    const dx = heroDragDeltaX.value;
    heroDragging.value = false; heroDragAxis.value = null; heroPointerId.value = null; heroDragDeltaX.value = 0;
    if (dx <= -HERO_SWIPE_THRESHOLD_PX) heroNext();
    else if (dx >= HERO_SWIPE_THRESHOLD_PX) heroPrev();
    startHeroAutoplay();
};
const cancelHeroSwipe = () => {
    heroDragging.value = false; heroDragAxis.value = null; heroPointerId.value = null; heroDragDeltaX.value = 0;
    startHeroAutoplay();
};

/* ----------- Static content ----------- */
const advantages = [
    { eyebrow: 'Таможня и финансы', title: 'Льготное оформление', desc: 'Прямые поставки и растаможка по льготным условиям снижают итоговую стоимость.', icon: 'pi-shield' },
    { eyebrow: 'Подбор и проверка', title: 'Эксперты на месте', desc: 'Диагностика, история, торг и страховка — проверяем каждую машину перед выкупом.', icon: 'pi-search' },
    { eyebrow: 'Логистика', title: 'Доставка в ваш регион', desc: 'Организуем оплату, выкуп и поставку «под ключ», привозим авто туда, где вы.', icon: 'pi-truck' },
    { eyebrow: 'Юрподдержка', title: 'Документы без стресса', desc: 'Сопровождаем оформление, помогаем с ДКП и постановкой на учёт.', icon: 'pi-file' },
    { eyebrow: 'Связь', title: '24/7 на связи', desc: 'Отвечаем в любое время, держим вас в курсе статуса сделки.', icon: 'pi-comments' },
    { eyebrow: 'Экономия', title: 'Цены ниже рынка', desc: 'Оптимизируем бюджет за счёт прямых поставок и прозрачных условий.', icon: 'pi-chart-line' },
];

const kpiHighlights = [
    { value: '500+', label: 'Доставленных авто' },
    { value: '5–7', label: 'Дней — средний срок' },
    { value: '24/7', label: 'Поддержка клиентов' },
    { value: '100%', label: 'Прозрачные условия' },
];

const faqItems = [
    { q: 'Как происходит доставка?', a: 'После заключения договора и прохождения льготной процедуры таможни доставляем автовозом до вашего города. Адрес доставки заранее согласуем и пропишем в договоре.' },
    { q: 'Есть ли подводные камни при льготной процедуре таможни?', a: 'Подводных камней нет. Единственное ограничение — на переоформление в течение 1 года: авто берёте для личных целей, без коммерческого использования.' },
    { q: 'Сроки доставки', a: 'Средний срок доставки 5–7 дней, зависит от региона. По договору обязуемся доставить и поставить на учёт в течение 10 дней.' },
    { q: 'Как происходит оплата?', a: 'Оплата поэтапно. В договоре будут реквизиты для оплаты наших услуг (логистика, сопровождение, осмотр, постановка на учёт). В среднем 80–160 тыс. руб. в зависимости от региона.' },
    { q: 'Какие гарантии безопасности вы предоставляете?', a: 'Репутация, документы на авто, видео-знакомство, обмен паспортными данными, прямые эфиры. Все фиксируется, пошлины оплачиваются напрямую в таможню. Оплата авто — по факту пригона и постановки на вас.' },
    { q: 'Одинаковы ли условия при подборе авто под заказ?', a: 'Почти да: заключаем договор на подбор и пригон, ищем, проверяем и договариваемся о выкупе. При выкупе наше руководство оплачивает 85% суммы, 15% оплачиваете вы. Дальше пригон по стандартному процессу.' },
    { q: 'Что лучше: Самовывоз или Пригон?', a: 'Пригон — комфортнее: вы не тратите время, есть личный менеджер, который ведёт авто и вас до полного оформления. Самовывоз — забираете авто сами и проходите все этапы самостоятельно.' },
    { q: 'Как заключить договор, если у меня в регионе нет вашего филиала?', a: 'Мы заполняем договор со своей стороны и отправляем удобным способом. Ваша задача — заполнить и прислать скан-копию, оригинал остаётся у вас.' },
];

const processSteps = [
    { title: 'Заявка', desc: 'Оставьте заявку на подбор и подбор бюджета.' },
    { title: 'Консультация', desc: 'Подбираем авто и обсуждаем условия с менеджером.' },
    { title: 'Договор', desc: 'Заключаем договор с прозрачными условиями.' },
    { title: 'Транспортировка', desc: 'Запускаем процесс перевозки и выезд на таможню.' },
    { title: 'Таможня', desc: 'Подаём документы и оплачиваем таможенные пошлины.' },
    { title: 'СБКТС и ЕПТС', desc: 'Получаем все необходимые документы.' },
    { title: 'Доставка', desc: 'Привозим автомобиль в ваш регион.' },
    { title: 'Осмотр и оформление', desc: 'Осмотр, ДКП и постановка на учёт в ГИБДД.' },
    { title: 'Оплата', desc: 'Финальная оплата удобным способом.' },
];

/* ----------- Calculator modal ----------- */
const showCalculatorModal = ref(false);
const openStep = ref(null);
const calculatorSubmitting = ref(false);
const calculatorError = ref('');
const calculatorForm = ref({
    model: '', condition: 'any', year: '', city: '', name: '', phone: '', agree: false, mailing_agree: false,
});

const resetCalculatorForm = () => {
    calculatorForm.value = {
        model: '', condition: 'any', year: '', city: '', name: '', phone: '', agree: false, mailing_agree: false,
    };
};

const normalizePhoneDigits = (value = '') => {
    let digits = value.replace(/\D/g, '');
    if (!digits) return '';
    if (digits.startsWith('8')) digits = `7${digits.slice(1)}`;
    else if (!digits.startsWith('7')) digits = `7${digits}`;
    return digits.slice(0, 11);
};

const formatPhone = (value = '') => {
    const digits = normalizePhoneDigits(value);
    if (!digits) return '';
    const country = '+7';
    const area = digits.slice(1, 4);
    const first = digits.slice(4, 7);
    const second = digits.slice(7, 9);
    const third = digits.slice(9, 11);
    let f = country;
    if (area) f += ` (${area}`;
    if (digits.length >= 4) f += ')';
    if (first) f += ` ${first}`;
    if (second) f += `-${second}`;
    if (third) f += `-${third}`;
    return f;
};

const handlePhoneInput = (event) => { calculatorForm.value.phone = formatPhone(event.target.value); };

const submitCalculator = async () => {
    calculatorError.value = '';
    if (!calculatorForm.value.agree) {
        alert('Пожалуйста, подтвердите согласие с политикой конфиденциальности.');
        return;
    }
    calculatorSubmitting.value = true;
    try {
        await axios.post(route('leads.calculator.store'), calculatorForm.value);
        reachGoal('calculator_submit', {
            location: 'home_modal',
            has_year: Boolean(calculatorForm.value.year),
            has_city: Boolean(calculatorForm.value.city),
            mailing_agree: Boolean(calculatorForm.value.mailing_agree),
        });
        showCalculatorModal.value = false;
        resetCalculatorForm();
        alert('Заявка отправлена. Мы свяжемся с вами в ближайшее время.');
    } catch (error) {
        calculatorError.value = error.response?.data?.errors?.phone?.[0]
            ?? error.response?.data?.errors?.name?.[0]
            ?? error.response?.data?.errors?.model?.[0]
            ?? error.response?.data?.message
            ?? 'Failed to send request. Please try again.';
    } finally {
        calculatorSubmitting.value = false;
    }
};

/* ----------- Team slider ----------- */
const teamSliderRef = ref(null);

const scrollTeam = (direction) => {
    const el = teamSliderRef.value;
    if (!el) return;
    const card = el.querySelector('.team-card');
    if (!card) return;
    const gap = parseFloat(getComputedStyle(el).columnGap || getComputedStyle(el).gap || '0') || 0;
    const step = card.offsetWidth + gap;
    el.scrollBy({ left: direction * step, behavior: 'smooth' });
};

/* ----------- Helpers ----------- */
const popularCarsAdapted = computed(() => {
    return (props.popularCars || []).map((c) => ({
        id: c.id,
        slug: c.slug ?? c.id,
        photo: c.photo,
        thumbnail_url: c.photo ? `/storage/${c.photo}` : null,
        name: c.name,
        model: { name: c.name },
        brand: { name: c.brand?.name || '' },
        year: c.release_date ? String(c.release_date).substring(0, 4) : null,
        transmission: c.drive || c.transmission || null,
        engine: c.engine ? `${c.engine} см³${c.power ? ' • ' + c.power + ' л.с.' : ''}` : null,
        price: c.price ?? null,
        badge: 'Под заказ',
    }));
});
</script>

<template>
    <AppLayout title="Главная">
        <!-- ============== HERO ============== -->
        <section class="hero">
            <div class="hero__bg" aria-hidden="true">
                <div class="hero__glow hero__glow--1"></div>
                <div class="hero__glow hero__glow--2"></div>
                <div class="bg-grid absolute inset-0 opacity-50"></div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="grid lg:grid-cols-[1.05fr_0.95fr] gap-10 lg:gap-16 items-center min-h-[80vh] py-16 lg:py-24" style="display: grid;">
                    <div class="space-y-7 order-2 lg:order-1 max-w-2xl">
                        <span class="section-eyebrow">Авто под ключ · Доставка по России</span>
                        <h1 class="hero__title display-heading">
                            Премиальный подбор и&nbsp;доставка
                            <span class="hero__title-accent">авто под ключ</span>
                        </h1>
                        <p class="hero__lede">
                            Подбираем, проверяем и привозим автомобили в ваш регион. Прозрачная цена, проверенные источники, личный менеджер на каждом этапе.
                        </p>
                        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 pt-2">
                            <button
                                type="button"
                                class="premium-btn"
                                data-metrika-goal="calculator_open"
                                data-metrika-location="home_hero"
                                @click="showCalculatorModal = true"
                            >
                                Рассчитать стоимость
                                <i class="pi pi-arrow-right text-[12px]"></i>
                            </button>
                            <Link
                                :href="route('catalog.index')"
                                class="premium-btn-secondary"
                                data-metrika-goal="catalog_cta_click"
                                data-metrika-location="home_hero"
                            >
                                Посмотреть каталог
                            </Link>
                        </div>

                        <div class="hero__kpi">
                            <div v-for="kpi in kpiHighlights" :key="kpi.label" class="hero__kpi-tile">
                                <div class="hero__kpi-value">{{ kpi.value }}</div>
                                <div class="hero__kpi-label">{{ kpi.label }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="order-1 lg:order-2 relative">
                        <div
                            class="hero__media select-none touch-pan-y"
                            @pointerdown="onHeroPointerDown"
                            @pointermove="onHeroPointerMove"
                            @pointerup="finishHeroSwipe"
                            @pointercancel="cancelHeroSwipe"
                            @pointerleave="finishHeroSwipe"
                        >
                            <div class="hero__media-glow" aria-hidden="true"></div>
                            <div class="hero__media-frame">
                                <img
                                    v-for="(slide, index) in heroSlides"
                                    :key="slide.key"
                                    :src="slide.src"
                                    :alt="slide.alt"
                                    class="hero__media-img"
                                    :class="index === heroIndex ? 'is-active' : ''"
                                    :loading="index === 0 ? 'eager' : 'lazy'"
                                    draggable="false"
                                    @error="onSlideError(slide.key)"
                                />
                                <!-- Fallback marker when no slides could be loaded -->
                                <div v-if="!heroSlides.length" class="hero__media-fallback" aria-hidden="true">
                                    <i class="pi pi-car"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============== ABOUT ============== -->
        <Section eyebrow="О компании" title="БазаАвто — эксперт по подбору и привозу авто">
            <div class="grid lg:grid-cols-[0.95fr_1.05fr] gap-12 lg:gap-16 items-center">
                <div class="about-img-wrap">
                    <div class="about-img-glow" aria-hidden="true"></div>
                    <img src="/images/main.png" alt="Premium auto" class="about-img" loading="lazy" />
                </div>
                <div class="space-y-6">
                    <p class="text-xl lg:text-2xl font-display font-semibold text-ink-primary leading-snug tracking-tight">
                        Премиальный подбор, пригон и сопровождение автомобилей с пробегом по всей России.
                    </p>
                    <p class="text-base lg:text-lg text-ink-secondary leading-relaxed">
                        Помогли сотням клиентов стать счастливыми владельцами автомобилей мечты — экономя время и деньги.
                        Представительства и партнёры в ключевых регионах России: оперативно находим лучшие предложения и быстро доставляем туда, куда нужно вам.
                    </p>
                    <div class="hairline"></div>
                    <p class="text-base text-ink-secondary leading-relaxed">
                        Вы говорите, какую машину хотите — мы делаем всё остальное: дешевле, быстрее и без головной боли.
                    </p>
                </div>
            </div>
        </Section>

        <!-- ============== ADVANTAGES (KPI tiles) ============== -->
        <Section eyebrow="Почему выбирают нас" title="Работаем под ключ и держим вас в курсе" subtitle="Берём ответственность за подбор, проверку, логистику и документы. Согласовываем бюджет, сроки и показываем статус каждого этапа.">
            <div class="advantage-grid">
                <article
                    v-for="(item, i) in advantages"
                    :key="item.title"
                    class="advantage-tile"
                >
                    <div class="advantage-tile__icon">
                        <i class="pi" :class="item.icon"></i>
                    </div>
                    <div class="advantage-tile__num">{{ String(i + 1).padStart(2, '0') }}</div>
                    <div class="advantage-tile__eyebrow">{{ item.eyebrow }}</div>
                    <h3 class="advantage-tile__title">{{ item.title }}</h3>
                    <p class="advantage-tile__desc">{{ item.desc }}</p>
                </article>
            </div>
        </Section>

        <!-- ============== POPULAR MODELS ============== -->
        <Section eyebrow="Популярные модели" title="Авто, которые спрашивают чаще всего" subtitle="Подборка машин, которые мы недавно подбирали и привозили клиентам.">
            <template #head-extra>
                <Link
                    v-if="popularCarsAdapted.length"
                    :href="route('catalog.index')"
                    class="premium-btn-secondary !py-2.5 !px-5 !text-sm"
                >
                    Весь каталог
                    <i class="pi pi-arrow-right text-[10px]"></i>
                </Link>
            </template>

            <div v-if="popularCarsAdapted.length" class="popular-grid">
                <CarCard
                    v-for="car in popularCarsAdapted"
                    :key="car.id"
                    :car="car"
                />
            </div>
            <div v-else class="text-ink-muted text-sm">Популярных моделей пока нет.</div>
        </Section>

        <!-- ============== PROCESS ============== -->
        <Section eyebrow="Процесс" title="Как мы доставляем авто" subtitle="9 шагов от заявки до получения авто на учёт в ГИБДД.">
            <div class="process-grid">
                <article
                    v-for="(step, i) in processSteps"
                    :key="step.title"
                    class="process-step"
                >
                    <div class="process-step__num">{{ String(i + 1).padStart(2, '0') }}</div>
                    <h3 class="process-step__title">{{ step.title }}</h3>
                    <p class="process-step__desc">{{ step.desc }}</p>
                </article>
            </div>
        </Section>

        <!-- ============== TEAM ============== -->
        <Section eyebrow="Наша команда" title="Люди, которые ведут ваши сделки" subtitle="Эксперты по закупке, проверке и логистике — всегда на связи.">
            <template #head-extra>
                <div class="team-slider__nav" v-if="props.teamMembers.length > 1">
                    <button
                        type="button"
                        class="team-slider__btn"
                        aria-label="Назад"
                        @click="scrollTeam(-1)"
                    >
                        <i class="pi pi-arrow-left"></i>
                    </button>
                    <button
                        type="button"
                        class="team-slider__btn"
                        aria-label="Вперёд"
                        @click="scrollTeam(1)"
                    >
                        <i class="pi pi-arrow-right"></i>
                    </button>
                </div>
            </template>

            <div ref="teamSliderRef" class="team-slider">
                <article
                    v-for="member in props.teamMembers"
                    :key="member.id ?? member.name"
                    class="team-card"
                >
                    <div class="team-card__photo">
                        <img
                            v-if="member.photo"
                            :src="'/storage/' + member.photo"
                            :alt="member.name"
                            class="team-card__img"
                            :style="{ objectPosition: `${member.photo_focus_x ?? 50}% ${member.photo_focus_y ?? 50}%` }"
                            loading="lazy"
                        />
                        <div v-else class="team-card__placeholder">
                            <i class="pi pi-user"></i>
                        </div>
                    </div>
                    <div class="team-card__body">
                        <div class="team-card__name">{{ member.name }}</div>
                        <div class="team-card__role">{{ member.role }}</div>
                        <div v-if="member.city" class="team-card__city">{{ member.city }}</div>
                        <p v-if="member.description" class="team-card__desc">
                            {{ member.description }}
                        </p>
                        <div v-if="member.phone" class="team-card__phone">{{ member.phone }}</div>
                        <a
                            v-if="member.telegram_username"
                            :href="`https://t.me/${member.telegram_username.replace(/^@/, '')}`"
                            target="_blank"
                            rel="noopener"
                            class="team-card__tg"
                        >
                            <i class="pi pi-telegram"></i>
                            <span>{{ member.telegram_username }}</span>
                        </a>
                    </div>
                </article>
            </div>
        </Section>

        <!-- ============== FAQ ============== -->
        <Section eyebrow="FAQ" title="Отвечаем на популярные вопросы" subtitle="Доставка, оплата, гарантии и договор — собрали в одном месте.">
            <div class="faq-list">
                <details
                    v-for="item in faqItems"
                    :key="item.q"
                    class="faq-item group"
                >
                    <summary class="faq-summary">
                        <span class="faq-question">{{ item.q }}</span>
                        <span class="faq-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                        </span>
                    </summary>
                    <div class="faq-answer">{{ item.a }}</div>
                </details>
            </div>
        </Section>

        <!-- ============== TELEGRAM CTA ============== -->
        <section class="telegram-cta">
            <div class="telegram-cta__bg" aria-hidden="true"></div>
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative py-24 lg:py-32">
                <div class="grid lg:grid-cols-[1.1fr_0.9fr] gap-12 items-center">
                    <div class="space-y-6 max-w-2xl">
                        <span class="section-eyebrow">Telegram</span>
                        <h2 class="display-heading text-4xl sm:text-5xl lg:text-6xl text-ink-primary">
                            Будьте первыми. <span class="text-accent">Подпишитесь.</span>
                        </h2>
                        <p class="text-lg text-ink-secondary leading-relaxed">
                            Канал и чат в Telegram: спецпредложения, фотоотчёты, статусы доставок и быстрые ответы менеджеров.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <a
                                href="https://t.me/+v6p6S5BlUFg3MzBi"
                                target="_blank"
                                rel="noopener"
                                class="premium-btn"
                            >
                                <i class="pi pi-telegram"></i>
                                Подписаться на канал
                            </a>
                            <a
                                href="https://t.me/+uXvETOFXQ99iMjUy"
                                target="_blank"
                                rel="noopener"
                                class="premium-btn-secondary"
                            >
                                <i class="pi pi-comments"></i>
                                Наш чат
                            </a>
                        </div>
                    </div>

                    <div class="contact-card glass-card">
                        <div class="contact-card__head">
                            <span class="section-eyebrow">Контакт</span>
                            <h3 class="text-2xl font-display font-bold text-ink-primary mt-2">Остались вопросы?</h3>
                            <p class="text-ink-secondary mt-2">Свяжитесь с нами — ответим быстро и подробно.</p>
                        </div>
                        <div class="hairline"></div>
                        <div class="contact-card__rows">
                            <a href="tel:+74951780750" class="contact-card__row">
                                <span class="contact-card__icon"><i class="pi pi-phone"></i></span>
                                <span class="flex-1">
                                    <span class="contact-card__row-label">Телефон</span>
                                    <span class="contact-card__row-value">+7 495 178-07-50</span>
                                </span>
                                <i class="pi pi-arrow-right text-[12px] text-ink-muted"></i>
                            </a>
                            <a href="https://t.me/+uXvETOFXQ99iMjUy" target="_blank" rel="noopener" class="contact-card__row">
                                <span class="contact-card__icon"><i class="pi pi-telegram"></i></span>
                                <span class="flex-1">
                                    <span class="contact-card__row-label">Telegram</span>
                                    <span class="contact-card__row-value">Открыть чат</span>
                                </span>
                                <i class="pi pi-arrow-right text-[12px] text-ink-muted"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>

    <!-- ============== CALCULATOR MODAL ============== -->
    <Modal :show="showCalculatorModal" max-width="2xl" @close="showCalculatorModal = false">
        <div class="calc-modal">
            <div class="calc-modal__head">
                <span class="section-eyebrow">Калькулятор</span>
                <h2 class="display-heading text-2xl sm:text-3xl mt-2">Рассчитать стоимость авто</h2>
                <p class="text-ink-secondary mt-3 leading-relaxed">
                    Узнайте цену, условия договора и сроки поставки авто с доставкой в ваш город. Мы свяжемся с вами и всё расскажем.
                </p>
            </div>
            <form class="calc-form" @submit.prevent="submitCalculator">
                <div class="calc-field">
                    <label class="calc-label">Марка и модель автомобиля</label>
                    <input v-model="calculatorForm.model" type="text" placeholder="Toyota Corolla" class="public-form-input" required />
                </div>

                <div class="calc-field">
                    <label class="calc-label">Какие автомобили рассматриваете?</label>
                    <div class="calc-radio-group">
                        <label class="calc-radio">
                            <input type="radio" value="used50" v-model="calculatorForm.condition" class="public-form-radio" />
                            <span>С пробегом до 50 т км</span>
                        </label>
                        <label class="calc-radio">
                            <input type="radio" value="used100" v-model="calculatorForm.condition" class="public-form-radio" />
                            <span>До 100 т км</span>
                        </label>
                        <label class="calc-radio">
                            <input type="radio" value="any" v-model="calculatorForm.condition" class="public-form-radio" />
                            <span>Не важно</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="calc-field">
                        <label class="calc-label">Год выпуска</label>
                        <input v-model="calculatorForm.year" type="text" placeholder="2022" class="public-form-input" />
                    </div>
                    <div class="calc-field">
                        <label class="calc-label">Город доставки</label>
                        <input v-model="calculatorForm.city" type="text" placeholder="Москва" class="public-form-input" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="calc-field">
                        <label class="calc-label">Ваше имя</label>
                        <input v-model="calculatorForm.name" type="text" required placeholder="Иван Смирнов" class="public-form-input" />
                    </div>
                    <div class="calc-field">
                        <label class="calc-label">Телефон</label>
                        <input
                            :value="calculatorForm.phone"
                            type="tel"
                            required
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="+7 (___) ___-__-__"
                            data-ui="home-modal-phone-mask-v2"
                            class="public-form-input"
                            @input="handlePhoneInput"
                        />
                    </div>
                </div>

                <div class="calc-checks">
                    <label class="calc-check">
                        <input v-model="calculatorForm.agree" type="checkbox" class="public-form-checkbox" />
                        <span>Я согласен с <a href="/privacy-policy" class="text-accent underline">политикой конфиденциальности</a></span>
                    </label>
                    <label class="calc-check">
                        <input v-model="calculatorForm.mailing_agree" type="checkbox" class="public-form-checkbox" />
                        <span>Согласен на рассылку новостей и спецпредложений</span>
                    </label>
                </div>

                <p v-if="calculatorError" class="text-sm text-accent">{{ calculatorError }}</p>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="calculatorSubmitting"
                        class="premium-btn w-full !justify-center"
                    >
                        <span v-if="!calculatorSubmitting">Отправить заявку</span>
                        <span v-else class="inline-flex items-center gap-2">
                            <i class="pi pi-spin pi-spinner"></i>
                            Отправляем…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<style scoped>
/* ============ HERO ============ */
.hero {
    position: relative;
    overflow: hidden;
    background: rgb(var(--bg-base));
}

.hero__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

.hero__glow {
    position: absolute;
    border-radius: 9999px;
    filter: blur(120px);
    opacity: 0.5;
}

.hero__glow--1 {
    top: -10%;
    left: -10%;
    width: 50vw;
    height: 50vw;
    background: rgba(239, 68, 68, 0.18);
}

.hero__glow--2 {
    bottom: -20%;
    right: -10%;
    width: 60vw;
    height: 60vw;
    background: rgba(120, 113, 246, 0.10);
}

html.dark .hero__glow--1 { background: rgba(239, 68, 68, 0.25); }
html.dark .hero__glow--2 { background: rgba(120, 113, 246, 0.18); }

.hero__title {
    font-size: clamp(2.25rem, 6vw, 5rem);
    line-height: 0.98;
    color: rgb(var(--text-primary));
    text-wrap: balance;
}

.hero__title-accent {
    background: linear-gradient(135deg, #ef4444, #f97316);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero__lede {
    font-size: clamp(1rem, 1.4vw, 1.25rem);
    color: rgb(var(--text-secondary));
    line-height: 1.55;
    max-width: 36rem;
}

.hero__kpi {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0;
    margin-top: 3rem;
    border-top: 1px solid var(--border-subtle);
}

@media (min-width: 768px) {
    .hero__kpi { grid-template-columns: repeat(4, 1fr); }
}

.hero__kpi-tile {
    padding: 1.25rem 0.5rem 0;
    border-right: 1px solid var(--border-subtle);
}

.hero__kpi-tile:last-child { border-right: none; }

@media (max-width: 767px) {
    .hero__kpi-tile:nth-child(2) { border-right: none; }
    .hero__kpi-tile:nth-child(3),
    .hero__kpi-tile:nth-child(4) { padding-top: 1rem; }
}

.hero__kpi-value {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: clamp(1.5rem, 2.4vw, 2rem);
    line-height: 1;
    letter-spacing: -0.03em;
    color: rgb(var(--text-primary));
}

.hero__kpi-label {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgb(var(--text-muted));
}

.hero__media {
    position: relative;
    width: 100%;
    min-height: 320px;
}

.hero__media-glow {
    position: absolute;
    inset: -10%;
    background: radial-gradient(ellipse 65% 55% at 50% 55%, rgba(239, 68, 68, 0.32), transparent 72%);
    filter: blur(48px);
    z-index: 0;
    pointer-events: none;
}

html.dark .hero__media-glow {
    background: radial-gradient(ellipse 65% 55% at 50% 55%, rgba(239, 68, 68, 0.50), transparent 72%);
}

.hero__media-frame {
    position: relative;
    width: 100%;
    height: 320px;
    z-index: 1;
}

@media (min-width: 640px) { .hero__media-frame { height: 420px; } }
@media (min-width: 1024px) { .hero__media-frame { height: 480px; } }
@media (min-width: 1280px) { .hero__media-frame { height: 560px; } }
@media (min-width: 1536px) { .hero__media-frame { height: 620px; } }

.hero__media-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    opacity: 0;
    transition: opacity 900ms cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
}

.hero__media-img.is-active { opacity: 1; }

.hero__media-fallback {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 6rem;
    color: rgb(var(--accent));
    opacity: 0.4;
}

/* ============ ABOUT ============ */
.about-img-wrap {
    position: relative;
    width: 100%;
}

.about-img-glow {
    position: absolute;
    inset: -10%;
    background: radial-gradient(ellipse 60% 50% at 50% 50%, rgba(239, 68, 68, 0.20), transparent 60%);
    filter: blur(40px);
    z-index: 0;
}

.about-img {
    position: relative;
    width: 100%;
    height: auto;
    z-index: 1;
}

/* ============ POPULAR MODELS ============ */
.popular-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.5rem;
}

@media (min-width: 640px) {
    .popular-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .popular-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

/* ============ ADVANTAGES ============ */
.advantage-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1px;
    background: var(--border-subtle);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

@media (min-width: 640px) {
    .advantage-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.advantage-tile {
    position: relative;
    padding: 2rem 1.75rem 2.25rem;
    background: rgb(var(--bg-elevated));
    transition: background-color var(--dur-base) ease;
}

.advantage-tile:hover {
    background: rgb(var(--surface-1));
}

.advantage-tile__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-md);
    background: var(--accent-soft);
    color: rgb(var(--accent));
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.advantage-tile__num {
    position: absolute;
    top: 1.5rem;
    right: 1.75rem;
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 0.875rem;
    color: rgb(var(--text-muted));
    letter-spacing: 0.08em;
}

.advantage-tile__eyebrow {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgb(var(--text-muted));
    margin-bottom: 0.5rem;
}

.advantage-tile__title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.25rem;
    letter-spacing: -0.02em;
    color: rgb(var(--text-primary));
    margin-bottom: 0.5rem;
}

.advantage-tile__desc {
    font-size: 0.9375rem;
    color: rgb(var(--text-secondary));
    line-height: 1.55;
}

/* ============ PROCESS ============ */
.process-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;
}

@media (min-width: 640px) {
    .process-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 1024px) {
    .process-grid { grid-template-columns: repeat(3, 1fr); }
}

.process-step {
    position: relative;
    padding: 1.5rem 0;
    border-top: 1px solid var(--border-subtle);
    transition: border-color var(--dur-base) ease;
}

.process-step:hover {
    border-top-color: rgb(var(--accent));
}

.process-step__num {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: 2.5rem;
    line-height: 1;
    letter-spacing: -0.04em;
    background: linear-gradient(135deg, rgb(var(--text-primary)), rgb(var(--text-muted)));
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.75rem;
}

.process-step__title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.125rem;
    letter-spacing: -0.01em;
    color: rgb(var(--text-primary));
    margin-bottom: 0.5rem;
}

.process-step__desc {
    font-size: 0.9375rem;
    color: rgb(var(--text-secondary));
    line-height: 1.55;
}

/* ============ TEAM SLIDER ============ */
.team-slider {
    display: flex;
    gap: 1.25rem;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    padding-bottom: 0.5rem;
    /* Hide scrollbar across browsers — use the arrow buttons or drag */
    scrollbar-width: none;
    -ms-overflow-style: none;
    /* Slight horizontal padding so the first/last cards don't touch container edges */
    padding-left: 2px;
    padding-right: 2px;
}

.team-slider::-webkit-scrollbar { display: none; }

.team-slider .team-card {
    flex: 0 0 calc(100% - 2px);
    scroll-snap-align: start;
}

@media (min-width: 640px) {
    .team-slider { gap: 1.25rem; }
    .team-slider .team-card { flex: 0 0 calc((100% - 1.25rem) / 2); }
}

@media (min-width: 768px) {
    .team-slider .team-card { flex: 0 0 calc((100% - 1.25rem * 2) / 3); }
}

@media (min-width: 1024px) {
    .team-slider .team-card { flex: 0 0 calc((100% - 1.25rem * 3) / 4); }
}

@media (min-width: 1280px) {
    .team-slider .team-card { flex: 0 0 calc((100% - 1.25rem * 4) / 5); }
}

.team-slider__nav {
    display: inline-flex;
    gap: 0.5rem;
}

.team-slider__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 9999px;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    color: rgb(var(--text-primary));
    cursor: pointer;
    transition: border-color var(--dur-base) ease, background-color var(--dur-base) ease, color var(--dur-base) ease, transform var(--dur-base) ease;
}

.team-slider__btn:hover {
    border-color: rgb(var(--accent) / 0.5);
    color: rgb(var(--accent));
    transform: translateY(-1px);
}

.team-slider__btn i { font-size: 0.875rem; }

/* ============ TEAM CARD ============ */
.team-card {
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: border-color var(--dur-base) ease, transform var(--dur-base) ease;
}

.team-card:hover {
    border-color: var(--border-strong);
    transform: translateY(-3px);
}

.team-card__photo {
    aspect-ratio: 4 / 5;
    background: rgb(var(--surface-1));
    overflow: hidden;
    position: relative;
}

.team-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(0.85) contrast(1.05);
    transition: filter var(--dur-slow) ease, transform var(--dur-slow) cubic-bezier(0.16, 1, 0.3, 1);
}

.team-card:hover .team-card__img {
    filter: grayscale(0) contrast(1);
    transform: scale(1.04);
}

.team-card__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgb(var(--text-muted));
    font-size: 2rem;
}

.team-card__body {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.team-card__name {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.0625rem;
    letter-spacing: -0.01em;
    color: rgb(var(--text-primary));
}

.team-card__role { font-size: 0.875rem; color: rgb(var(--text-secondary)); }
.team-card__city { font-size: 0.8125rem; color: rgb(var(--text-muted)); }

.team-card__desc {
    margin-top: 0.5rem;
    font-size: 0.8125rem;
    line-height: 1.55;
    color: rgb(var(--text-secondary));
}

.team-card__phone { margin-top: 0.5rem; font-weight: 600; color: rgb(var(--text-primary)); font-size: 0.9375rem; }

.team-card__tg {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: rgb(var(--accent));
    transition: color var(--dur-base) ease;
}

.team-card__tg:hover { color: rgb(var(--accent-hover)); }

/* ============ FAQ ============ */
.faq-list {
    border-top: 1px solid var(--border-subtle);
}

.faq-item {
    border-bottom: 1px solid var(--border-subtle);
}

.faq-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1.5rem 0.5rem 1.5rem 0;
    cursor: pointer;
    list-style: none;
    transition: padding var(--dur-base) ease;
}

.faq-summary::-webkit-details-marker { display: none; }

.faq-question {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 600;
    font-size: 1.0625rem;
    letter-spacing: -0.01em;
    color: rgb(var(--text-primary));
}

.faq-icon {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 9999px;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    color: rgb(var(--text-secondary));
    transition: transform var(--dur-base) cubic-bezier(0.34, 1.56, 0.64, 1), background-color var(--dur-base) ease, color var(--dur-base) ease;
}

.faq-icon svg { width: 14px; height: 14px; }

.faq-item[open] .faq-icon {
    transform: rotate(45deg);
    background: rgb(var(--accent));
    color: #fff;
    border-color: rgb(var(--accent));
}

.faq-answer {
    padding: 0 0.5rem 1.75rem 0;
    font-size: 0.9375rem;
    color: rgb(var(--text-secondary));
    line-height: 1.65;
    max-width: 44rem;
}

/* ============ TELEGRAM CTA ============ */
.telegram-cta {
    position: relative;
    overflow: hidden;
}

.telegram-cta__bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 50% 40% at 80% 50%, rgba(239, 68, 68, 0.18), transparent 60%),
        radial-gradient(ellipse 60% 50% at 20% 80%, rgba(239, 68, 68, 0.10), transparent 50%);
    pointer-events: none;
}

.contact-card {
    padding: 2rem;
    background: rgb(var(--bg-elevated) / 0.7);
    border-radius: var(--radius-xl);
}

.contact-card__head { display: flex; flex-direction: column; gap: 0; margin-bottom: 1.5rem; }

.contact-card__rows {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.contact-card__row {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1rem;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    color: rgb(var(--text-primary));
    transition: border-color var(--dur-base) ease, transform var(--dur-base) ease;
}

.contact-card__row:hover {
    border-color: rgb(var(--accent) / 0.5);
    transform: translateX(2px);
}

.contact-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 9999px;
    background: var(--accent-soft);
    color: rgb(var(--accent));
}

.contact-card__row-label {
    display: block;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgb(var(--text-muted));
}

.contact-card__row-value {
    display: block;
    font-size: 0.9375rem;
    font-weight: 600;
    color: rgb(var(--text-primary));
    margin-top: 2px;
}

/* ============ CALCULATOR MODAL ============ */
.calc-modal {
    padding: 2rem 2rem 2.25rem;
    background: rgb(var(--bg-elevated));
    color: rgb(var(--text-primary));
    border-radius: var(--radius-lg);
}

.calc-modal__head { margin-bottom: 1.5rem; }

.calc-form { display: flex; flex-direction: column; gap: 1.25rem; }

.calc-field { display: flex; flex-direction: column; gap: 0.5rem; }

.calc-label {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgb(var(--text-secondary));
}

.calc-radio-group { display: flex; flex-direction: column; gap: 0.5rem; }

.calc-radio,
.calc-check {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    color: rgb(var(--text-secondary));
    font-size: 0.9375rem;
    cursor: pointer;
}

.calc-checks { display: flex; flex-direction: column; gap: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border-subtle); margin-top: 0.5rem; }
</style>
