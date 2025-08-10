<template>
    <div class="container mt-5">
        <h1>Demo ScentGroupSelector</h1>
        <p>Đây là trang dùng để kiểm tra component ScentGroupSelector một cách độc lập.</p>

        <hr>

        <h2>ScentGroupSelector instance 1 (Với dữ liệu giả lập ban đầu)</h2>
        <ScentGroupSelector
            v-model:selectedScentGroupIds="demoSelectedScentGroupIds1"
            v-model:scentGroupsData="demoScentGroupsData1"
            :allScentGroups="demoAllScentGroups"
        />
        <div class="mt-3 p-3 bg-light rounded">
            <h4>Dữ liệu hiện tại của ScentGroupSelector 1:</h4>
            <pre>selectedScentGroupIds: {{ demoSelectedScentGroupIds1 }}</pre>
            <pre>scentGroupsData: {{ demoScentGroupsData1 }}</pre>
        </div>

        <hr>

        <h2>ScentGroupSelector instance 2 (Bắt đầu trống)</h2>
        <ScentGroupSelector
            v-model:selectedScentGroupIds="demoSelectedScentGroupIds2"
            v-model:scentGroupsData="demoScentGroupsData2"
            :allScentGroups="demoAllScentGroups"
        />
        <div class="mt-3 p-3 bg-light rounded">
            <h4>Dữ liệu hiện tại của ScentGroupSelector 2:</h4>
            <pre>selectedScentGroupIds: {{ demoSelectedScentGroupIds2 }}</pre>
            <pre>scentGroupsData: {{ demoScentGroupsData2 }}</pre>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import ScentGroupSelector from '@/components/admin/product/ScentGroupSelector.vue'; // Import component của bạn

// Dữ liệu giả lập cho tất cả các nhóm hương có sẵn (thay vì gọi API)
// Bạn có thể lấy dữ liệu này từ console log mà bạn đã thấy
const demoAllScentGroups = ref([
    { id: 1, name: 'Hương Hoa', color_code: '#FFC0CB' },
    { id: 2, name: 'Hương Gỗ', color_code: '#8B4513' },
    { id: 3, name: 'Hương Citrus', color_code: '#FFA500' },
    { id: 4, name: 'Hương Đông', color_code: '#800000' },
    { id: 5, name: 'Hương Thảo Mộc', color_code: '#228B22' },
    { id: 6, name: 'Hương Nước', color_code: '#ADD8E6' },
    { id: 7, name: 'Hương Da Thuộc', color_code: '#A0522D' },
    { id: 8, name: 'Hương Động Vật', color_code: '#4B0082' },
    { id: 9, name: 'Hương Gia Vị', color_code: '#DAA520' },
    // ... thêm các nhóm hương khác nếu cần
]);

// Dữ liệu ban đầu cho Instance 1
const demoSelectedScentGroupIds1 = ref([1, 3]);
const demoScentGroupsData1 = ref({
    1: { strength: 75 },
    3: { strength: 40 }
});

// Dữ liệu ban đầu cho Instance 2 (bắt đầu trống)
const demoSelectedScentGroupIds2 = ref([]);
const demoScentGroupsData2 = ref({});

onMounted(() => {
    // Có thể mô phỏng việc fetch data nếu cần
    console.log('Demo Page Mounted!');
});
</script>

<style scoped>
/* Các style riêng cho trang demo nếu cần */
pre {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    font-size: 0.85em;
}
</style>