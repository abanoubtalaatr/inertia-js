<template>
    <AuthenticatedLayout>
        <!-- breadcrumb-->
        <div class="pagetitle row">
            <BreadcrumbComponent
                :pageTitle="$t('admins')"
                createRoute="admins.create"
                createPermission="create admins"
                :homeLabel="$t('home')"
                :createButtonLabel="$t('create')"
            />
        </div>

        <!-- End breadcrumb-->

        <section class="section dashboard">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <div class="col-md-12 px-2">
                            <FilterComponent
                                :filter-fields="filterFields"
                                :initial-filters="filterForm"
                                @update:filters="handleFilterUpdate"
                            />
                        </div>
                        <div class="col-md-1 px-2">
                            <!-- <Link
                                v-if="hasPermission('read admins')"
                                class="btn btn-outline-secondary w-100"
                                :href="route('export.admins')"
                                ><i class="bi bi-filetype-xls"></i
                            ></Link> -->
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- <table class="table text-center">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ $t("name") }}</th>
                                    <th scope="col">{{ $t("avatar") }}</th>
                                    <th scope="col">{{ $t("role") }}</th>
                                    <th scope="col">{{ $t("email") }}</th>
                                    <th scope="col">{{ $t("created_at") }}</th>
                                    <th scope="col">{{ $t("status") }}</th>
                                    <th
                                        scope="col"
                                        v-if="hasPermission('update admins')"
                                    >
                                        {{ $t("edit") }}
                                    </th>
                                    <th
                                        scope="col"
                                        v-if="hasPermission('delete admins')"
                                    >
                                        {{ $t("delete") }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <tr
                                    v-for="(admin, index) in admins.data"
                                    :key="admin.id"
                                >
                                    <th scope="row">{{ index + 1 }}</th>
                                    <td>{{ admin.name }}</td>
                                    <td>
                                        <img
                                            :src="admin.avatar"
                                            alt="Avatar"
                                            class="avatar"
                                            width="45px"
                                        />
                                    </td>
                                    <td>
                                        <span
                                            v-for="role in admin.roles"
                                            :key="role.id"
                                            class="badge bg-secondary"
                                        >
                                            {{ role.name }}
                                        </span>
                                    </td>
                                    <td>{{ admin.email }}</td>
                                    <td>{{ admin.created_at }}</td>
                                    <td>
                                        <div>
                                            <label
                                                class="inline-flex items-center me-5 cursor-pointer"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="sr-only peer"
                                                    :checked="
                                                        admin.is_active == 1
                                                    "
                                                    @change="Activate(admin.id)"
                                                />
                                                <div
                                                    class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"
                                                ></div>
                                            </label>
                                        </div>
                                    </td>
                                    <td v-if="hasPermission('update admins')">
                                        <a
                                            class="btn btn-outline-secondary"
                                            :href="
                                                route('admins.edit', {
                                                    admin: admin.id,
                                                })
                                            "
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                    <td v-if="hasPermission('delete admins')">
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger del-btn"
                                            @click="Delete(admin.id)"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                    <tr v-if="admins.data.length == 0">
                                    <td colspan="8" class="text-center">
                                        {{ $t("no_data_found") }}
                                    </td>
                                </tr>
                            </tbody>
                        </table> -->

                        <!-- Table -->

                        <DataTable
                            :headers="headers"
                            :data="admins.data"
                            :pagination-links="admins.links"
                            noDataMessage="No admins found."
                            @update:page="handlePageChange"
                        >
                            <!-- Slot للأعمدة الخاصة -->
                            <template #avatar="{ data }">
                                <img
                                    :src="data.avatar"
                                    alt="Avatar"
                                    class="avatar"
                                    width="45px"
                                />
                            </template>

                            <template #role="{ data }">
                                <span
                                    v-for="role in data.roles"
                                    :key="role.id"
                                    class="badge bg-secondary"
                                >
                                    {{ role.name }}
                                </span>
                            </template>

                            <template #is_active="{ data }">
                                <template
                                    v-if="isSuperAdmin(data)"
                                >
                                    <el-tag type="success">{{
                                        t("active")
                                    }}</el-tag>
                                </template>
                                <template v-else>
                                    <ActivateToggle
                                        v-if="!isSuperAdmin(data)"
                                        :id="data.id"
                                        :is-active="data.is_active == 1"
                                        :activate-url="`/admins/${data.id}/activate`"
                                        @update:is-active="
                                            (newStatus) =>
                                                updateStatus(data.id, newStatus)
                                        "
                                    />
                                </template>
                            </template>

                            <template #edit="{ data }">

                                  <EditButton
                                   
                                    @click="
                                        router.get(
                                            route('admins.edit', { admin: data.id })
                                        )
                                    "
                                />
                                <!-- <a
                                    class="btn btn-outline-secondary"
                                    :href="
                                        route('admins.edit', { admin: data.id })
                                    "
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </a> -->
                            </template>

                            <template #delete="{ data }">
                                <DeleteAction
                                  v-if="!isSuperAdmin(data)"
                                    :id="data.id"
                                    :delete-url="
                                        route('admins.destroy', {
                                            admin: data.id,
                                        })
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
import Pagination from "@/Components/Pagination.vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { reactive } from "vue";
import { useI18n } from "vue-i18n";
import FilterComponent from "@/Components/FilterComponent.vue";
import BreadcrumbComponent from "@/Components/BreadcrumbComponent.vue";
import ActivateToggle from "@/Components/ActivateToggle.vue";
import DeleteAction from "@/Components/DeleteAction.vue";
import EditButton from "@/Components/EditButton.vue";
import DataTable from "@/Components/DataTable.vue";

const { t } = useI18n();
const props = defineProps({ admins: Object });
const page = usePage();

const filterForm = reactive({
    name: "",
    email: "",
    is_active: "",
});
const headers = [
    { key: "name", label: t("name") },
    { key: "avatar", label: t("avatar"), slot: true },
    { key: "role", label: t("role"), slot: true },
    { key: "email", label: t("email") },
    { key: "created_at", label: t("created_at") },
    { key: "is_active", label: t("status"), slot: true },
    { key: "edit", label: t("edit"), slot: true },
    { key: "delete", label: t("delete"), slot: true },
];
const filterFields = [
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

const handleFilterUpdate = (updatedFilters) => {
    Object.assign(filterForm, updatedFilters);
    router.get(route("admins.index"), filterForm, {
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
            router.post(`/admins/${id}/activate`, {
                onSuccess: () => {
                    Swal.fire(
                        "Updated !",
                        "admin stuaus item has been updated.",
                        "success"
                    );
                },
                onError: () => {
                    Swal.fire(
                        "Error!",
                        "There was an issue updating admin status.",
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
            router.delete("admins/" + id, {
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
const isSuperAdmin = (admin) => {
    return admin.email === 'admin@admin.com' || admin.role === 'superadmin';
};
const exportadmins = () => {
    const url = route("export.admins");
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "admins.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>
<style></style>
