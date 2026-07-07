<script setup>
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useTrans } from '@/composables/useTrans';
import { Head, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    resendAvailableInSeconds: {
        type: Number,
        default: 0,
    },
    status: {
        type: String,
    },
});

const { t } = useTrans();

const form = useForm({
    code: '',
});

const resendForm = useForm({});

const secondsLeft = ref(props.resendAvailableInSeconds);
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        if (secondsLeft.value > 0) {
            secondsLeft.value -= 1;
        }
    }, 1000);
});

onBeforeUnmount(() => clearInterval(timer));

const submit = () => {
    form.post(route('two-factor.verify'), {
        onFinish: () => form.reset('code'),
    });
};

const resend = () => {
    resendForm.post(route('two-factor.resend'), {
        preserveScroll: true,
        onSuccess: () => {
            secondsLeft.value = 60;
        },
    });
};
</script>

<template>
    <AuthSplitLayout :title="t('Two-factor verification')">
        <Head :title="t('Two-factor verification')" />

        <p class="mb-4 text-center text-sm text-slate-500">
            {{ t('We sent a 6-digit code to') }} <span class="font-medium text-slate-700">{{ email }}</span>
        </p>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" novalidate>
            <div>
                <InputLabel for="code" class="sr-only">{{ t('6-digit code') }}</InputLabel>

                <input
                    id="code"
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    pattern="[0-9]*"
                    autofocus
                    required
                    :placeholder="t('6-digit code')"
                    class="block w-full rounded-lg border-0 bg-white px-4 py-3 text-center text-2xl tracking-[0.5em] text-slate-800 shadow-inner ring-1 ring-inset ring-slate-200 placeholder:text-base placeholder:tracking-normal placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <InputError :message="form.errors.code" />
            </div>

            <PrimaryButton class="mt-6" :disabled="form.processing" :processing="form.processing">
                {{ form.processing ? t('Verifying…') : t('Verify') }}
            </PrimaryButton>
        </form>

        <div class="mt-6 text-center text-sm text-slate-500">
            {{ t("Didn't get a code?") }}
            <button
                type="button"
                class="font-medium text-blue-600 hover:text-blue-800 disabled:cursor-not-allowed disabled:text-slate-400"
                :disabled="secondsLeft > 0 || resendForm.processing"
                @click="resend"
            >
                {{ secondsLeft > 0 ? `${t('Resend in')} ${secondsLeft}s` : t('Resend code') }}
            </button>

            <InputError :message="resendForm.errors.code" />
        </div>
    </AuthSplitLayout>
</template>
