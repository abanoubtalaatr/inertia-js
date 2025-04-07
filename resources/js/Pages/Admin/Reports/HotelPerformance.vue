<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t("reports.hotel_performance.title") }}
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters Section -->
                <el-card class="mb-6">
                    <!-- Loading Indicator -->
                    <div
                        v-if="loading"
                        class="flex justify-center items-center py-4"
                    >
                        <el-spinner size="40" />
                    </div>

                    <div v-else>
                        <div
                            class="flex flex-wrap items-center justify-between gap-4"
                        >
                            <!-- Date Range -->
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <span>{{ $t('reports.provider_performance.from') }}:</span>
                                    <el-date-picker
                                        v-model="filters.dateRange.start"
                                        type="date"
                                        :placeholder="$t('reports.hotel_performance.filters.date_from')"
                                        format="YYYY/MM/DD"
                                        value-format="YYYY-MM-DD"
                                        @change="applyFilters"
                                        clearable
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span>{{ $t('reports.provider_performance.to') }}:</span>
                                    <el-date-picker
                                        v-model="filters.dateRange.end"
                                        type="date"
                                        :placeholder="$t('reports.hotel_performance.filters.date_to')"
                                        format="YYYY/MM/DD"
                                        value-format="YYYY-MM-DD"
                                        @change="applyFilters"
                                        clearable
                                    />
                                </div>
                            </div>

                            <!-- Export Buttons -->
                            <div class="flex items-center gap-2">
                                <el-button
                                    type="primary"
                                    @click="exportReport('pdf')"
                                    :icon="Printer"
                                    class="flex items-center gap-2"
                                >
                                    <span>{{ $t('reports.hotel_performance.export_pdf') }}</span>
                                </el-button>
                                <el-button
                                    type="success"
                                    @click="exportReport('excel')"
                                    :icon="Document"
                                    class="flex items-center gap-2"
                                >
                                    <span>{{ $t('reports.hotel_performance.export_excel') }}</span>
                                </el-button>
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="mt-4">
                            <el-select
                                v-model="filters.rating"
                                :placeholder="
                                    $t(
                                        'reports.hotel_performance.filters.rating.label'
                                    )
                                "
                                class="w-full"
                                :popper-class="[
                                    'el-select-dropdown',
                                    {
                                        'select-dropdown-rtl':
                                            $page.props.locale === 'ar',
                                    },
                                ]"
                                :class="{
                                    'direction-rtl':
                                        $page.props.locale === 'ar',
                                }"
                                @change="applyFilters"
                            >
                                <el-option
                                    :label="
                                        $t(
                                            'reports.hotel_performance.filters.rating.all'
                                        )
                                    "
                                    value=""
                                />
                                <el-option
                                    v-for="rating in 5"
                                    :key="rating"
                                    :label="`${rating} ${$t(
                                        'reports.hotel_performance.filters.rating.stars'
                                    )}`"
                                    :value="rating"
                                />
                            </el-select>
                        </div>
                    </div>
                </el-card>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <SummaryCard
                        :title="
                            $t('reports.hotel_performance.summary.total_hotels')
                        "
                        :value="report.summary.total_hotels"
                        color="blue"
                        icon="Office"
                    />
                    <SummaryCard
                        :title="
                            $t(
                                'reports.hotel_performance.summary.total_contracts'
                            )
                        "
                        :value="report.summary.total_contracts"
                        color="green"
                        icon="Document"
                    />
                    <SummaryCard
                        :title="
                            $t(
                                'reports.hotel_performance.summary.average_rating'
                            )
                        "
                        :value="`${report.summary.average_rating} ${$t(
                            'reports.hotel_performance.table.stars'
                        )}`"
                        color="yellow"
                        icon="StarFilled"
                    />
                    <SummaryCard
                        :title="
                            $t('reports.hotel_performance.summary.total_spent')
                        "
                        :value="formatCurrency(report.summary.total_spent)"
                        color="red"
                        icon="Money"
                    />
                </div>

                <!-- Hotels Table -->
                <HotelsTable
                    :hotels="report.hotels"
                    :pagination="pagination"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SummaryCard from "@/Components/Reports/SummaryCard.vue";
import { Printer, Document } from "@element-plus/icons-vue";
import ar from "element-plus/dist/locale/ar";
import en from "element-plus/dist/locale/en";
import { ElConfigProvider } from "element-plus";
import HotelsTable from "@/Components/Reports/HotelsTable.vue";
const props = defineProps({
    report: Object,
    filters: Object,
    pagination: Object,
    total: Number,
});

const filters = ref({
    dateRange: {
        start: null,
        end: null,
    },
    rating: "",
});

const currentPage = ref(props.pagination?.currentPage || 1);
const perPage = ref(props.pagination?.perPage || 10);
const loading = ref(false);

const total = computed(() => props.pagination?.total || 0);

const formatCurrency = (value) => {
    return new Intl.NumberFormat("ar-SA", {
        style: "currency",
        currency: "SAR",
    }).format(value);
};

const applyFilters = () => {
    loading.value = true;
    router.get(
        route("reports.hotel-performance"),
        {
            ...filters.value,

        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
            },
        }
    );
};

const exportReport = (type) => {
    window.location.href = route("reports.hotel-performance", {
        ...filters.value,
        export: type,
    });
};

const handleSizeChange = (val) => {
    perPage.value = val;
    router.get(
        route("reports.hotel-performance"),
        {
            ...filters.value,
            perPage: val,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const handleCurrentChange = (val) => {
    currentPage.value = val;
    router.get(
        route("reports.hotel-performance"),
        {
            ...filters.value,
            page: val,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};
</script>
