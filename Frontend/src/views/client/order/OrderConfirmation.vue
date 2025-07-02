<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
        <div class="bg-white-100 p-8 max-w-3xl w-full text-center flex flex-col items-center">
            <div class="relative mb-6">
                <div class="w-60 h-60 bg-gray-50 rounded-full flex items-center justify-center border-4 border-gray-50">
                    <svg class="w-full text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 7L12 3L4 7L12 11L20 7Z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 12V19L12 23L20 19V12"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11L12 23"></path>
                    </svg>
                </div>
                <div
                    class="absolute bottom-0 right-0 transform translate-x-1/4 translate-y-1/4 bg-green-500 rounded-full p-1.5 border-4 border-white">
                    <svg class="w-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-gray-700 mb-2">
                Đặt hàng thành công!
            </h1>

            <p class="text-gray-600 text-base mb-8 leading-relaxed">
                <span class="mr-1">🔔</span> Cảm ơn bạn đã đặt hàng, bộ phận chăm sóc
                khách hàng của chúng tôi sẽ liên hệ với bạn trong vòng 24h để xác nhận,
                hãy để ý điện thoại bạn nhé!
            </p>

            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 w-full justify-center">
                <router-link to="/">
                    <button
                        class="flex items-center justify-center px-6 py-3 border border-gray-300 rounded-full text-gray-700 font-medium hover:bg-gray-50 transition duration-150 ease-in-out">
                        Quay về trang chủ
                        <span class="ml-2">↗</span>
                    </button>
                </router-link>
                <router-link to=""></router-link>
                <button
                    class="flex items-center justify-center px-6 py-3 border border-gray-300 rounded-full text-gray-700 font-medium hover:bg-gray-50 transition duration-150 ease-in-out">
                    Xem sản phẩm khác
                    <span class="ml-2">↗</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "OrderConfirmation",
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>