<template>
    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 items-start sm:items-center">
        <div class="relative inline-block text-left w-full sm:w-auto">
            <button @click="toggleDropdown"
                class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                id="menu-button" aria-expanded="true" aria-haspopup="true">
                Nhóm Hương
                <span v-if="selectedAromas.length > 0" class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                    {{ selectedAromas.length }}
                </span>
                <svg class="ml-2 -mr-1 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <div v-if="isDropdownOpen"
                class="origin-top-left absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-20"
                role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                <div class="py-1" role="none">
                    <a v-for="aroma in aromaOptions" :key="aroma"
                       href="#"
                       class="text-gray-700 block px-4 py-2 text-sm"
                       :class="{ 'bg-blue-100 font-semibold': selectedAromas.includes(aroma), 'hover:bg-gray-100': !selectedAromas.includes(aroma) }"
                       role="menuitem"
                       tabindex="-1"
                       @click.prevent="toggleAromaSelection(aroma)">
                        {{ aroma }}
                    </a>
                </div>
            </div>
        </div>

        <button
            v-if="selectedAromas.length > 0"
            @click="clearAromaSelection"
            class=" py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200"
            :class="{
                'bg-red-500 text-white hover:bg-red-600': selectedAromas.length > 0,
                'w-full sm:w-auto': true
            }"
        >
            Hủy chọn nhóm hương ({{ selectedAromas.length }})
        </button>

        <div class="flex flex-wrap gap-2 sm:space-x-2 w-full sm:w-auto items-center">
            <button v-for="priceRange in priceRanges" :key="priceRange.value"
                @click="selectPriceRange(priceRange.value)" :class="{
                    'bg-blue-600 text-white': selectedPriceRange === priceRange.value,
                    'bg-gray-100 text-gray-700 hover:bg-gray-200': selectedPriceRange !== priceRange.value,
                }" class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                {{ priceRange.label }}
            </button>
            <button
                v-if="selectedPriceRange"
                @click="clearPriceRangeSelection"
                class="px-3 py-1.5 rounded-md text-xs font-medium bg-red-500 text-white hover:bg-red-600 transition-colors duration-200"
            >
                Hủy chọn giá
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProductFilters',
    props: {
        priceRanges: {
            type: Array,
            required: true
        },
        selectedPriceRange: {
            type: String,
            default: null
        },
        aromaOptions: {
            type: Array,
            default: () => []
        },
        selectedAromas: { // Prop này phải là mảng
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            isDropdownOpen: false,
        };
    },
    methods: {
        selectPriceRange(range) {
            if (this.selectedPriceRange === range) {
                this.$emit('update:selectedPriceRange', null);
                this.$emit('select-price-range', null);
            } else {
                this.$emit('update:selectedPriceRange', range);
                this.$emit('select-price-range', range);
            }
        },
        toggleAromaSelection(aroma) {
            const currentSelectedAromas = [...this.selectedAromas];
            const index = currentSelectedAromas.indexOf(aroma);

            if (index > -1) {
                currentSelectedAromas.splice(index, 1);
            } else {
                currentSelectedAromas.push(aroma);
            }

            // this.isDropdownOpen = false; // Bỏ comment nếu bạn muốn dropdown tự đóng sau khi chọn

            this.$emit('update:selectedAromas', currentSelectedAromas);
            this.$emit('select-aroma', currentSelectedAromas);
        },
        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen;
        },
        closeDropdownProgrammatically() {
            this.isDropdownOpen = false;
        },
        clearAromaSelection() {
            if (this.selectedAromas.length > 0) {
                this.$emit('update:selectedAromas', []);
                this.$emit('select-aroma', []);
            }
        },
        clearPriceRangeSelection() {
            if (this.selectedPriceRange) {
                this.$emit('update:selectedPriceRange', null);
                this.$emit('select-price-range', null);
            }
        }
    }
};
</script>

<style scoped>
/* Không cần thay đổi phần style */
</style>