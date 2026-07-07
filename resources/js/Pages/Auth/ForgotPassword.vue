<script setup>
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTrans } from '@/composables/useTrans';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const { t } = useTrans();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthSplitLayout :title="t('Reset password')">
        <Head :title="t('Reset password')" />

        <p class="mb-4 text-sm text-slate-500">
            {{ t("No problem. Enter your email and we'll send you a link to reset it.") }}
        </p>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" novalidate>
            <div>
                <InputLabel for="email" class="sr-only">{{ t('Email') }}</InputLabel>

                <TextInput
                    id="email"
                    type="email"
                    :placeholder="t('Email')"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError :message="form.errors.email" />
            </div>

            <PrimaryButton class="mt-6" :disabled="form.processing" :processing="form.processing">
                {{ form.processing ? t('Sending…') : t('Send password reset link') }}
            </PrimaryButton>

            <p class="mt-6 text-center text-sm text-slate-500">
                <Link :href="route('login')" class="font-medium text-blue-600 hover:text-blue-800">
                    {{ t('Back to login') }}
                </Link>
            </p>
        </form>
    </AuthSplitLayout>
</template>
