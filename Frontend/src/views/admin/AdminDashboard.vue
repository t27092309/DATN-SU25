<template>
  <div class="p-6 bg-gray-100">
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Biểu đồ Doanh thu</h2>
        
        <div class="space-x-2">
          <button @click="selectedChartType = 'yearly'" 
                  :class="{'bg-blue-500 text-white': selectedChartType === 'yearly', 'bg-gray-200': selectedChartType !== 'yearly'}"
                  class="px-4 py-2 rounded-lg font-semibold">Năm</button>
          <button @click="selectedChartType = 'monthly'" 
                  :class="{'bg-blue-500 text-white': selectedChartType === 'monthly', 'bg-gray-200': selectedChartType !== 'monthly'}"
                  class="px-4 py-2 rounded-lg font-semibold">Tháng</button>
          <button @click="selectedChartType = 'weekly'" 
                  :class="{'bg-blue-500 text-white': selectedChartType === 'weekly', 'bg-gray-200': selectedChartType !== 'weekly'}"
                  class="px-4 py-2 rounded-lg font-semibold">Tuần</button>
          <button @click="selectedChartType = 'daily'" 
                  :class="{'bg-blue-500 text-white': selectedChartType === 'daily', 'bg-gray-200': selectedChartType !== 'daily'}"
                  class="px-4 py-2 rounded-lg font-semibold">Ngày</button>
        </div>
      </div>
      
      <div v-if="selectedChartType === 'yearly'">
        <canvas id="yearlyRevenueChart" width="700" height="250"></canvas>
      </div>
      <div v-else-if="selectedChartType === 'monthly'">
        <canvas id="monthlyRevenueChart" width="700" height="250"></canvas>
      </div>
      <div v-else-if="selectedChartType === 'weekly'">
        <canvas id="weeklyRevenueChart" width="700" height="250"></canvas>
      </div>
      <div v-else>
        <canvas id="dailyRevenueChart" width="700" height="250"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';
import zoomPlugin from 'chartjs-plugin-zoom';
Chart.register(zoomPlugin);

const selectedChartType = ref('yearly');

const dailyRevenueData = ref({});
const weeklyRevenueData = ref([]);
const monthlyRevenueData = ref([]);
const yearlyRevenueData = ref([]);

const chartInstances = ref({});

const fetchData = async () => {
  try {
    const token = localStorage.getItem('authToken');
    const headers = { Authorization: `Bearer ${token}` };
    const dateParams = { start_date: '2025-01-01', end_date: '2025-12-31' };

    const dailyRes = await axios.get('/reports/summary', { headers, params: dateParams });
    dailyRevenueData.value = dailyRes.data.charts_data.daily_revenue;
    
    const weeklyRes = await axios.get('/reports/weekly-revenue', { headers, params: dateParams });
    weeklyRevenueData.value = weeklyRes.data;

    const monthlyRes = await axios.get('/reports/monthly-revenue', { headers, params: dateParams });
    monthlyRevenueData.value = monthlyRes.data;
    
    const yearlyRes = await axios.get('/reports/yearly-revenue', { headers, params: dateParams });
    yearlyRevenueData.value = yearlyRes.data;

    // Khi dữ liệu đã có, vẽ biểu đồ mặc định
    await nextTick(() => {
      renderChart(selectedChartType.value);
    });

  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu báo cáo:", error);
  }
};

const renderChart = (chartType) => {
    // Hủy biểu đồ cũ nếu có
    if (chartInstances.value[chartType]) {
        chartInstances.value[chartType].destroy();
    }
    
    const canvasElement = document.getElementById(chartType + 'RevenueChart');
    if (!canvasElement) return;

    let chartConfig;
    switch (chartType) {
        case 'yearly':
            chartConfig = {
                type: 'bar',
                data: {
                    labels: yearlyRevenueData.value.map(d => `Năm ${d.year}`),
                    datasets: [{
                        label: 'Doanh thu hàng năm',
                        data: yearlyRevenueData.value.map(d => d.total),
                        backgroundColor: '#d53f8c',
                    }]
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
                        backgroundColor: '#ed8936',
                    }]
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
                        backgroundColor: '#48bb78',
                    }]
                }
            };
            break;
        case 'daily':
            chartConfig = {
                type: 'line',
                data: {
                    labels: Object.keys(dailyRevenueData.value),
                    datasets: [{
                        label: 'Doanh thu hàng ngày',
                        data: Object.values(dailyRevenueData.value),
                        borderColor: '#4299e1',
                    }]
                }
            };
            break;
        default:
            return;
    }

    chartInstances.value[chartType] = new Chart(canvasElement, chartConfig);
};

// Theo dõi sự thay đổi của selectedChartType
watch(selectedChartType, (newType) => {
  // nextTick đảm bảo rằng DOM đã được cập nhật trước khi cố gắng vẽ biểu đồ
  nextTick(() => {
    renderChart(newType);
  });
});

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
/* Các style cục bộ nếu cần */
</style>