<template>
    <div class="bg-gray-100">
        <div class="max-w-[1200px] mx-auto">
            <main class="p-4 md:p-6 flex-grow relative">
                <div
                    class="bg-white p-3 rounded-t-md grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center text-gray-600 font-semibold gap-x-2">
                    <div class="col-span-1"></div>
                    <div class="col-span-1">Sản Phẩm</div>
                    <div class="col-span-1 text-center">Đơn Giá</div>
                    <div class="col-span-1 text-center">Số Lượng</div>
                    <div class="col-span-1 text-center">Số Tiền</div>
                    <div class="col-span-1 text-center">Thao Tác</div>
                </div>


                <div
                    class="bg-white rounded-md shadow-sm mb-3 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-2">
                    <div class="col-span-1">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded" />
                    </div>
                    <div class="flex items-center space-x-3 col-span-1">
                        <div
                            class="w-20 h-20 bg-gray-200 object-cover rounded-md border border-gray-200 flex items-center justify-center text-gray-500">
                            Image</div>
                        <div class="flex-1">
                            <p class="text-gray-800 font-medium text-base">Quạt tản nhiệt điện thoại ipad sò lạnh từ
                                tính Memo CX07 CX08...</p>
                            <div class="flex items-center text-gray-500 text-sm mt-1">
                                <span>Phân Loại Hàng:</span>
                                <select class="border rounded-md ml-2 px-2 py-1 text-sm">
                                    <option>Memo CX15 Pro 5V AI</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="line-through text-gray-400 text-sm block">₫339.000</span>
                        <span class="text-red-500 font-semibold text-lg">₫239.000</span>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <div class="flex items-center border rounded-md">
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md">-</button>
                            <input type="number" value="1"
                                class="w-12 text-center border-l border-r border-gray-200 focus:outline-none" />
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md">+</button>
                        </div>
                    </div>
                    <span class="col-span-1 text-red-500 font-semibold text-lg text-center">₫239.000</span>
                    <div class="col-span-1 text-sm space-y-1 text-center">
                        <button class="text-blue-500 text-base hover:text-red-500 block mx-auto">Xóa</button>
                    </div>
                </div>
                <div
                    class="bg-white rounded-md shadow-sm mb-3 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-2">
                    <div class="col-span-1">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded" />
                    </div>
                    <div class="flex items-center space-x-3 col-span-1">
                        <div
                            class="w-20 h-20 bg-gray-200 object-cover rounded-md border border-gray-200 flex items-center justify-center text-gray-500">
                            Image</div>
                        <div class="flex-1">
                            <p class="text-gray-800 font-medium text-base">Quạt tản nhiệt điện thoại ipad sò lạnh từ
                                tính Memo CX07 CX08...</p>
                            <div class="flex items-center text-gray-500 text-sm mt-1">
                                <span>Phân Loại Hàng:</span>
                                <select class="border rounded-md ml-2 px-2 py-1 text-sm">
                                    <option>Memo CX15 Pro 5V AI</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="line-through text-gray-400 text-sm block">₫339.000</span>
                        <span class="text-red-500 font-semibold text-lg">₫239.000</span>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <div class="flex items-center border rounded-md">
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md">-</button>
                            <input type="number" value="1"
                                class="w-12 text-center border-l border-r border-gray-200 focus:outline-none" />
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md">+</button>
                        </div>
                    </div>
                    <span class="col-span-1 text-red-500 font-semibold text-lg text-center">₫239.000</span>
                    <div class="col-span-1 text-sm space-y-1 text-center">
                        <button class="text-blue-500 text-base hover:text-red-500 block mx-auto">Xóa</button>
                    </div>
                </div>
                <div
                    class="bg-white rounded-md shadow-sm mb-3 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-2">
                    <div class="col-span-1">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded" />
                    </div>
                    <div class="flex items-center space-x-3 col-span-1">
                        <div
                            class="w-20 h-20 bg-gray-200 object-cover rounded-md border border-gray-200 flex items-center justify-center text-gray-500">
                            Image</div>
                        <div class="flex-1">
                            <p class="text-gray-800 font-medium text-base">Quạt tản nhiệt điện thoại ipad sò lạnh từ
                                tính Memo CX07 CX08...</p>
                            <div class="flex items-center text-gray-500 text-sm mt-1">
                                <span>Phân Loại Hàng:</span>
                                <select class="border rounded-md ml-2 px-2 py-1 text-sm">
                                    <option>Memo CX15 Pro 5V AI</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="line-through text-gray-400 text-sm block">₫339.000</span>
                        <span class="text-red-500 font-semibold text-lg">₫239.000</span>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <div class="flex items-center border rounded-md">
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md">-</button>
                            <input type="number" value="1"
                                class="w-12 text-center border-l border-r border-gray-200 focus:outline-none" />
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md">+</button>
                        </div>
                    </div>
                    <span class="col-span-1 text-red-500 font-semibold text-lg text-center">₫239.000</span>
                    <div class="col-span-1 text-sm space-y-1 text-center">
                        <button class="text-blue-500 text-base hover:text-red-500 block mx-auto">Xóa</button>
                    </div>
                </div>
                                <div
                    class="bg-white rounded-md shadow-sm mb-3 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-2">
                    <div class="col-span-1">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded" />
                    </div>
                    <div class="flex items-center space-x-3 col-span-1">
                        <div
                            class="w-20 h-20 bg-gray-200 object-cover rounded-md border border-gray-200 flex items-center justify-center text-gray-500">
                            Image</div>
                        <div class="flex-1">
                            <p class="text-gray-800 font-medium text-base">Quạt tản nhiệt điện thoại ipad sò lạnh từ
                                tính Memo CX07 CX08...</p>
                            <div class="flex items-center text-gray-500 text-sm mt-1">
                                <span>Phân Loại Hàng:</span>
                                <select class="border rounded-md ml-2 px-2 py-1 text-sm">
                                    <option>Memo CX15 Pro 5V AI</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="line-through text-gray-400 text-sm block">₫339.000</span>
                        <span class="text-red-500 font-semibold text-lg">₫239.000</span>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <div class="flex items-center border rounded-md">
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md">-</button>
                            <input type="number" value="1"
                                class="w-12 text-center border-l border-r border-gray-200 focus:outline-none" />
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md">+</button>
                        </div>
                    </div>
                    <span class="col-span-1 text-red-500 font-semibold text-lg text-center">₫239.000</span>
                    <div class="col-span-1 text-sm space-y-1 text-center">
                        <button class="text-blue-500 text-base hover:text-red-500 block mx-auto">Xóa</button>
                    </div>
                </div>
                                <div
                    class="bg-white rounded-md shadow-sm mb-3 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-2">
                    <div class="col-span-1">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded" />
                    </div>
                    <div class="flex items-center space-x-3 col-span-1">
                        <div
                            class="w-20 h-20 bg-gray-200 object-cover rounded-md border border-gray-200 flex items-center justify-center text-gray-500">
                            Image</div>
                        <div class="flex-1">
                            <p class="text-gray-800 font-medium text-base">Quạt tản nhiệt điện thoại ipad sò lạnh từ
                                tính Memo CX07 CX08...</p>
                            <div class="flex items-center text-gray-500 text-sm mt-1">
                                <span>Phân Loại Hàng:</span>
                                <select class="border rounded-md ml-2 px-2 py-1 text-sm">
                                    <option>Memo CX15 Pro 5V AI</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="line-through text-gray-400 text-sm block">₫339.000</span>
                        <span class="text-red-500 font-semibold text-lg">₫239.000</span>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <div class="flex items-center border rounded-md">
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md">-</button>
                            <input type="number" value="1"
                                class="w-12 text-center border-l border-r border-gray-200 focus:outline-none" />
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md">+</button>
                        </div>
                    </div>
                    <span class="col-span-1 text-red-500 font-semibold text-lg text-center">₫239.000</span>
                    <div class="col-span-1 text-sm space-y-1 text-center">
                        <button class="text-blue-500 text-base hover:text-red-500 block mx-auto">Xóa</button>
                    </div>
                </div>
                <div
                    class="bg-white rounded-md shadow-sm mb-3 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-2">
                    <div class="col-span-1">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded" />
                    </div>
                    <div class="flex items-center space-x-3 col-span-1">
                        <div
                            class="w-20 h-20 bg-gray-200 object-cover rounded-md border border-gray-200 flex items-center justify-center text-gray-500">
                            Image</div>
                        <div class="flex-1">
                            <p class="text-gray-800 font-medium text-base">Miếng tản nhiệt cao cấp Thermal Pad SNOWMAN
                                16.8 W/mk...</p>
                            <div class="flex items-center text-gray-500 text-sm mt-1">
                                <span>Phân Loại Hàng:</span>
                                <select class="border rounded-md ml-2 px-2 py-1 text-sm">
                                    <option>SNOWMAN 13.8 Wmk.85 x 45 x 0.5mm</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="line-through text-gray-400 text-sm block">₫199.000</span>
                        <span class="text-red-500 font-semibold text-lg">₫93.000</span>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <div class="flex items-center border rounded-md">
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md">-</button>
                            <input type="number" value="2"
                                class="w-12 text-center border-l border-r border-gray-200 focus:outline-none" />
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md">+</button>
                        </div>
                    </div>
                    <span class="col-span-1 text-red-500 font-semibold text-lg text-center">₫186.000</span>
                    <div class="col-span-1 text-sm space-y-1 text-center">
                        <button class="text-blue-500 text-base hover:text-red-500 block mx-auto">Xóa</button>
                    </div>
                </div>

                <div
                    class="bg-white rounded-md shadow-sm mb-3 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-2">
                    <div class="col-span-1">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded" />
                    </div>
                    <div class="flex items-center space-x-3 col-span-1">
                        <div
                            class="w-20 h-20 bg-gray-200 object-cover rounded-md border border-gray-200 flex items-center justify-center text-gray-500">
                            Image</div>
                        <div class="flex-1">
                            <p class="text-gray-800 font-medium text-base">Miếng tản nhiệt cao cấp Thermal Pad SNOWMAN
                                16.8 W/mk...</p>
                            <div class="flex items-center text-gray-500 text-sm mt-1">
                                <span>Phân Loại Hàng:</span>
                                <select class="border rounded-md ml-2 px-2 py-1 text-sm">
                                    <option>SNOWMAN 13.8 Wmk.85 x 45 x 1.5mm</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="line-through text-gray-400 text-sm block">₫139.000</span>
                        <span class="text-red-500 font-semibold text-lg">₫130.000</span>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <div class="flex items-center border rounded-md">
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md">-</button>
                            <input type="number" value="1"
                                class="w-12 text-center border-l border-r border-gray-200 focus:outline-none" />
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md">+</button>
                        </div>
                    </div>
                    <span class="col-span-1 text-red-500 font-semibold text-lg text-center">₫130.000</span>
                    <div class="col-span-1 text-sm space-y-1 text-center">
                        <button class="text-blue-500 text-base hover:text-red-500 block mx-auto">Xóa</button>
                    </div>
                </div>

                <div class="flex justify-end items-center py-3 pr-4 mb-4">
                    <div
                        class="h-5 w-5 bg-gray-300 rounded-full mr-2 flex items-center justify-center text-xs text-gray-600">
                        V</div>
                    <span class="text-gray-700 font-medium mr-4">Shopee Voucher</span>
                    <button class="text-blue-500 hover:underline">Chọn hoặc nhập mã</button>
                </div>

                <footer
                    class="bg-white p-4 flex justify-between items-center rounded-b-md shadow-md sticky bottom-0 z-10">
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded" />
                            <span class="ml-2 text-gray-700">Chọn Tất Cả (16)</span>
                        </label>
                        <button class="text-blue-500 hover:underline">Xóa</button>
                        <button class="text-blue-500 hover:underline">Bỏ sản phẩm không hoạt động</button>
                    </div>
                    <div class="flex items-center">
                        <div class="flex items-center mr-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 dark:peer-focus:ring-orange-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-orange-600">
                                </div>
                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Shopee Xu</span>
                            </label>
                            <span class="ms-2 text-gray-500 text-sm">Bạn chưa chọn sản phẩm ⓘ</span>
                        </div>
                        <span class="text-gray-700 font-medium">Tổng cộng (0 Sản phẩm):</span>
                        <span class="text-red-500 font-bold text-xl ml-2">₫0</span>
                        <button
                            class="bg-red-500 text-white font-semibold py-3 px-6 rounded-md ml-4 hover:bg-red-600">Mua
                            Hàng</button>
                    </div>
                </footer>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'; // Import onMounted và onUnmounted

</script>

<style scoped>
@import '@/assets/tailwind.css';

/* Any component-specific styles if needed, though Tailwind aims to minimize this. */
</style>