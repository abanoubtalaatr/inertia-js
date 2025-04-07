<template>
    <AuthenticatedLayout>
      <!-- Breadcrumb -->
      <div class="pagetitle">
        <h1>{{ $t('dashboard') }}</h1>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <Link class="nav-link" :href="route('dashboard')">
                {{ $t('Home') }}
              </Link>
            </li>
          </ol>
        </nav>
      </div>
      <!-- End Breadcrumb -->
  
      <section class="section dashboard" v-if="!loading">
        <div class="row">
          <!-- Left side columns -->
          <div class="col-lg-12">
            <div class="row">
              <!-- Total Users Card -->
              <div class="col-xxl-4 col-md-4">
                <div class="card info-card customers-card">
                  <div class="card-body">
                    <h5 class="card-title">{{ $t('users') }}</h5>
                    <div class="d-flex align-items-center">
                      <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-people"></i>
                      </div>
                      <div class="ps-3">
                        <h6>{{ userCount }}</h6>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Bookings Card -->
              <div class="col-xxl-4 col-md-4">
                <div class="card info-card sales-card">
                  <div class="card-body">
                    <h5 class="card-title">{{ $t('Bookings') }}</h5>
                    <div class="d-flex align-items-center">
                      <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-book"></i>
                      </div>
                      <div class="ps-3">
                        <h6>{{ bookingsCount }}</h6>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Roles Card -->
              <div class="col-xxl-4 col-md-4" v-if="role !='company'">
                <div class="card info-card revenue-card">
                  <div class="card-body">
                    <h5 class="card-title">{{ $t('roles') }}</h5>
                    <div class="d-flex align-items-center">
                      <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-lock"></i>
                      </div>
                      <div class="ps-3">
                        <h6>{{ rolesCount }}</h6>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Reports Section -->
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex flex-col space-y-4">
            <h2 class="font-semibold text-xl text-gray-800">{{ $t('reports.dashboard.title') }}</h2>
  
            <!-- Filters Section -->
            <div class="grid grid-cols-4 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('from_date') }}</label>
                <el-date-picker
                  v-model="dateRange[0]"
                  type="date"
                  :placeholder="$t('choose_date')"
                  format="YYYY/MM/DD"
                  value-format="YYYY-MM-DD"
                  class="w-full"
                  @change="updateData"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('to_date') }}</label>
                <el-date-picker
                  v-model="dateRange[1]"
                  type="date"
                  :placeholder="$t('choose_date')"
                  format="YYYY/MM/DD"
                  value-format="YYYY-MM-DD"
                  class="w-full"
                  @change="updateData"
                />
              </div>
            </div>
  
            <!-- Quick Filters -->
            <div class="flex gap-2 mb-4">
              <el-button size="small" @click="setDateRange('week')">{{ $t('last_week') }}</el-button>
              <el-button size="small" @click="setDateRange('month')">{{ $t('last_month') }}</el-button>
              <el-button size="small" @click="setDateRange('quarter')">{{ $t('last_3_months') }}</el-button>
            </div>
  
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8" >
              <!-- Users by Role Summary -->
              <el-card shadow="hover" :body-style="{ padding: '20px' }" v-if="role !='company'">
                <div class="flex flex-col">
                  <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold">{{ $t('users_by_role') }}</span>
                    <el-tag :type="summaryData?.users?.growth >= 0 ? 'success' : 'danger'" size="small">
                      {{ formatGrowth(summaryData?.users?.growth ?? 0) }}
                    </el-tag>
                  </div>
                  <div class="grid grid-cols-3 gap-4" >

                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                      <div class="text-xl font-bold text-blue-600">{{ summaryData?.users?.specialists ?? 0 }}</div>
                      <div class="text-sm text-gray-600">{{ $t('specialists') }}</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                      <div class="text-xl font-bold text-green-600">{{ summaryData?.users?.companies ?? 0 }}</div>
                      <div class="text-sm text-gray-600">{{ $t('companies') }}</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                      <div class="text-xl font-bold text-purple-600">{{ summaryData?.users?.admins ?? 0 }}</div>
                      <div class="text-sm text-gray-600">{{ $t('admins') }}</div>
                    </div>
                  </div>
                </div>
              </el-card>
  
              <!-- Bookings Summary -->
              <el-card shadow="hover" :body-style="{ padding: '20px' }">
                <div class="flex flex-col">
                  <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold">{{ $t('Bookings') }}</span>
                    <el-tag :type="summaryData?.bookings?.growth >= 0 ? 'success' : 'danger'" size="small">
                      {{ formatGrowth(summaryData?.bookings?.growth ?? 0) }}
                    </el-tag>
                  </div>
                  <div class="text-3xl font-bold text-indigo-600 text-center mb-4">
                    {{ summaryData?.bookings?.total ?? 0 }}
                  </div>
                </div>
              </el-card>
  
              <!-- Contacts Summary (remove if no Contact model) -->
              <el-card shadow="hover" :body-style="{ padding: '20px' }" v-if="role !='company'">
                <div class="flex flex-col">
                  <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold">{{ $t('contacts') }}</span>
                    <el-tag :type="summaryData?.contacts?.growth >= 0 ? 'success' : 'danger'" size="small">
                      {{ formatGrowth(summaryData?.contacts?.growth ?? 0) }}
                    </el-tag>
                  </div>
                  <div class="text-3xl font-bold text-indigo-600 text-center mb-4">
                    {{ summaryData?.contacts?.total ?? 0 }}
                  </div>
                </div>
              </el-card>
            </div>
  
            <!-- Charts -->
            
          </div>
        </div>
      </section>
      <div v-else class="text-center p-6">Loading...</div>
    </AuthenticatedLayout>
  </template>
  
  <script setup>
  import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
  import { ref, onMounted } from "vue";
  import { router } from "@inertiajs/vue3";
  import LineChart from "@/Components/Charts/LineChart.vue";
  import BarChart from "@/Components/Charts/BarChart.vue";
  import { useI18n } from "vue-i18n";
  
  const { t } = useI18n();
  const loading = ref(true);
  
  const props = defineProps({
    userCount: Number,
    bookingsCount: Number,
    rolesCount: Number,
    role :String,
    summaryData: {
      type: Object,
      default: () => ({
        users: { specialists: 0, companies: 0, admins: 0, growth: 0 },
        bookings: { total: 0, growth: 0 },
        contacts: { total: 0, growth: 0 },
      }),
    },
    bookingsData: {
      type: Object,
      default: () => ({
        trends: { labels: [], values: [] },
      }),
    },
    userGrowthData: {
      type: Object,
      default: () => ({
        registration: { labels: [], specialists: [], companies: [], admins: [] },
      }),
    },
  });
  
  const dateRange = ref([null, null]);
  
  const setDateRange = (period) => {
    const end = new Date();
    const start = new Date();
  
    switch (period) {
      case "week":
        start.setDate(end.getDate() - 7);
        break;
      case "month":
        start.setDate(end.getDate() - 30);
        break;
      case "quarter":
        start.setDate(end.getDate() - 90);
        break;
    }
  
    dateRange.value = [start.toISOString().split("T")[0], end.toISOString().split("T")[0]];
    updateData();
  };
  
  const updateData = () => {
    if (!dateRange.value[0] || !dateRange.value[1]) return;
  
    loading.value = true;
    router.get(
      route("dashboard"),
      {
        start_date: dateRange.value[0],
        end_date: dateRange.value[1],
      },
      {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => (loading.value = false),
      }
    );
  };
  
  const formatGrowth = (value) => {
    const sign = value >= 0 ? "+" : "";
    return `${sign}${value}%`;
  };
  
  onMounted(() => {
    const end = new Date();
    const start = new Date();
    start.setMonth(start.getMonth() - 1);
    dateRange.value = [start.toISOString().split("T")[0], end.toISOString().split("T")[0]];
    updateData();
  });
  </script>