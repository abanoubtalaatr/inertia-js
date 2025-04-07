<template>
    <AuthenticatedLayout>
        <div class="pagetitle">
            <h1>{{ $t("notification_settings") }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link class="nav-link" :href="route('dashboard')">
                            {{ $t("Home") }}
                        </Link>
                    </li>
                    <li class="breadcrumb-item active">{{ $t("settings") }}</li>
                    <li class="breadcrumb-item active">{{ $t("notifications") }}</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $t("manage_notification_settings") }}
                            </h5>
                            <form @submit.prevent="submit">
                                <div class="mb-4 notification-group">
                                    <h2 class="mb-4 group-title">
                                        {{ $t("email_notifications") }}
                                    </h2>
                                    <div class="row align-items-center notification-row">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center justify-content-between switch-container">
                                                <label
                                                    for="email_notifications"
                                                    class="form-label mb-0"
                                                >
                                                    {{ $t("email_notifications") }}
                                                </label>
                                                <el-switch
                                                    v-model="form.email_notifications"
                                                    active-text="Enabled"
                                                    inactive-text="Disabled"
                                                    active-value="1"
                                                    inactive-value="0"
                                                    active-color="#13ce66"
                                                    inactive-color="#ff4949"
                                                    class="custom-switch"
                                                />
                                            </div>
                                            <div
                                                v-if="errors.email_notifications"
                                                class="text-danger mt-2"
                                            >
                                                {{ errors.email_notifications }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button
                                        type="submit"
                                        class="btn btn-primary save-button"
                                        :disabled="form.processing"
                                    >
                                        {{ $t("save") }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ElSwitch } from 'element-plus';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({
            email_notifications: "0",
        }),
    },
});

const form = useForm({
    email_notifications: String(props.settings.email_notifications) || "0",
});

const errors = ref({});

const submit = () => {
    form.post(route("notification.settings.update"), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Settings updated successfully');
        },
        onError: (e) => {
            errors.value = e;
        },
    });
};
</script>

<style scoped>
.notification-group {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    background-color: #fff;
}

.group-title {
    color: #2c3e50;
    font-size: 1.25rem;
    font-weight: 600;
    border-bottom: 2px solid #3498db;
    padding-bottom: 8px;
}

.notification-row {
    padding: 15px 0;
}

.switch-container {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.switch-container:hover {
    background-color: #f1f3f5;
}

:deep(.el-switch) {
    height: 24px;
}

:deep(.el-switch__core) {
    width: 50px !important;
    height: 24px;
    border-radius: 12px;
}

:deep(.el-switch__core:after) {
    width: 20px;
    height: 20px;
    top: 1px;
}

:deep(.el-switch__label) {
    font-size: 14px;
    font-weight: 500;
    color: #495057;
    margin-left: 12px;
}

.form-label {
    font-size: 15px;
    font-weight: 500;
    color: #343a40;
}

.save-button {
    padding: 10px 25px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.save-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card {
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border-radius: 10px;
}

.card-body {
    padding: 25px;
}
</style>