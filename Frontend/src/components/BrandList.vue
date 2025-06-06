<template>
    <div class=" flex-shrink-0">
        <h3 class="text-xl font-semibold mb-4">Các hãng</h3>
        <div class="flex flex-col gap-4 overflow-y-auto p-2 bg-gray-50 rounded-lg h-[600px]">
            <div v-for="brand in brands" :key="brand.name" class="flex-shrink-0 flex items-center justify-center w-full h-20
               cursor-pointer hover:bg-gray-100 transition-colors duration-200 rounded-md p-2"
                 :class="{ 'bg-blue-100 ring-2 ring-blue-500': selectedBrand === brand.name }"
                 @click="selectBrand(brand.name)">
                <img :src="brand.imageUrl" :alt="brand.name" class="max-h-full max-w-full object-contain">
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BrandList',
    props: {
        brands: {
            type: Array,
            required: true
        },
        selectedBrand: {
            type: String,
            default: null
        }
    },
    methods: {
        selectBrand(brandName) {
            // Nếu hãng được click hiện đang là hãng đã chọn, hủy chọn nó.
            // Ngược lại, chọn hãng mới.
            if (this.selectedBrand === brandName) {
                this.$emit('select-brand', null); // Gửi 'null' để hủy chọn
            } else {
                this.$emit('select-brand', brandName); // Chọn hãng mới
            }
        }
    }
};
</script>

<style scoped>
@import '@/assets/tailwind.css';

/* Ẩn thanh cuộn mặc định của trình duyệt */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}
</style>