import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * English copy is used as the translation key itself, so an untranslated
 * locale (or a missing key) always falls back to readable English text
 * instead of a raw key like "auth.login_title".
 */
export function useTrans() {
    const page = usePage();

    const locale = computed(() => page.props.locale);
    const isRtl = computed(() => locale.value === 'he');

    function t(key) {
        return page.props.translations?.[key] ?? key;
    }

    return { t, locale, isRtl };
}
