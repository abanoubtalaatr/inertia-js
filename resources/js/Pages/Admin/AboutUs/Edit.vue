<template>
    <AuthenticatedLayout title="من نحن">
        <div class="pagetitle mb-4" dir="rtl">
            <h1>من نحن</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link class="nav-link" :href="route('dashboard')">الرئيسية</Link>
                    </li>
                    <li class="breadcrumb-item active">من نحن</li>
                    <li class="breadcrumb-item active">تعديل</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm p-4 rounded">
                        <div class="card-body">
                            <form @submit.prevent="submitForm" class="row g-3" enctype="multipart/form-data">
                                <!-- Images Section -->
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">الصور</h5>
                                    <div class="row">
                                        <!-- Image 1 -->
                                        <div class="col-md-6 mb-3">
                                            <el-form-item label="الصورة الأولى *">
                                                <div class="image-upload-wrapper">
                                                    <el-upload action="" :auto-upload="false"
                                                        :on-change="(file) => handleFileChange('image1', file)"
                                                        list-type="picture-card" :show-file-list="false">
                                                        <img v-if="form.image1Preview || form.image1"
                                                            :src="form.image1Preview || form.image1"
                                                            class="img-thumbnail" width="100%" />
                                                        <div v-else class="upload-placeholder">
                                                            <i class="el-icon-plus"></i>
                                                            <div>رفع صورة</div>
                                                        </div>
                                                    </el-upload>
                                                    <el-icon v-if="form.image1Preview || form.image1"
                                                        class="delete-icon" @click="clearImage('image1')">
                                                        <Delete />
                                                    </el-icon>
                                                </div>
                                            </el-form-item>
                                        </div>

                                        <!-- Image 2 -->
                                        <div class="col-md-6 mb-3">
                                            <el-form-item label="الصورة الثانية *">
                                                <div class="image-upload-wrapper">
                                                    <el-upload action="" :auto-upload="false"
                                                        :on-change="(file) => handleFileChange('image2', file)"
                                                        list-type="picture-card" :show-file-list="false">
                                                        <img v-if="form.image2Preview || form.image2"
                                                            :src="form.image2Preview || form.image2"
                                                            class="img-thumbnail" width="100%" />
                                                        <div v-else class="upload-placeholder">
                                                            <i class="el-icon-plus"></i>
                                                            <div>رفع صورة</div>
                                                        </div>
                                                    </el-upload>
                                                    <el-icon v-if="form.image2Preview || form.image2"
                                                        class="delete-icon" @click="clearImage('image2')">
                                                        <Delete />
                                                    </el-icon>
                                                </div>
                                            </el-form-item>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content Items -->
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">عناصر المحتوى *</h5>
                                    <div class="accordion" id="itemsAccordion">
                                        <div v-for="(item, index) in form.items" :key="index"
                                            class="accordion-item mb-3">
                                            <h2 class="accordion-header" :id="'heading' + index">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    :data-bs-target="'#collapse' + index" aria-expanded="true"
                                                    :aria-controls="'collapse' + index">
                                                    عنصر {{ index + 1 }} *
                                                </button>
                                            </h2>
                                            <div :id="'collapse' + index" class="accordion-collapse collapse"
                                                :class="{ show: index === 0 }" :aria-labelledby="'heading' + index"
                                                data-bs-parent="#itemsAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <!-- English Fields -->
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">العنوان
                                                                (إنجليزي)</label>
                                                            <el-input v-model="item.title_en"
                                                                placeholder="العنوان (إنجليزي)"></el-input>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">الوصف
                                                                (إنجليزي)</label>
                                                            <div class="editor-wrapper">
                                                                <Editor v-model="item.description_en"
                                                                    :init="editorConfig" :key="`editor-${index}-en`" />
                                                            </div>
                                                        </div>

                                                        <!-- Arabic Fields -->
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">العنوان
                                                                (عربي)</label>
                                                            <el-input v-model="item.title_ar"
                                                                placeholder="العنوان (عربي)"></el-input>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">الوصف
                                                                (عربي)</label>
                                                            <div class="editor-wrapper">
                                                                <Editor v-model="item.description_ar"
                                                                    :init="editorConfig" :key="`editor-${index}-ar`" />
                                                            </div>
                                                        </div>

                                                        <!-- French Fields -->
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">العنوان
                                                                (فرنسي)</label>
                                                            <el-input v-model="item.title_fr"
                                                                placeholder="العنوان (فرنسي)"></el-input>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">الوصف
                                                                (فرنسي)</label>
                                                            <div class="editor-wrapper">
                                                                <Editor v-model="item.description_fr"
                                                                    :init="editorConfig" :key="`editor-${index}-fr`" />
                                                            </div>
                                                        </div>

                                                        <!-- Tagalog (Filipino) Fields -->
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">العنوان
                                                                (فلبيني)</label>
                                                            <el-input v-model="item.title_tl"
                                                                placeholder="العنوان (فلبيني)"></el-input>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">الوصف
                                                                (فلبيني)</label>
                                                            <div class="editor-wrapper">
                                                                <Editor v-model="item.description_tl"
                                                                    :init="editorConfig" :key="`editor-${index}-tl`" />
                                                            </div>
                                                        </div>

                                                        <!-- Urdu Fields -->
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">العنوان
                                                                (أردو)</label>
                                                            <el-input v-model="item.title_ur"
                                                                placeholder="العنوان (أردو)"></el-input>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label text-secondary">الوصف
                                                                (أردو)</label>
                                                            <div class="editor-wrapper">
                                                                <Editor v-model="item.description_ur"
                                                                    :init="editorConfig" :key="`editor-${index}-ur`" />
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12 text-start">
                                                            <el-button type="danger" @click="removeItem(index)" plain
                                                                size="small" :icon="Delete">
                                                                حذف العنصر
                                                            </el-button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <el-button type="primary" @click="addItem" plain :icon="Plus">
                                            إضافة عنصر
                                        </el-button>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-center mt-5">
                                    <button type="submit" class="btn btn-primary px-4 py-2" :disabled="show_loader">
                                        تحديث
                                        <i class="bi bi-save ms-2" v-if="!show_loader"></i>
                                        <span v-if="show_loader" class="spinner-border spinner-border-sm ms-2"
                                            role="status" aria-hidden="true"></span>
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
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import { ElButton, ElMessage, ElIcon } from "element-plus";
import { Delete, Plus } from '@element-plus/icons-vue';
import { Link } from "@inertiajs/vue3";
import Editor from '@tinymce/tinymce-vue';

const props = defineProps({
    aboutUs: Object,
});

const show_loader = ref(false);

const editorConfig = {
    height: 300,
    menubar: false,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic backcolor | \
               alignleft aligncenter alignright alignjustify | \
               bullist numlist outdent indent | removeformat | help',
    content_style: 'body { font-family:Tahoma,Arial,sans-serif; font-size:14px }',
    directionality: 'rtl'
};

const form = ref({
    id: props.aboutUs?.id || null,
    image1: null,
    image2: null,
    items: [],
    image1Preview: null,
    image2Preview: null,
});

// Initialize form with translations
const initializeForm = () => {
    if (props.aboutUs?.image1) {
        form.value.image1 = props.aboutUs.image1;
    }
    if (props.aboutUs?.image2) {
        form.value.image2 = props.aboutUs.image2;
    }

    if (props.aboutUs?.items && props.aboutUs.items.length > 0) {
        form.value.items = props.aboutUs.items.map(item => {
            const newItem = { order: item.order || 0 };

            // Handle both translation structures
            if (item.translations) {
                Object.entries(item.translations).forEach(([lang, translation]) => {
                    newItem[`title_${lang}`] = translation.title || '';
                    newItem[`description_${lang}`] = translation.description || '';
                });
            } else {
                // Handle direct properties
                const languages = ['en', 'ar', 'fr', 'tl', 'ur'];
                languages.forEach(lang => {
                    newItem[`title_${lang}`] = item[`title_${lang}`] || '';
                    newItem[`description_${lang}`] = item[`description_${lang}`] || '';
                });
            }

            return newItem;
        });
    } else {
        // Initialize with empty fields for all languages
        form.value.items = [{
            order: 0,
            title_en: '', description_en: '',
            title_ar: '', description_ar: '',
            title_fr: '', description_fr: '',
            title_tl: '', description_tl: '',
            title_ur: '', description_ur: ''
        }];
    }
};

const addItem = () => {
    form.value.items.push({
        title_en: '',
        description_en: '',
        title_ar: '',
        description_ar: '',
        title_fr: '',
        description_fr: '',
        title_tl: '',
        description_tl: '',
        title_ur: '',
        description_ur: '',
        order: form.value.items.length
    });
};

const removeItem = (index) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    } else {
        ElMessage.warning('يجب وجود عنصر واحد على الأقل');
    }
};

const handleFileChange = (field, file) => {
    if (file && file.raw) {
        form.value[field] = file.raw;
        const reader = new FileReader();
        reader.onload = (e) => {
            form.value[`${field}Preview`] = e.target.result;
        };
        reader.readAsDataURL(file.raw);
    }
};

const clearImage = (field) => {
    form.value[field] = null;
    form.value[`${field}Preview`] = null;
};

const submitForm = () => {
    show_loader.value = true;
    const formData = new FormData();

    if (form.value.id) {
        formData.append('id', form.value.id);
    }

    if (form.value.image1 instanceof File) {
        formData.append('image1', form.value.image1);
    } else if (form.value.image1) {
        formData.append('image_path1', form.value.image1);
    }

    if (form.value.image2 instanceof File) {
        formData.append('image2', form.value.image2);
    } else if (form.value.image2) {
        formData.append('image_path2', form.value.image2);
    }

    form.value.items.forEach((item, index) => {
        formData.append(`items[${index}][order]`, item.order || index);

        // Create translations object for each language
        const languages = ['en', 'ar', 'fr', 'tl', 'ur'];
        languages.forEach(lang => {
            formData.append(`items[${index}][translations][${lang}][title]`, item[`title_${lang}`] || '');
            formData.append(`items[${index}][translations][${lang}][description]`, item[`description_${lang}`] || '');
        });
    });

    router.post(route('about-us.update'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            ElMessage.success('تم التحديث بنجاح');
            show_loader.value = false;
        },
        onError: (errors) => {
            ElMessage.error('حدث خطأ أثناء التحديث');
            console.error(errors);
            show_loader.value = false;
        },
    });
};

onMounted(() => {
    initializeForm();
});
</script>

<style scoped>
/* RTL Styles */
[dir="rtl"] .breadcrumb-item+.breadcrumb-item::before {
    float: right;
    padding-left: var(--bs-breadcrumb-item-padding-x);
    padding-right: 0;
}

[dir="rtl"] .text-end {
    text-align: left !important;
}

[dir="rtl"] .text-start {
    text-align: right !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}

/* Main Styles */
.pagetitle {
    margin-bottom: 1.5rem;
    font-family: 'Tahoma', Arial, sans-serif;
}

.pagetitle h1 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #012970;
    font-weight: 600;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin-bottom: 0;
    font-family: 'Tahoma', Arial, sans-serif;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
    font-family: 'Tahoma', Arial, sans-serif;
}

.accordion-button:not(.collapsed) {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.img-thumbnail {
    max-width: 100%;
    max-height: 200px;
    border-radius: 6px;
    border: 1px solid #ddd;
    object-fit: contain;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #8c939d;
    font-size: 14px;
    font-family: 'Tahoma', Arial, sans-serif;
}

/* Image Upload Styles */
.image-upload-wrapper {
    position: relative;
    display: inline-block;
}

.delete-icon {
    position: absolute;
    top: 10px;
    left: 10px;
    color: #f56c6c;
    background: white;
    border-radius: 50%;
    padding: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    z-index: 10;
    font-size: 18px;
    transition: all 0.3s;
}

.delete-icon:hover {
    color: white;
    background: #f56c6c;
    transform: scale(1.1);
}

.el-upload--picture-card {
    position: relative;
    overflow: hidden;
    width: 100%;
    height: 200px;
    line-height: 200px;
}

/* Form Elements */
.form-label {
    font-family: 'Tahoma', Arial, sans-serif;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: block;
}

/* Buttons */
.el-button {
    font-family: 'Tahoma', Arial, sans-serif;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    font-family: 'Tahoma', Arial, sans-serif;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>
