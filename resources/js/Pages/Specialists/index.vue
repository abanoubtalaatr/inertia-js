<template>
    <AuthenticatedLayout>
        <!-- breadcrumb -->
        <div class="pagetitle row">
            <BreadcrumbComponent
                :pageTitle="$t('specialists')"
                createRoute="specialists.create"
                createPermission="create specialists"
                :homeLabel="$t('home')"
                :createButtonLabel="$t('create')"
            />
        </div>
        <!-- End breadcrumb -->

        <section class="section dashboard">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <div class="col-md-12 px-2">
                            <!-- FilterComponent for other fields -->
                            <FilterComponent
                                :filter-fields="nonCompanyFilterFields"
                                :initial-filters="filterForm"
                                @update:filters="handleFilterUpdate"
                            />
                            
                        </div>
                        <div class="col-md-1 px-2"></div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Display selected company name -->
                    
                    <div class="table-responsive">
                        <DataTable
                            :headers="headers"
                            :data="specialists.data"
                            :pagination-links="specialists.links"
                            noDataMessage="No specialists found."
                            @update:page="handlePageChange"
                        >
                            <!-- Slots for custom columns -->
                            <template #avatar="{ data }">
                                <img
                                    :src="data.avatar"
                                    alt="Avatar"
                                    class="avatar"
                                    width="45px"
                                />
                            </template>

                            <template #company="{ data }">
                                <span>{{ data.company?.name ?? 'No Company' }}</span>
                            </template>

                            <template #is_active="{ data }">
                                <template v-if="isSuperAdmin(data)">
                                    <el-tag type="success">{{ t("active") }}</el-tag>
                                </template>
                                <template v-else>
                                    <ActivateToggle
                                        v-if="!isSuperAdmin(data)"
                                        :id="data.id"
                                        :is-active="data.is_active == 1"
                                        :activate-url="`/specialists/${data.id}/activate`"
                                        @update:is-active="
                                            (newStatus) => updateStatus(data.id, newStatus)
                                        "
                                    />
                                </template>
                            </template>

                            <template #edit="{ data }">
                                <EditButton
                                    @click="
                                        router.get(
                                            route('specialists.edit', { specialist: data.id })
                                        )
                                    "
                                />
                            </template>

                            <template #delete="{ data }">
                                <DeleteAction
                                    v-if="!isSuperAdmin(data)"
                                    :id="data.id"
                                    :delete-url="
                                        route('specialists.destroy', { specialist: data.id })
                                    "
                                />
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { reactive, computed } from "vue";
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
    specialists: {
        type: Object,
        required: true,
    },
    companies: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Initialize filterForm with query params from props.filters
const filterForm = reactive({
    name: props.filters?.name || "",
    email: props.filters?.email || "",
    is_active: props.filters?.is_active || "",
});


// Filter fields excluding company (since we're handling it manually)
const nonCompanyFilterFields = [
    {
        key: "name",
        type: "text",
        placeholder: t("name"),
    },
    {
        key: "email",
        type: "text",
        placeholder: t("email"),
    },
    {
        key: "is_active",
        type: "select",
        placeholder: t("status"),
        options: [
            { label: t("active"), value: 1 },
            { label: t("not_active"), value: 0 },
        ],
    },
];

const headers = [
    { key: "name", label: t("name") },
    { key: "avatar", label: t("avatar"), slot: true },
    { key: "email", label: t("email") },
    { key: "created_at", label: t("created_at") },
    { key: "is_active", label: t("status"), slot: true },
    { key: "edit", label: t("edit"), slot: true },
    { key: "delete", label: t("delete"), slot: true },
];

const handleFilterUpdate = (updatedFilters) => {
    Object.assign(filterForm, updatedFilters);
    router.get(route("specialists.index"), filterForm, {
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

const hasPermission = (permission) => {
    return page.props.auth_permissions.includes(permission);
};

const updateStatus = (id, newStatus) => {
    router.post(`/specialists/${id}/activate`, { is_active: newStatus }, {
        onSuccess: () => {
            Swal.fire("Updated!", "specialist status has been updated.", "success");
        },
        onError: () => {
            Swal.fire("Error!", "There was an issue updating specialist status.", "error");
        },
    });
};

const Activate = (id) => {
    Swal.fire({
        title: t("are_your_sure"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#7066e0",
        confirmButtonText: t("yes"),
        cancelButtonText: t("cancel"),
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(`/specialists/${id}/activate`, {
                onSuccess: () => {
                    Swal.fire(
                        "Updated!",
                        "specialist status has been updated.",
                        "success"
                    );
                },
                onError: () => {
                    Swal.fire(
                        "Error!",
                        "There was an issue updating specialist status.",
                        "error"
                    );
                },
            });
        }
    });
};

const Delete = (id) => {
    Swal.fire({
        title: t("are_your_sure"),
        text: t("You_will_not_be_able_to_revert_this"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: t("yes"),
        cancelButtonText: t("cancel"),
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`specialists/${id}`, {
                onSuccess: () => {
                    Swal.fire({
                        title: t("data_deleted_successfully"),
                        icon: "success",
                    });
                },
                onError: () => {
                    Swal.fire(
                        "Error!",
                        "There was an issue deleting the item.",
                        "error"
                    );
                },
            });
        }
    });
};

const isSuperAdmin = (specialist) => {
    return specialist.email === "admin@admin.com" || specialist.role === "superadmin";
};

const exportspecialists = () => {
    const url = route("export.specialists");
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "specialists.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<style>
/* Optional styling for the custom dropdown */
.form-select {
    max-width: 300px; /* Adjust width as needed */
}
</style>