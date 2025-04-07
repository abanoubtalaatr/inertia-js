<template>
    <el-card>
        <div class="overflow-x-auto">
            <el-table
                :data="hotels"
                style="width: 100%"
                :default-sort="{ prop: 'contracts_count', order: 'descending' }"
                v-loading="loading"
                :empty-text="$t('common.no_data')"
            >
                <el-table-column
                    prop="name"
                    :label="$t('reports.hotel_performance.table.hotel_name')"
                />
                <el-table-column
                    :label="$t('reports.hotel_performance.table.rating')"
                    width="120"
                >
                    <template #default="{ row }">
                        <el-rate
                            v-model="row.average_rating"
                            disabled
                            show-score
                        />
                    </template>
                </el-table-column>
                <el-table-column
                    :label="
                        $t('reports.hotel_performance.table.requested_services')
                    "
                >
                    <template #default="{ row }">
                        <div class="flex flex-wrap gap-1">
                            <el-tag
                                v-for="service in row.sub_services"
                                :key="service"
                                size="small"
                            >
                                {{ service }}
                            </el-tag>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                    prop="contracts_count"
                    :label="
                        $t('reports.hotel_performance.table.contracts_count')
                    "
                    width="120"
                    sortable
                />
                <el-table-column
                    :label="$t('reports.hotel_performance.table.total_spent')"
                    width="150"
                    sortable
                >
                    <template #default="{ row }">
                        {{ formatCurrency(row.total_spent) }}
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-end mt-4 rtl:space-x-reverse">
            <el-config-provider :locale="currentLocale">
                <el-pagination
                    v-model:current-page="currentPage"
                    v-model:page-size="pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    :layout="paginationLayout"
                    :total="pagination.total"
                    :prev-text="$t('pagination.previous')"
                    :next-text="$t('pagination.next')"
                    class="pagination-wrapper"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                />
            </el-config-provider>
        </div>
    </el-card>
</template>

<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { ElConfigProvider } from "element-plus";
import ar from "element-plus/dist/locale/ar";
import en from "element-plus/dist/locale/en";

const props = defineProps({
    hotels: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    pagination: {
        type: Object,
        required: true,
    },
});

const loading = ref(false);
const page = usePage();

const currentPage = ref(props.pagination?.currentPage || 1);
const pageSize = ref(props.pagination?.perPage || 10);

const currentLocale = computed(() => {
    return page.props.locale === "ar" ? ar : en;
});

const paginationLayout = computed(() => {
    return window.innerWidth < 640
        ? "prev, pager, next"
        : "total, sizes, prev, pager, next";
});

const handleSizeChange = (val) => {
    loading.value = true;
    pageSize.value = val;
    currentPage.value = 1;
    router.get(
        route("reports.hotel-performance"),
        {
            ...props.filters,
            perPage: val,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => (loading.value = false),
        }
    );
};

const handleCurrentChange = (val) => {
    loading.value = true;
    currentPage.value = val;
    router.get(
        route("reports.hotel-performance"),
        {
            ...props.filters,
            page: val,
            perPage: pageSize.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => (loading.value = false),
        }
    );
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("ar-SA", {
        style: "currency",
        currency: "SAR",
    }).format(value);
};
</script>

<style scoped>
</style>
