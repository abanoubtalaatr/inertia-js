<template>
    <AuthenticatedLayout>
        <div class="pagetitle mb-4">
            <h1>{{ $t("create_advantage") }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link class="nav-link" :href="route('dashboard')">{{ $t("home") }}</Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link class="nav-link" :href="route('advantages.index')">{{ $t("advantages") }}</Link>
                    </li>
                    <li class="breadcrumb-item active">{{ $t("create") }}</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm p-4 rounded">
                        <div class="card-body">
                            <form @submit.prevent="store" class="row g-3">
                                <!-- Image -->
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">{{ $t("general_information") }}</h5>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <el-form-item :label="$t('image')">
                                                <el-upload
                                                    action=""
                                                    :auto-upload="false"
                                                    :on-change="handleFileChange"
                                                    list-type="picture-card"
                                                >
                                                    <img v-if="avatarPreview" :src="avatarPreview" class="img-thumbnail" width="80" />
                                                    <div v-else class="upload-placeholder">
                                                        <i class="el-icon-plus"></i>
                                                        <div>{{ $t("upload_image") }}</div>
                                                    </div>
                                                </el-upload>
                                            </el-form-item>
                                        </div>
                                    </div>
                                </div>

                                <!-- Translations -->
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">{{ $t("translations") }}</h5>
                                    <div class="row mb-3">
                                        <div v-for="lang in supportedLanguages" :key="lang" class="col-md-6 mb-3">
                                            <label class="form-label text-secondary">{{ $t(`title_${lang}`) }}</label>
                                            <el-input
                                                v-model="form.translations[lang].title"
                                                :placeholder="$t('title') + ` (${lang})`"
                                            ></el-input>
                                            <small class="text-danger">{{ form.errors[`translations.${lang}.title`] }}</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div v-for="lang in supportedLanguages" :key="lang" class="col-md-6 mb-3">
                                            <label class="form-label text-secondary">{{ $t(`description_${lang}`) }}</label>
                                            <div class="editor-wrapper">
                                                <quill-editor
                                                    v-model:content="form.translations[lang].description"
                                                    contentType="html"
                                                    :options="editorOptions"
                                                />
                                            </div>
                                            <small class="text-danger">{{ form.errors[`translations.${lang}.description`] }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-center mt-5">
                                    <button
                                        type="submit"
                                        class="btn btn-primary px-4 py-2"
                                        v-bind:disabled="show_loader"
                                    >
                                        {{ $t("create") }}
                                        <i class="bi bi-save" v-if="!show_loader"></i>
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="show_loader"></span>
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
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import settings from "@/src/config/settings";
import { ElMessage } from "element-plus";
import { Link } from "@inertiajs/vue3";

const supportedLanguages = settings.supportedLanguages; // ['ar', 'en', 'fr', 'tl', 'ur']
const avatarPreview = ref(null);
const show_loader = ref(false);

const editorOptions = {
    theme: "snow",
    placeholder: "Type here...",
    modules: {
        toolbar: [
            ["bold", "italic", "underline"],
            [{ list: "ordered" }, { list: "bullet" }],
            ["link"],
            ["clean"],
            ["code-block"],
            [{ header: [1, 2, 3, 4, 5, 6, false] }],
            [{ size: ["small", false, "large", "huge"] }],
            [{ color: [] }],
            [{ align: [] }],
            [{ direction: "rtl" }],
            [{ font: [] }],
            [{ script: "sub" }, { script: "super" }],
            [{ indent: "-1" }, { indent: "+1" }],
            [{ background: [] }],
            ["blockquote", "code-block"],
        ],
    },
};

const form = useForm({
    image: null,
    translations: supportedLanguages.reduce((acc, lang) => {
        acc[lang] = { title: '', description: '' };
        return acc;
    }, {}),
});

const handleFileChange = (file) => {
    if (file && file.raw) {
        avatarPreview.value = URL.createObjectURL(file.raw);
        form.image = file.raw;
    }
};

const store = () => {
    show_loader.value = true;

    form.post(route("advantages.store"), {
        onSuccess: () => {
            ElMessage({ type: "success", message: "Advantage created successfully!" });
            form.reset();
            avatarPreview.value = null;
        },
        onError: (errors) => {
            console.error('Create errors:', errors);
            Object.keys(errors).forEach(key => {
                console.error(`Field: ${key}, Error: ${errors[key]}`);
            });
            ElMessage({ type: "error", message: "There was an error creating the advantage. Check console for details." });
        },
        onFinish: () => {
            show_loader.value = false;
        },
    });
};
</script>

<style scoped>
.editor-class {
    min-height: 200px;
    max-height: 300px;
    overflow: auto;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px;
    background-color: #fff;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.img-thumbnail {
    max-width: 120px;
    max-height: 120px;
    border-radius: 6px;
    border: 1px solid #ddd;
}

button[disabled] {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>