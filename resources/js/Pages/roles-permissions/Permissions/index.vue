<template>
    <AuthenticatedLayout>
        <!-- breadcrumb-->
        <div class="pagetitle">
            <h1>{{ $t("permissions") }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link class="nav-link" :href="route('dashboard')">
                            {{ $t("Home") }}
                        </Link>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ $t("permissions") }}
                    </li>
                </ol>
            </nav>
        </div>
        <!-- End breadcrumb-->

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <div class="card-header">
                        <div class="d-flex">
                            <Link
                                v-if="hasPermission('create permissions')"
                                class="btn btn-primary ms-auto"
                                :href="route('permissions.create')"
                            >
                                {{ $t("create") }} &nbsp;
                                <i class="bi bi-plus-circle"> </i
                            ></Link>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ $t("name") }}</th>
                                    <th
                                        scope="col"
                                        v-if="
                                            hasPermission('update permissions')
                                        "
                                    >
                                        {{ $t("edit") }}
                                    </th>
                                    <th
                                        scope="col"
                                        v-if="
                                            hasPermission('delete permissions')
                                        "
                                    >
                                        {{ $t("delete") }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(
                                        permission, index
                                    ) in permissions.data"
                                    :key="permission.id"
                                >
                                    <th scope="row">{{ index + 1 }}</th>
                                    <td>{{ permission.name }}</td>
                                    <td
                                        v-if="
                                            hasPermission('update permissions')
                                        "
                                    >
                                        <a
                                            class="btn btn-primary"
                                            :href="
                                                route('permissions.edit', {
                                                    permission: permission.id,
                                                })
                                            "
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                    <td
                                        v-if="
                                            hasPermission('delete permissions')
                                        "
                                    >
                                        <button
                                            type="button"
                                            class="btn btn-danger"
                                            @click="Delete(permission.id)"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="permissions.links" />
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { Link, usePage } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { router } from "@inertiajs/vue3";

import { useI18n } from "vue-i18n";

const { t } = useI18n();
const page = usePage();

const props = defineProps({ permissions: Object });

const hasPermission = (permission) => {
    return page.props.auth_permissions.includes(permission);
};

const Delete = (id) => {
    Swal.fire({
        title: t("are_your_sure"),
        text: t("You_will_not_be_able_to_revert_this"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: t("Yes, delete it!"),
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete("permissions/" + id, {
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
</script>
