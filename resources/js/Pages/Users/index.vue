<template>
    <AuthenticatedLayout>
        <div class="pagetitle row">
            <BreadcrumbComponent
                :pageTitle="$t('users')"
                createRoute="users.create"
                createPermission="create users"
                :homeLabel="$t('home')"
                :createButtonLabel="$t('create')"
            />
        </div>

        <section class="section dashboard">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <div class="col-md-12 px-2">
                            <FilterComponent
                                :filter-fields="nonCompanyFilterFields"
                                :initial-filters="filterForm"
                                @update:filters="handleFilterUpdate"
                            />
                            <div class="mt-2"  v-if="role !='company'">
                                <select
                                    v-model="filterForm.company"
                                    class="form-select"
                                    @change="handleFilterUpdate(filterForm)"
                                >
                                    <option value="">{{ t("all_companies") }}</option>
                                    <option
                                        v-for="company in props.companies"
                                        :key="company.id"
                                        :value="company.id"
                                    >
                                        {{ company.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="selectedCompanyName && role  !='company'" class="mb-3">
                        <strong>{{ t("selected_company") }}:</strong> {{ selectedCompanyName }}
                    </div>
                    <div class="table-responsive">
                        <DataTable
                            :headers="headers"
                            :data="users.data"
                            :pagination-links="users.links"
                            noDataMessage="No users found."
                            @update:page="handlePageChange"
                        >
                            <template #avatar="{ data }">
                                <img
                                    :src="data.avatar"
                                    alt="Avatar"
                                    class="avatar"
                                    width="45px"
                                />
                            </template>

                            <template #company="{ data }">
                                <span>{{ data.company?.name ?? t('No Company') }}</span>
                            </template>

                            <template #status="{ data }">
                                <el-tag
                                    :type="getStatusTagType(data.status)"
                                    v-if="!isSuperAdmin(data)"
                                >
                                    {{ t(data.status) }}
                                    <el-tooltip v-if="data.rejection_reason" :content="data.rejection_reason">
                                        <i class="el-icon-info ml-1"></i>
                                    </el-tooltip>
                                </el-tag>
                                <el-tag v-else type="success">{{ t("active") }}</el-tag>
                            </template>

                            <template #is_active="{ data }">
                                <template v-if="isSuperAdmin(data)">
                                    <el-tag type="success">{{ t("active") }}</el-tag>
                                </template>
                                <template v-else>
                                    <ActivateToggle
                                        :id="data.id"
                                        :is-active="data.is_active == 1"
                                        :activate-url="`/users/${data.id}/activate`"
                                        @update:is-active="
                                            (newStatus) => updateStatus(data.id, newStatus)
                                        "
                                    />
                                </template>
                            </template>

                            <template #actions="{ data }">
                                <div v-if="!isSuperAdmin(data) && data.status === 'pending'" class="d-flex gap-2">
                                    <el-button
                                        type="success"
                                        size="small"
                                        @click="acceptUser(data.id)"
                                    >
                                        {{ t('accept') }}
                                    </el-button>
                                    <el-button
                                        type="danger"
                                        size="small"
                                        @click="showRejectDialog(data.id)"
                                    >
                                        {{ t('reject') }}
                                    </el-button>
                                </div>
                            </template>

                            <template #edit="{ data }">
                                <EditButton
                                    @click="
                                        router.get(
                                            route('users.edit', { user: data.id })
                                        )
                                    "
                                />
                            </template>

                            <template #delete="{ data }">
                                <DeleteAction
                                    v-if="!isSuperAdmin(data)"
                                    :id="data.id"
                                    :delete-url="
                                        route('users.destroy', { user: data.id })
                                    "
                                />
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </section>

        <!-- Rejection Dialog -->
        <el-dialog
            :title="t('reject_user')"
            v-model="rejectDialogVisible"
            width="30%"
            :before-close="handleClose"
        >
            <el-form :model="rejectForm" ref="rejectFormRef" :rules="rules">
                <el-form-item :label="t('rejection_reason')" prop="rejection_reason">
                    <el-input
                        type="textarea"
                        v-model="rejectForm.rejection_reason"
                        :placeholder="t('enter_rejection_reason')"
                        :rows="4"
                        :disabled="false"
                        @input="onReasonInput"
                    />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="rejectDialogVisible = false">{{ t('cancel') }}</el-button>
                    <el-button type="danger" @click="rejectUser">{{ t('reject') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { reactive, ref, computed } from "vue";
import { useI18n } from "vue-i18n";
import FilterComponent from "@/Components/FilterComponent.vue";
import BreadcrumbComponent from "@/Components/BreadcrumbComponent.vue";
import ActivateToggle from "@/Components/ActivateToggle.vue";
import DeleteAction from "@/Components/DeleteAction.vue";
import EditButton from "@/Components/EditButton.vue";
import DataTable from "@/Components/DataTable.vue";

const { t } = useI18n();
const page = usePage();

const props = defineProps({
    users: { type: Object, required: true },
    companies: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    role : {type:String}
});

// Filter form
const filterForm = reactive({
    name: props.filters?.name || "",
    email: props.filters?.email || "",
    company: props.filters?.company || "",
    is_active: props.filters?.is_active || "",
    status: props.filters?.status || "",
});

const selectedCompanyName = computed(() => {
    if (!filterForm.company) return "";
    const company = props.companies.find((c) => c.id == filterForm.company);
    return company ? company.name : "Unknown Company";
});

const nonCompanyFilterFields = [
    { key: "name", type: "text", placeholder: t("name") },
    { key: "email", type: "text", placeholder: t("email") },
    {
        key: "is_active",
        type: "select",
        placeholder: t("status"),
        options: [
            { label: t("active"), value: 1 },
            { label: t("not_active"), value: 0 },
        ],
    },
    {
        key: "status",
        type: "select",
        placeholder: t("approval_status"),
        options: [
            { label: t("pending"), value: "pending" },
            { label: t("accepted"), value: "accepted" },
            { label: t("rejected"), value: "rejected" },
        ],
    },
];

const headers = [
    { key: "company", label: t("Company") },
    { key: "name", label: t("name") },
    { key: "avatar", label: t("avatar"), slot: true },
    { key: "email", label: t("email") },
    { key: "created_at", label: t("created_at") },
    { key: "status", label: t("approval_status"), slot: true },
    { key: "is_active", label: t("active_status"), slot: true },
    { key: "actions", label: t("actions"), slot: true },
    { key: "edit", label: t("edit"), slot: true },
    { key: "delete", label: t("delete"), slot: true },
];

// Rejection dialog state
const rejectDialogVisible = ref(false);
const rejectForm = reactive({
    userId: null,
    rejection_reason: "",
});
const rejectFormRef = ref(null); // Form reference for validation
const rules = {
    rejection_reason: [
        { required: true, message: t("rejection_reason_required"), trigger: "blur" },
        { max: 255, message: t("rejection_reason_max"), trigger: "blur" },
    ],
};

// Event handlers
const handleFilterUpdate = (updatedFilters) => {
    Object.assign(filterForm, updatedFilters);
    router.get(route("users.index"), filterForm, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePageChange = (page) => {
    router.get(page, filterForm, {
        preserveState: true,
        preserveScroll: true,
    });
};

const updateStatus = (id, newStatus) => {
    router.post(`/users/${id}/activate`, { is_active: newStatus }, {
        onSuccess: () => {
            Swal.fire("Updated!", "User status has been updated.", "success");
        },
        onError: () => {
            Swal.fire("Error!", "There was an issue updating user status.", "error");
        },
    });
};

const acceptUser = (id) => {
    Swal.fire({
        title: t("are_you_sure"),
        text: t("accept_user_confirmation"),
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#7066e0",
        confirmButtonText: t("yes"),
        cancelButtonText: t("cancel"),
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(`/users/${id}/accept`, {}, {
                onSuccess: () => {
                    Swal.fire(t("accepted"), t("user_accepted"), "success");
                },
                onError: () => {
                    Swal.fire("Error!", t("error_accepting_user"), "error");
                },
            });
        }
    });
};

const showRejectDialog = (id) => {
    console.log('Opening reject dialog for user:', id); // Debug
    rejectForm.userId = id;
    rejectForm.rejection_reason = ""; // Reset the textarea
    rejectDialogVisible.value = true;
};

const onReasonInput = (value) => {
    console.log('Textarea input:', value); // Debug to confirm typing works
};

const rejectUser = () => {
    rejectFormRef.value.validate((valid) => {
        if (valid) {
            console.log('Submitting rejection with reason:', rejectForm.rejection_reason); // Debug
            router.post(`/users/${rejectForm.userId}/reject`, {
                rejection_reason: rejectForm.rejection_reason,
            }, {
                onSuccess: () => {
                    rejectDialogVisible.value = false;
                    Swal.fire(t("rejected"), t("user_rejected"), "success");
                },
                onError: (errors) => {
                    console.error('Rejection errors:', errors); // Debug
                    Swal.fire("Error!", errors.rejection_reason || t("error_rejecting_user"), "error");
                },
            });
        } else {
            console.log('Form validation failed'); // Debug
        }
    });
};

const handleClose = (done) => {
    ElMessageBox.confirm(t('confirm_close'))
        .then(() => done())
        .catch(() => {});
};

// Utility functions
const isSuperAdmin = (user) => {
    return user.email === "admin@admin.com" || user.role === "superadmin";
};

const getStatusTagType = (status) => {
    switch (status) {
        case 'accepted': return 'success';
        case 'rejected': return 'danger';
        case 'pending': return 'warning';
        default: return 'info';
    }
};
</script>

<style>
.form-select {
    max-width: 300px;
}
</style>