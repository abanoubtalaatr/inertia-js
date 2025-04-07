<template>
    <AuthenticatedLayout>
        <div class="pagetitle mb-4">
            <h1>{{ $t("advantages") }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link class="nav-link" :href="route('dashboard')">{{ $t("home") }}</Link>
                    </li>
                    <li class="breadcrumb-item active">{{ $t("advantages") }}</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm p-4 rounded">
                        <div class="card-body">
                            <Link :href="route('advantages.create')" class="btn btn-primary mb-3">
                                {{ $t("create_advantage") }}
                            </Link>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ $t("image") }}</th>
                                        <th>{{ $t("title_en") }}</th>
                                        <th>{{ $t("description_en") }}</th>
                                        <th>{{ $t("actions") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="advantage in advantages" :key="advantage.id">
                                        <td>
                                            <img
                                                v-if="advantage.image_url"
                                                :src="advantage.image_url"
                                                class="img-thumbnail"
                                                width="50"
                                                @error="onImageError"
                                            />
                                            <span v-else>{{ $t("no_image") }}</span>
                                        </td>
                                        <td>{{ advantage.translations.find(t => t.locale === 'en')?.title }}</td>
                                        <td v-html="advantage.translations.find(t => t.locale === 'en')?.description"></td>
                                        <td>
                                            <Link :href="route('advantages.edit', advantage.id)" class="btn btn-sm btn-primary me-2">
                                                {{ $t("edit") }}
                                            </Link>
                                            <button @click="confirmDelete(advantage.id)" class="btn btn-sm btn-danger">
                                                {{ $t("delete") }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!advantages || advantages.length === 0">
                                        <td colspan="4" class="text-center">{{ $t("no_advantages_found") }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { ElMessage, ElMessageBox } from "element-plus";
import { useI18n } from 'vue-i18n';

// Define the props explicitly
const props = defineProps({
    advantages: {
        type: Array,
        default: () => [],
    },
});

// Use i18n composable to get $t
const { t: $t } = useI18n();

// Form for delete action
const form = useForm({});

const confirmDelete = (id) => {
    ElMessageBox.confirm(
        $t("are_you_sure_delete"),
        $t("confirm_deletion"),
        {
            confirmButtonText: $t("delete"),
            cancelButtonText: $t("cancel"),
            type: "warning",
        }
    )
    .then(() => {
        form.delete(route('advantages.destroy', id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                ElMessage({ type: "success", message: $t("advantage_deleted_successfully") });
            },
            onError: (errors) => {
                console.error('Delete errors:', errors);
                ElMessage({ type: "error", message: $t("error_deleting_advantage") });
            },
        });
    })
    .catch(() => {
        // User cancelled
    });
};

const onImageError = (event) => {
    console.error('Image failed to load:', event.target.src);
    event.target.style.display = 'none'; // Hide broken image
};
</script>

<style scoped>
.img-thumbnail {
    max-width: 50px;
    max-height: 50px;
    border-radius: 6px;
}

.btn-sm {
    margin-right: 5px;
}
</style>