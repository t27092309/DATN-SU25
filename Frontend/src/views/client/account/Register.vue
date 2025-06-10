<template>
    <div class="min-h-screen bg-red-500 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6 relative">
                <h2 class="text-2xl font-bold text-gray-800">Đăng ký</h2>
                <div class="absolute right-0 top-0 -mr-6 -mt-6">
                </div>
            </div>

            <div class="mb-4">
                <input type="text" placeholder="Số điện thoại/Tên đăng nhập"
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-400" />
            </div>

            <div class="mb-4">
                <input type="email" placeholder="Email đăng ký"
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-400" />
            </div>

            <div class="mb-6 relative">
                <input :type="isPasswordVisible ? 'text' : 'password'" placeholder="Mật khẩu"
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-400 pr-10" />
                <span @click="togglePasswordVisibility"
                    class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <svg v-if="isPasswordVisible" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M3.707 2.293a1 1 0 00-1.414 1.414L5 7.414V8a1 1 0 001 1h1a1 1 0 001-1V7.414l2.293 2.293A1 1 0 0011 9v-.586l.293.293a1 1 0 001.414 0L17.707 5.707a1 1 0 00-1.414-1.414L14 7.586V7a1 1 0 00-1-1h-1a1 1 0 00-1 1v.586L9 9.586V9a1 1 0 00-1-1H7a1 1 0 00-1 1v.586l-2.293-2.293a1 1 0 00-1.414 0z"
                            clip-rule="evenodd"></path>
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 00-.894.553L7.382 7.71A1 1 0 008 9h4a1 1 0 00.618-1.29l-1.724-3.157A1 1 0 0010 3zm-2 5a2 2 0 100 4 2 2 0 000-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd"
                            d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                </span>
            </div>

            <button
                class="w-full bg-red-500 text-white py-3 rounded-md font-semibold text-lg hover:bg-red-600 transition duration-200">
                ĐĂNG KÝ
            </button>


            <div class="flex items-center mb-6 mt-6">
                <hr class="flex-grow border-gray-300" />
                <span class="mx-4 text-gray-500 text-sm">HOẶC</span>
                <hr class="flex-grow border-gray-300" />
            </div>

            <div class="flex space-x-4 mb-8">
                <button
                    class="flex-1 flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/2021_Facebook_icon.svg/512px-2021_Facebook_icon.svg.png"
                        alt="Facebook" class="h-5 w-5 mr-2" />
                    Facebook
                </button>
                <button
                    class="flex-1 flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/512px-Google_%22G%22_logo.svg.png"
                        alt="Google" class="h-5 w-5 mr-2" />
                    Google
                </button>
            </div>
            <div class="text-center text-sm text-gray-600 mb-6">
                Bằng việc đăng kí, bạn đã đồng ý với Shopee về Điều khoản dịch vụ & Chính sách bảo mật
            </div>

            <div class="text-center text-sm text-gray-600">
                Bạn đã có tài khoản?
                <router-link to="/dang-nhap" class="text-red-500 hover:underline font-semibold">Đăng nhập</router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const isPasswordVisible = ref(false);

const togglePasswordVisibility = () => {
    isPasswordVisible.value = !isPasswordVisible.value;
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Any custom styles if needed, but Tailwind should handle most of it */
</style>