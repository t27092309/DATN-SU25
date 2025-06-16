<template>
    <div class="container mx-auto p-4 flex flex-col lg:flex-row gap-8">
        <div class="flex-1 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-6">Chi tiết về sản phẩm</h2>

            <div class="flex flex-col gap-4 text-gray-700">
                <div class="flex items-center">
                    <span class="font-medium w-32 shrink-0">Phân loại:</span>
                    <span>Nước hoa</span>
                </div>
                <div class="flex items-center">
                    <span class="font-medium w-32 shrink-0">Xuất xứ:</span>
                    <span>Mỹ</span>
                </div>
                <div class="flex items-center">
                    <span class="font-medium w-32 shrink-0">Năm phát hành:</span>
                    <span>2006</span>
                </div>
                <div class="flex items-center">
                    <span class="font-medium w-32 shrink-0">Nồng độ:</span>
                    <span>Eau de Parfum (EDP)</span>
                </div>
                <div class="flex items-center">
                    <span class="font-medium w-32 shrink-0">Nhóm hương:</span>
                    <span>Hoa, Gỗ, Xạ Hương</span>
                </div>
                <div class="flex items-center">
                    <span class="font-medium w-32 shrink-0">Nhà chế tác:</span>
                    <span>Christine Nagel</span>
                </div>
                <div class="flex items-start col-span-1 md:col-span-2">
                    <span class="font-medium w-32 shrink-0 mt-1">Phong cách:</span>
                    <span>Ấm áp (Cozy), Lãng mạn (Romantic), Nữ tính (Feminine), Thanh lịch (Elegant), Thoải mái
                        (Casual), Tươi mát (Fresh)</span>
                </div>
                <div class="flex items-center col-span-1 md:col-span-2">
                    <span class="font-medium w-32 shrink-0">Dịp sử dụng:</span>
                    <span>Hàng ngày, Văn phòng, Mùa đông, Mùa hè, Du lịch</span>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-80 bg-white p-6 rounded-lg shadow-md flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <protect class="size-fit text-gray-500"/>
                <div>
                    <p class="font-medium text-gray-800">Cam kết chính hãng 100%</p>
                    <p class="text-sm text-gray-500">Tất cả nước hoa &amp; mỹ phẩm</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <moneyback class="size-fit text-gray-500"/>
                <div>
                    <p class="font-medium text-gray-800">Bảo hành đến giọt cuối cùng</p>
                    <p class="text-sm text-gray-500">Miễn phí đổi trả trong 7 ngày</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <shippingvan class="size-fit text-gray-500" />
                <div>
                    <p class="font-medium text-gray-800">Giao hàng miễn phí toàn quốc</p>
                    <p class="text-sm text-gray-500">Miễn phí thu thập &amp; gửi quà</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <vnpay class="w-8 h-8 size-fit text-gray-500" />

                <div>
                    <p class="font-medium text-gray-800">Thanh toán online an toàn</p>
                    <p class="text-sm text-gray-500">Trả góp 0% qua Fundiin</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import moneyback from '@/assets/images/icons/icon-money-back.svg'
import protect from '@/assets/images/icons/icon-protect.svg'
import shippingvan from '@/assets/images/icons/icon-truck.svg'
import vnpay from '@/assets/images/icons/vnpay_new.svg'
// No script logic needed for this static display component
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* You can add custom styles here if Tailwind defaults aren't enough,
   but for this layout, Tailwind should suffice. */
</style>