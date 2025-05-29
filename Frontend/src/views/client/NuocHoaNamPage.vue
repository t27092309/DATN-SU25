<template>
  <div class="container mx-auto p-4">
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <button class="p-2 rounded-full bg-gray-200 hover:bg-gray-300">
          <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <div class="flex-1 overflow-hidden">
          <div class="flex whitespace-nowrap scrollbar-hide space-x-8">
            <div v-for="brand in brands" :key="brand.name" class="flex-shrink-0 text-lg font-bold uppercase cursor-pointer px-4 py-2 hover:text-gray-700 transition-colors duration-200">
              {{ brand.name }}
            </div>
          </div>
        </div>
        <button class="p-2 rounded-full bg-gray-200 hover:bg-gray-300">
          <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
      </div>
       <div class="relative w-full h-2 bg-gray-300 rounded-full mt-4">
          <div class="absolute h-full bg-gray-500 rounded-full" :style="{ width: '60%', left: '20%' }"></div>
        </div>
    </div>

    <div class="flex space-x-4 items-center">
      <div class="relative inline-block text-left">
        <button
          @click="toggleDropdown"
          class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          id="menu-button"
          aria-expanded="true"
          aria-haspopup="true"
        >
          Nhóm Hương
          <svg class="ml-2 -mr-1 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>

        <div
          v-if="isDropdownOpen"
          class="origin-top-left absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
          role="menu"
          aria-orientation="vertical"
          aria-labelledby="menu-button"
          tabindex="-1"
        >
          <div class="py-1" role="none">
            <a href="#" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1" id="menu-item-0">Option 1</a>
            <a href="#" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1" id="menu-item-1">Option 2</a>
            <a href="#" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1" id="menu-item-2">Option 3</a>
          </div>
        </div>
      </div>

      <div class="flex space-x-2">
        <button
          v-for="priceRange in priceRanges"
          :key="priceRange.value"
          @click="selectPriceRange(priceRange.value)"
          :class="{
            'bg-blue-600 text-white': selectedPriceRange === priceRange.value,
            'bg-gray-100 text-gray-700 hover:bg-gray-200': selectedPriceRange !== priceRange.value,
          }"
          class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
        >
          {{ priceRange.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductFilters',
  data() {
    return {
      brands: [
        { name: 'Dior' },
        { name: 'Chanel' },
        { name: 'Versace' },
        { name: 'Gucci' },
        { name: 'Giorgio Armani' },
        { name: 'Yves Saint Laurent' },
        { name: 'Lancome' },
        { name: 'Tom Ford' },
        { name: 'Narciso Rodriguez' },
        { name: 'Dolce & Gabbana' },
        { name: 'Jean Paul Gaultier' },
      ],
      priceRanges: [
        { label: 'Dưới 2 Triệu', value: 'under_2' },
        { label: '2 - 4 Triệu', value: '2_4' },
        { label: 'Trên 4 Triệu', value: 'over_4' },
      ],
      selectedPriceRange: null,
      isDropdownOpen: false,
    };
  },
  methods: {
    selectPriceRange(range) {
      this.selectedPriceRange = range;
      // You would typically emit an event or call a method to filter products here
      console.log('Selected Price Range:', this.selectedPriceRange);
    },
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },
    closeDropdown(event) {
      if (!this.$el.contains(event.target)) {
        this.isDropdownOpen = false;
      }
    }
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeDropdown);
  }
};
</script>

<style scoped>
/* Custom scrollbar hide for the brand carousel if needed, or use a library */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>