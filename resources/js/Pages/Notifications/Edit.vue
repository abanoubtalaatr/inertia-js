<template>
    <AuthenticatedLayout>
        <div class="pagetitle">
            <h1>{{ $t("notification.edit") }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link :href="route('dashboard')">{{
                            $t("dashboard")
                        }}</Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link :href="route('notifications.index')">{{
                            $t("notification.notifications")
                        }}</Link>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ $t("notification.edit") }}
                    </li>
                </ol>
            </nav>
        </div>

        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label required">{{
                            $t("notification.title")
                        }}</label>
                        <el-input
                            v-model="form.title"
                            :placeholder="$t('notification.title')"
                            :class="{ 'is-invalid': form.errors.title }"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.title }}
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="mb-3">
                        <label class="form-label required">{{
                            $t("notification.message")
                        }}</label>
                        <el-input
                            v-model="form.message"
                            type="textarea"
                            :rows="4"
                            :placeholder="$t('notification.message')"
                            :class="{ 'is-invalid': form.errors.message }"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.message }}
                        </div>
                    </div>

                    <!-- Recipient Type -->
                    <div class="mb-3">
                        <label class="form-label required">{{
                            $t("notification.recipient_type")
                        }}</label>
                        <el-select
                            v-model="form.recipient_type"
                            class="w-100"
                            :placeholder="$t('notification.recipient_type')"
                            :class="{
                                'is-invalid': form.errors.recipient_type,
                            }"
                            @change="handleRecipientTypeChange"
                        >
                            <el-option
                                value="all"
                                :label="$t('all_users')"
                            />
                            <el-option
                                value="companies"
                                :label="$t('companies')"
                            />
                            <el-option
                                value="specialists"
                                :label="$t('specialists')"
                            />
                            <el-option
                                value="clients"
                                :label="$t('clients')"
                            />
                        </el-select>
                        <div class="invalid-feedback">
                            {{ form.errors.recipient_type }}
                        </div>
                    </div>

                    <!-- Companies Recipient -->
                    <div v-if="form.recipient_type === 'companies'" class="mb-3">
                        <label class="form-label required">{{
                            $t("notification.select_recipient")
                        }}</label>
                        <el-select
                            v-model="form.recipient_ids"
                            multiple
                            filterable
                            class="w-100"
                            :placeholder="$t('notification.select_recipients')"
                            :class="{ 'is-invalid': form.errors.recipient_ids }"
                        >
                            <el-option value="all" :label="$t('all_companies')" />
                            <el-option
                                v-for="company in props.companies"
                                :key="company.id"
                                :label="company.name"
                                :value="company.id"
                            />
                        </el-select>
                        <div class="invalid-feedback">
                            {{ form.errors.recipient_ids }}
                        </div>
                    </div>

                    <!-- Specialists Recipient -->
                    <div v-if="form.recipient_type === 'specialists'" class="mb-3">
                        <label class="form-label required">{{
                            $t("notification.select_recipient")
                        }}</label>
                        <el-select
                            v-model="form.recipient_ids"
                            multiple
                            filterable
                            class="w-100"
                            :placeholder="$t('notification.select_recipients')"
                            :class="{ 'is-invalid': form.errors.recipient_ids }"
                        >
                            <el-option value="all" :label="$t('all_specialists')" />
                            <el-option
                                v-for="specialist in props.specialists"
                                :key="specialist.id"
                                :label="specialist.name"
                                :value="specialist.id"
                            />
                        </el-select>
                        <div class="invalid-feedback">
                            {{ form.errors.recipient_ids }}
                        </div>
                    </div>

                    <!-- Clients Recipient -->
                    <div v-if="form.recipient_type === 'clients'" class="mb-3">
                        <label class="form-label required">{{
                            $t("notification.select_recipient")
                        }}</label>
                        <el-select
                            v-model="form.recipient_ids"
                            multiple
                            filterable
                            class="w-100"
                            :placeholder="$t('notification.select_recipients')"
                            :class="{ 'is-invalid': form.errors.recipient_ids }"
                        >
                            <el-option value="all" :label="$t('all_clients')" />
                            <el-option
                                v-for="client in props.clients"
                                :key="client.id"
                                :label="client.name"
                                :value="client.id"
                            />
                        </el-select>
                        <div class="invalid-feedback">
                            {{ form.errors.recipient_ids }}
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="mb-3">
                        <el-checkbox v-model="isScheduled">{{
                            $t("notification.schedule")
                        }}</el-checkbox>
                    </div>

                    <!-- Schedule DateTime -->
                    <div v-if="isScheduled" class="mb-3">
                        <label class="form-label required">{{
                            $t("notification.schedule_time")
                        }}</label>
                        <el-date-picker
                            v-model="form.scheduled_at"
                            type="datetime"
                            :placeholder="$t('notification.schedule_time')"
                            format="YYYY-MM-DD HH:mm"
                            value-format="YYYY-MM-DD HH:mm"
                            :class="{ 'is-invalid': form.errors.scheduled_at }"
                            class="w-100"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.scheduled_at }}
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <el-button
                            type="info"
                            @click="saveAsDraft"
                            :loading="form.processing"
                        >
                            {{ $t("notification.save_draft") }}
                        </el-button>
                        <el-button
                            type="primary"
                            @click="sendNow"
                            :loading="form.processing"
                        >
                            {{ $t("notification.send_now") }}
                        </el-button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    notification: Object,
    companies: Array,
    specialists: Array,
    clients: Array,
});

const isScheduled = ref(!!props.notification.scheduled_at);

// Parse recipient_ids if they exist
const initialRecipientIds = props.notification.recipient_id 
    ? JSON.parse(props.notification.recipient_id??[])
    : [];

const form = useForm({
    title: props.notification.title,
    message: props.notification.message || props.notification.data?.message,
    recipient_type: props.notification.recipient_type,
    recipient_ids: initialRecipientIds,
    scheduled_at: props.notification.scheduled_at,
    status: props.notification.status,
});

const handleRecipientTypeChange = () => {
    // Reset recipient_ids when type changes
    form.recipient_ids = [];
};

const sendNow = () => {
    form.status = "sent";
    submitForm();
};

const saveAsDraft = () => {
    form.status = "draft";
    submitForm();
};

const submitForm = () => {
    form.put(route("notifications.update", props.notification.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success if needed
        },
    });
};
</script>
<style scoped>
.required:after {
    content: " *";
    color: var(--el-color-danger);
}

:deep(.el-input__wrapper),
:deep(.el-textarea__wrapper),
:deep(.el-select) {
    width: 100%;
}

:deep(.el-select .el-input) {
    width: 100%;
}

.is-invalid :deep(.el-input__wrapper),
.is-invalid :deep(.el-textarea__wrapper),
.is-invalid :deep(.el-select .el-input__wrapper) {
    box-shadow: 0 0 0 1px var(--el-color-danger) inset;
}

.el-select :deep(.el-select-dropdown__item) {
    padding: 8px 12px;
}

.user-option {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.user-name {
    font-weight: 600;
    font-size: 14px;
}

.user-email {
    font-size: 12px;
    color: #909399;
}
</style>

