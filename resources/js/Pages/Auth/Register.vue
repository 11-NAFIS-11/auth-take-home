<script setup>
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTrans } from '@/composables/useTrans';
import { Head, Link, useForm } from '@inertiajs/vue3';

const { t } = useTrans();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthSplitLayout :title="t('Create your account')">
        <Head :title="t('Register')" />

        <form @submit.prevent="submit" novalidate>
            <div>
                <InputLabel for="name" class="sr-only">{{ t('Name') }}</InputLabel>

                <TextInput
                    id="name"
                    type="text"
                    :placeholder="t('Name')"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" class="sr-only">{{ t('Email') }}</InputLabel>

                <TextInput
                    id="email"
                    type="email"
                    :placeholder="t('Email')"
                    v-model="form.email"
                    required
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
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" class="sr-only">{{ t('Confirm password') }}</InputLabel>

                <TextInput
                    id="password_confirmation"
                    type="password"
                    :placeholder="t('Confirm password')"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton class="mt-6" :disabled="form.processing" :processing="form.processing">
                {{ form.processing ? t('Registering…') : t('Register') }}
            </PrimaryButton>

            <p class="mt-6 text-center text-sm text-slate-500">
                {{ t('Already have an account?') }}
                <Link :href="route('login')" class="font-medium text-blue-600 hover:text-blue-800">
                    {{ t('Login') }}
                </Link>
            </p>
        </form>
    </AuthSplitLayout>
</template>
