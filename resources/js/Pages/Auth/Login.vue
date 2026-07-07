<script setup>
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
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
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthSplitLayout :title="t('Login to the system')">
        <Head :title="t('Login')" />

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

            <div class="mt-4">
                <InputLabel for="password" class="sr-only">{{ t('Password') }}</InputLabel>

                <TextInput
                    id="password"
                    type="password"
                    :placeholder="t('Password')"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-slate-600">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    {{ t('Remember me') }}
                </label>

                <Link
                    :href="route('password.request')"
                    class="font-medium text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded"
                >
                    {{ t('Forgot password?') }}
                </Link>
            </div>

            <PrimaryButton class="mt-6" :disabled="form.processing" :processing="form.processing">
                {{ form.processing ? t('Logging in…') : t('Login') }}
            </PrimaryButton>

            <p class="mt-6 text-center text-sm text-slate-500">
                {{ t("Don't have an account?") }}
                <Link :href="route('register')" class="font-medium text-blue-600 hover:text-blue-800">
                    {{ t('Register') }}
                </Link>
            </p>
        </form>
    </AuthSplitLayout>
</template>
