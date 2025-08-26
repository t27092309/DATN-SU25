<template>
  <div class="p-6 bg-gray-100 min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Báo Cáo Doanh Thu</h1>
      <p class="text-gray-600">Tổng quan doanh thu và phân tích chi tiết theo sản phẩm, khách hàng</p>
      
      <!-- Ví dụ sử dụng PermissionGuard -->
      <PermissionGuard permission="reports:view">
        <!-- <div class="mt-4 p-4 bg-blue-50 rounded-lg">
          <p class="text-blue-800">Bạn có quyền xem báo cáo này</p>
        </div> -->
      </PermissionGuard>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
      <div class="flex flex-col md:flex-row gap-4 items-center">
        <div class="flex gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
            <input v-model="startDate" type="date" class="border border-gray-300 rounded-md px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
            <input v-model="endDate" type="date" class="border border-gray-300 rounded-md px-3 py-2">
          </div>
        </div>
        <button @click="fetchAllData" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 mt-6 md:mt-6">
          Cập nhật báo cáo
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6" v-if="keyMetrics">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Tổng Doanh Thu</p>
            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(keyMetrics.total_revenue) }}</p>
          </div>
          <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
            💰
          </div>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Doanh Thu TB/Ngày</p>
            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(keyMetrics.avg_daily_revenue) }}</p>
          </div>
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
            📈
          </div>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Số Đơn Hàng</p>
            <p class="text-2xl font-bold text-purple-600">{{ keyMetrics.order_count }}</p>
          </div>
          <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
            📦
          </div>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">GT Đơn Hàng TB</p>
            <p class="text-2xl font-bold text-orange-600">{{ formatCurrency(keyMetrics.avg_order_value) }}</p>
          </div>
          <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
            📅
          </div>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Tỷ Lệ Hủy</p>
            <p class="text-2xl font-bold text-red-600">{{ keyMetrics.cancellation_rate }}%</p>
          </div>
          <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
            ❌
          </div>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-md" v-if="conversionData">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Tỷ Lệ Chuyển Đổi</p>
            <p class="text-2xl font-bold text-teal-600">{{ conversionData.conversion_rate }}%</p>
          </div>
          <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center">
            🔄
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md" v-if="customerGrowthData">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Khách Hàng Mới</p>
            <p class="text-2xl font-bold text-pink-600">{{ customerGrowthData.new_customers_count }}</p>
          </div>
          <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
            🆕
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md" v-if="customerGrowthData">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Khách Hàng Cũ</p>
            <p class="text-2xl font-bold text-indigo-600">{{ customerGrowthData.loyal_customers_count }}</p>
          </div>
          <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
            🤝
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <div class="flex flex-col md:flex-row items-center justify-between mb-4">
        <h2 class="text-xl font-semibold mb-4 md:mb-0">Biểu Đồ Doanh Thu Tổng Quan</h2>
        
        <div class="space-x-2">
          <button @click="selectedChartType = 'daily'" 
                  :class="{'bg-blue-500 text-white': selectedChartType === 'daily', 'bg-gray-200': selectedChartType !== 'daily'}"
                  class="px-4 py-2 rounded-lg font-semibold transition-colors">Ngày</button>
          <button @click="selectedChartType = 'weekly'" 
                  :class="{'bg-blue-500 text-white': selectedChartType === 'weekly', 'bg-gray-200': selectedChartType !== 'weekly'}"
                  class="px-4 py-2 rounded-lg font-semibold transition-colors">Tuần</button>
          <button @click="selectedChartType = 'monthly'" 
                  :class="{'bg-blue-500 text-white': selectedChartType === 'monthly', 'bg-gray-200': selectedChartType !== 'monthly'}"
                  class="px-4 py-2 rounded-lg font-semibold transition-colors">Tháng</button>
          <button @click="selectedChartType = 'yearly'" 
                  :class="{'bg-blue-500 text-white': selectedChartType === 'yearly', 'bg-gray-200': selectedChartType !== 'yearly'}"
                  class="px-4 py-2 rounded-lg font-semibold transition-colors">Năm</button>
        </div>
      </div>
      
      <div class="relative h-96">
        <div v-if="selectedChartType === 'daily'">
          <canvas id="dailyRevenueChart" width="700" height="300"></canvas>
        </div>
        <div v-else-if="selectedChartType === 'weekly'">
          <canvas id="weeklyRevenueChart" width="700" height="300"></canvas>
        </div>
        <div v-else-if="selectedChartType === 'monthly'">
          <canvas id="monthlyRevenueChart" width="700" height="300"></canvas>
        </div>
        <div v-else>
          <canvas id="yearlyRevenueChart" width="700" height="300"></canvas>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex flex-col md:flex-row items-center justify-between mb-4">
          <h2 class="text-lg font-semibold mb-4 md:mb-0">Doanh Thu Theo Sản Phẩm</h2>
          <div class="space-x-2">
            <button @click="selectedProductView = 'all'" 
                    :class="{'bg-green-500 text-white': selectedProductView === 'all', 'bg-gray-200': selectedProductView !== 'all'}"
                    class="px-3 py-1 rounded-md text-sm font-medium transition-colors">Tất Cả</button>
            <button @click="selectedProductView = 'top5'" 
                    :class="{'bg-green-500 text-white': selectedProductView === 'top5', 'bg-gray-200': selectedProductView !== 'top5'}"
                    class="px-3 py-1 rounded-md text-sm font-medium transition-colors">Top 5</button>
          </div>
        </div>
        
        <div class="relative h-80">
          <canvas id="productsChart" :key="selectedProductView"  width="400" height="250"></canvas>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex flex-col md:flex-row items-center justify-between mb-4">
          <h2 class="text-lg font-semibold mb-4 md:mb-0">Doanh Thu Theo Khách Hàng</h2>
          <div class="space-x-2">
            <button @click="selectedCustomerView = 'top10'" 
                    :class="{'bg-purple-500 text-white': selectedCustomerView === 'top10', 'bg-gray-200': selectedCustomerView !== 'top10'}"
                    class="px-3 py-1 rounded-md text-sm font-medium transition-colors">Top 10</button>
            <button @click="selectedCustomerView = 'top5'" 
                    :class="{'bg-purple-500 text-white': selectedCustomerView === 'top5', 'bg-gray-200': selectedCustomerView !== 'top5'}"
                    class="px-3 py-1 rounded-md text-sm font-medium transition-colors">Top 5</button>
          </div>
        </div>
        
        <div class="relative h-80">
          <canvas id="customersChart" width="400" height="250"></canvas>
        </div>
      </div>
    </div>
    
    <div class="grid grid-cols-1 gap-6 mt-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Mã Giảm Giá Được Sử Dụng Nhiều Nhất</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã Giảm Giá</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số Lượt Sử Dụng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng Doanh Thu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng Tiền Giảm</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" v-if="topCouponsData && topCouponsData.length">
                        <tr v-for="coupon in topCouponsData" :key="coupon.coupon_code">
                            <td class="px-6 py-4 whitespace-nowrap">{{ coupon.coupon_code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ coupon.usage_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(coupon.total_revenue) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(coupon.total_discount) }}</td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                Không có dữ liệu về mã giảm giá trong khoảng thời gian này.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div v-if="loading" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
          <span>Đang tải dữ liệu...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';
import PermissionGuard from '@/components/common/PermissionGuard.vue';

// Reactive data
const loading = ref(false);
const startDate = ref('2025-01-01');
const endDate = ref('2025-12-31');

const selectedChartType = ref('daily');
const selectedProductView = ref('all');
const selectedCustomerView = ref('top10');

// Data storage
const keyMetrics = ref(null);
const dailyRevenueData = ref({});
const weeklyRevenueData = ref([]);
const monthlyRevenueData = ref([]);
const yearlyRevenueData = ref([]);
const productsData = ref([]);
const customersData = ref([]);

// Thêm các biến mới
const conversionData = ref(null);
const customerGrowthData = ref(null);
const topCouponsData = ref([]);

// Chart instances
const chartInstances = ref({});

// Utility functions
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value);
};

const getAuthHeaders = () => {
  const token = localStorage.getItem('authToken');
  return { Authorization: `Bearer ${token}` };
};

const getDateParams = () => ({
  start_date: startDate.value,
  end_date: endDate.value
});

// API calls
const fetchSummaryData = async () => {
  try {
    const response = await axios.get('admin/reports/summary', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });

    keyMetrics.value = response.data.key_metrics;
    dailyRevenueData.value = response.data.charts_data.daily_revenue;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu tổng quan:", error);
  }
};

const fetchWeeklyData = async () => {
  try {
    const response = await axios.get('admin/reports/weekly-revenue', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });
    weeklyRevenueData.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu tuần:", error);
  }
};

const fetchMonthlyData = async () => {
  try {
    const response = await axios.get('admin/reports/monthly-revenue', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });
    monthlyRevenueData.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu tháng:", error);
  }
};

const fetchYearlyData = async () => {
  try {
    const response = await axios.get('admin/reports/yearly-revenue', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });
    yearlyRevenueData.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu năm:", error);
  }
};

const fetchProductsData = async () => {
  try {
    const response = await axios.get('admin/reports/products', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });
    productsData.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu sản phẩm:", error);
  }
};

const fetchCustomersData = async () => {
  try {
    const response = await axios.get('admin/reports/customers', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });
    customersData.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu khách hàng:", error);
  }
};

// Thêm các API calls mới
const fetchConversionRate = async () => {
  try {
    const response = await axios.get('admin/reports/conversion-rate', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });
    conversionData.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu tỷ lệ chuyển đổi:", error);
  }
};

const fetchCustomerGrowth = async () => {
  try {
    const response = await axios.get('admin/reports/customer-growth', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });
    customerGrowthData.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu tăng trưởng khách hàng:", error);
  }
};

const fetchTopCouponsData = async () => {
  try {
    const response = await axios.get('admin/reports/top-coupons', {
      headers: getAuthHeaders(),
      params: getDateParams()
    });
    topCouponsData.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu mã giảm giá:", error);
  }
};

// Main fetch function
const fetchAllData = async () => {
  loading.value = true;
  try {
    await Promise.all([
      fetchSummaryData(),
      fetchWeeklyData(),
      fetchMonthlyData(),
      fetchYearlyData(),
      fetchProductsData(),
      fetchCustomersData(),
      fetchConversionRate(),
      fetchCustomerGrowth(),
      fetchTopCouponsData()
    ]);

    await nextTick();
    renderAllCharts();
  } catch (error) {
    console.error("Lỗi khi tải dữ liệu:", error);
  } finally {
    loading.value = false;
  }
};

// Chart rendering functions
const renderRevenueChart = (chartType) => {
  const chartId = chartType + 'RevenueChart';

  if (chartInstances.value[chartId]) {
    chartInstances.value[chartId].stop();
    chartInstances.value[chartId].destroy();
    chartInstances.value[chartId] = null;
  }

  const canvasElement = document.getElementById(chartId);
  if (!canvasElement) return;

  let chartConfig;

  switch (chartType) {
    case 'daily':
      chartConfig = {
        type: 'line',
        data: {
          labels: Object.keys(dailyRevenueData.value),
          datasets: [{
            label: 'Doanh thu hàng ngày',
            data: Object.values(dailyRevenueData.value),
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                }
              }
            }
          }
        }
      };
      break;

    case 'weekly':
      chartConfig = {
        type: 'bar',
        data: {
          labels: weeklyRevenueData.value.map(d => `Tuần ${d.week}`),
          datasets: [{
            label: 'Doanh thu hàng tuần',
            data: weeklyRevenueData.value.map(d => d.total),
            backgroundColor: '#10B981',
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                }
              }
            }
          }
        }
      };
      break;

    case 'monthly':
      chartConfig = {
        type: 'bar',
        data: {
          labels: monthlyRevenueData.value.map(d => `Tháng ${d.month}`),
          datasets: [{
            label: 'Doanh thu hàng tháng',
            data: monthlyRevenueData.value.map(d => d.total),
            backgroundColor: '#F59E0B',
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                }
              }
            }
          }
        }
      };
      break;

    case 'yearly':
      chartConfig = {
        type: 'bar',
        data: {
          labels: yearlyRevenueData.value.map(d => `Năm ${d.year}`),
          datasets: [{
            label: 'Doanh thu hàng năm',
            data: yearlyRevenueData.value.map(d => d.total),
            backgroundColor: '#EF4444',
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                }
              }
            }
          }
        }
      };
      break;
  }

  if (chartConfig) {
    chartInstances.value[chartId] = new Chart(canvasElement, chartConfig);
  }
};

const renderProductsChart = () => {
  const chartId = 'productsChart';

  if (chartInstances.value[chartId]) {
    chartInstances.value[chartId].stop();
    chartInstances.value[chartId].destroy();
    chartInstances.value[chartId] = null;
  }

  nextTick(() => {
    const canvasElement = document.getElementById(chartId);
    if (!canvasElement) return;

    const dataToShow = selectedProductView.value === 'top5'
      ? productsData.value.slice(0, 5)
      : productsData.value;

    if (!dataToShow.length) return;

    const chartConfig = {
      type: 'bar',
      data: {
        labels: dataToShow.map(d => d.product_name),
        datasets: [{
          label: 'Doanh thu sản phẩm',
          data: dataToShow.map(d => d.total_revenue),
          backgroundColor: '#10B981',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
              }
            }
          }
        }
      }
    };

    chartInstances.value[chartId] = new Chart(canvasElement, chartConfig);
  });
};


const renderCustomersChart = () => {
  const chartId = 'customersChart';

  if (chartInstances.value[chartId]) {
    chartInstances.value[chartId].stop();
    chartInstances.value[chartId].destroy();
    chartInstances.value[chartId] = null;
  }

  const canvasElement = document.getElementById(chartId);
  if (!canvasElement) return;

  const dataToShow = selectedCustomerView.value === 'top5'
    ? customersData.value.slice(0, 5)
    : customersData.value.slice(0, 10);

  const colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316'];

  const chartConfig = {
    type: 'doughnut',
    data: {
      labels: dataToShow.map(d => d.customer_name),
      datasets: [{
        data: dataToShow.map(d => d.total_spent),
        backgroundColor: colors.slice(0, dataToShow.length),
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  };

  chartInstances.value[chartId] = new Chart(canvasElement, chartConfig);
};

const renderAllCharts = () => {
  renderRevenueChart(selectedChartType.value);
  renderProductsChart();
  renderCustomersChart();
};

// Watchers
watch(selectedChartType, (newType) => {
  nextTick(() => {
    renderRevenueChart(newType);
  });
});

watch(selectedProductView, () => {
  nextTick(() => {
    renderProductsChart();
  });
});

watch(selectedCustomerView, () => {
  nextTick(() => {
    renderCustomersChart();
  });
});

// Lifecycle
onMounted(() => {
  fetchAllData();
});
</script>

<style scoped>
.transition-colors {
  transition: background-color 0.2s, color 0.2s;
}
</style>

<style scoped>
/* Custom styles if needed */
.transition-colors {
  transition: background-color 0.2s, color 0.2s;
}
</style>