<script setup>
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTrans } from '@/composables/useTrans';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const { t } = useTrans();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthSplitLayout :title="t('Set a new password')">
        <Head :title="t('Reset password')" />

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
                <InputLabel for="password" class="sr-only">{{ t('New password') }}</InputLabel>

                <TextInput
                    id="password"
                    type="password"
                    :placeholder="t('New password')"
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
                {{ form.processing ? t('Saving…') : t('Reset password') }}
            </PrimaryButton>
        </form>
    </AuthSplitLayout>
</template>
