<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">
                {{ $t("reports.subscription.title") }}
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <SummaryCard
                        :title="
                            $t(
                                'reports.subscription.summary.total_subscriptions'
                            )
                        "
                        :value="report.summary.total_subscriptions"
                        color="blue"
                        icon="Document"
                    />
                    <SummaryCard
                        :title="
                            $t(
                                'reports.subscription.summary.active_subscriptions'
                            )
                        "
                        :value="report.summary.active_subscriptions"
                        color="green"
                        icon="Check"
                    />
                    <SummaryCard
                        :title="
                            $t('reports.subscription.summary.total_revenue')
                        "
                        :value="formatCurrency(report.summary.total_revenue)"
                        color="yellow"
                        icon="Money"
                    />
                    <SummaryCard
                        :title="
                            $t(
                                'reports.subscription.summary.average_subscription'
                            )
                        "
                        :value="
                            formatCurrency(report.summary.average_subscription)
                        "
                        color="purple"
                        icon="TrendingUp"
                    />
                </div>

                <!-- Filters Section -->
                <el-card class="mb-6">
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
                                <span> {{ $t('reports.provider_performance.export_pdf') }}</span>
                            </el-button>
                            <el-button
                                type="success"
                                @click="exportReport('excel')"
                                :icon="Document"
                                class="flex items-center gap-2"
                            >
                                <span>{{ $t('reports.provider_performance.export_excel') }}</span>
                            </el-button>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="mt-4">
                        <el-select
                            v-model="filters.status"
                            :placeholder="
                                $t('reports.subscription.filters.status')
                            "
                            class="w-full md:w-auto"
                            @change="applyFilters"
                        >
                            <el-option
                                :label="
                                    $t(
                                        'reports.subscription.filters.status_options.all'
                                    )
                                "
                                value=""
                            />
                            <el-option
                                :label="
                                    $t(
                                        'reports.subscription.filters.status_options.active'
                                    )
                                "
                                value="active"
                            />
                            <el-option
                                :label="
                                    $t(
                                        'reports.subscription.filters.status_options.expired'
                                    )
                                "
                                value="expired"
                            />
                            <el-option
                                :label="
                                    $t(
                                        'reports.subscription.filters.status_options.pending'
                                    )
                                "
                                value="pending"
                            />
                            <el-option
                                :label="
                                    $t(
                                        'reports.subscription.filters.status_options.canceled'
                                    )
                                "
                                value="canceled"
                            />
                        </el-select>
                    </div>
                </el-card>

                <!-- Subscriptions Table -->

                    <SubscriptionTable
                        :subscriptions="report.subscriptions"
                        :pagination="pagination"
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                    />
             
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SummaryCard from "@/Components/Reports/SummaryCard.vue";
import { Document, Printer } from "@element-plus/icons-vue";
import SubscriptionTable from "@/Components/Reports/SubscriptionTable.vue";
import { useI18n } from "vue-i18n";

const t = useI18n();
const props = defineProps({
    report: Object,
    filters: Object,
    pagination: Object,
});

const loading = ref(false);
const currentPage = ref(props.pagination?.currentPage || 1);
const perPage = ref(props.pagination?.perPage || 10);

const filters = ref({
    dateRange: {
        start: props.filters?.dateRange?.start || null,
        end: props.filters?.dateRange?.end || null,
    },
    status: props.filters?.status || "",
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("ar-SA", {
        style: "currency",
        currency: "SAR",
    }).format(value);
};

const getStatusType = (status) => {
    const types = {
        active: "success",
        expired: "danger",
        pending: "warning",
        canceled:'danger'
    };
    return types[status] || "info";
};

const getStatusLabel = (status) => {
    return status === "active"
        ? "نشط"
        : status === "expired"
        ? "منتهي"
        : "قيد الانتظار";
};

const getRenewalStatusType = (status) => {
    const types = {
        active: "success",
        expired: "danger",
        pending_renewal: "warning",
    };
    return types[status] || "info";
};

const getRenewalStatusLabel = (status) => {
    return status === "active"
        ? t('active')
        : status === "expired"
        ?  t('expired')
        : t('pending_renew');
};

const applyFilters = () => {
    loading.value = true;
    router.get(
        route("reports.subscription"),
        {
            ...filters.value,
            page: currentPage.value,
            perPage: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => (loading.value = false),
        }
    );
};

const exportReport = (type) => {
    window.location.href = route("reports.subscription", {
        ...filters.value,
        export: type,
    });
};

const handleSizeChange = (val) => {
    perPage.value = val;
    currentPage.value = 1;
    applyFilters();
};

const handleCurrentChange = (val) => {
    currentPage.value = val;
    applyFilters();
};
</script>
