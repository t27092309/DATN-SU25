<template>
  <div class="user-bank">
    <div class="mb-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-800">Thẻ Tín Dụng/Ghi Nợ</h3>
        <button class="px-4 py-2 bg-red-500 text-white rounded-md flex items-center hover:bg-red-600">
          <i class="fas fa-plus mr-2"></i> Thêm Thẻ Mới
        </button>
      </div>
      <div class="text-center py-8 text-gray-500 border border-gray-200 rounded-lg bg-white shadow-sm">
        <p>Bạn chưa liên kết thẻ.</p>
      </div>
    </div>

    <div class="mb-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-800">Thẻ Tín Dụng/Ghi Nợ</h3>
        <button class="px-4 py-2 bg-red-500 text-white rounded-md flex items-center hover:bg-red-600">
          <i class="fas fa-plus mr-2"></i> Thêm Thẻ Mới
        </button>
      </div>
      <div class="text-center py-8 text-gray-500 border border-gray-200 rounded-lg bg-white shadow-sm">
        <p>Bạn chưa liên kết thẻ.</p>
      </div>
    </div>

    <div class="mb-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-800">Tài Khoản Ngân Hàng Của Tôi</h3>
        <button class="px-4 py-2 bg-red-500 text-white rounded-md flex items-center hover:bg-red-600">
          <i class="fas fa-plus mr-2"></i> Thêm Ngân Hàng Liên Kết
        </button>
      </div>
      <div class="text-center py-8 text-gray-500 border border-gray-200 rounded-lg bg-white shadow-sm">
        <p>Bạn chưa liên kết tài khoản ngân hàng nào.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
// Trong một ứng dụng thực tế, bạn sẽ có các props để nhận dữ liệu từ bên ngoài
// và các hàm để xử lý sự kiện (thêm thẻ/tài khoản ngân hàng, xóa, cập nhật, v.v.).

// import { ref, onMounted } from 'vue';

// const creditCards = ref([]); // Ví dụ: dữ liệu thẻ tín dụng/ghi nợ
// const bankAccounts = ref([]); // Ví dụ: dữ liệu tài khoản ngân hàng

// onMounted(() => {
//   // Load dữ liệu thẻ và tài khoản ngân hàng từ API khi component được mount
//   // Ví dụ:
//   // fetchCreditCards().then(data => {
//   //   creditCards.value = data;
//   // });
//   // fetchBankAccounts().then(data => {
//   //   bankAccounts.value = data;
//   // });
// });

// // Các hàm xử lý sự kiện (ví dụ)
// const addCreditCard = () => {
//   console.log('Thêm thẻ tín dụng/ghi nợ mới');
//   // Logic để hiển thị form thêm thẻ
// };

// const addBankAccount = () => {
//   console.log('Thêm tài khoản ngân hàng liên kết');
//   // Logic để hiển thị form thêm tài khoản ngân hàng
// };

// // Nếu có dữ liệu, bạn sẽ dùng v-for để lặp qua chúng
// // Ví dụ về cấu trúc dữ liệu nếu có
// /*
// creditCards.value = [
//   { id: 1, last4Digits: '1234', bank: 'Vietcombank', type: 'Visa' },
//   // ...
// ];
// bankAccounts.value = [
//   { id: 1, bankName: 'Techcombank', accountNumber: '123456789', accountHolder: 'Nguyen Van A' },
//   // ...
// ];
// */
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Các style riêng cho component này */
</style>