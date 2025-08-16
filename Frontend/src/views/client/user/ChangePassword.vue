<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Đổi mật khẩu</h2>
    <form @submit.prevent="handleChangePassword">
      <!-- Mật khẩu hiện tại -->
      <div class="mb-4 relative">
        <label>Mật khẩu hiện tại</label>
        <input
          v-model="currentPassword"
          :type="showCurrent ? 'text' : 'password'"
          class="border rounded w-full p-2 pr-10"
        />
        <span
          class="absolute right-3 top-9 cursor-pointer select-none"
          @click="showCurrent = !showCurrent"
        >
          {{ showCurrent ? "🙈" : "👁" }}
        </span>
      </div>

      <!-- Mật khẩu mới -->
      <div class="mb-4 relative">
        <label>Mật khẩu mới</label>
        <input
          v-model="newPassword"
          :type="showNew ? 'text' : 'password'"
          class="border rounded w-full p-2 pr-10"
        />
        <span
          class="absolute right-3 top-9 cursor-pointer select-none"
          @click="showNew = !showNew"
        >
          {{ showNew ? "🙈" : "👁" }}
        </span>
      </div>

      <!-- Xác nhận mật khẩu mới -->
      <div class="mb-4 relative">
        <label>Xác nhận mật khẩu mới</label>
        <input
          v-model="newPasswordConfirmation"
          :type="showConfirm ? 'text' : 'password'"
          class="border rounded w-full p-2 pr-10"
        />
        <span
          class="absolute right-3 top-9 cursor-pointer select-none"
          @click="showConfirm = !showConfirm"
        >
          {{ showConfirm ? "🙈" : "👁" }}
        </span>
      </div>

      <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">
        Xác nhận đổi mật khẩu
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

const currentPassword = ref("");
const newPassword = ref("");
const newPasswordConfirmation = ref("");

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const handleChangePassword = async () => {
  if (newPassword.value !== newPasswordConfirmation.value) {
    alert("Mật khẩu mới không khớp!");
    return;
  }

  try {
    await axios.post("/user/change-password", {
      current_password: currentPassword.value.trim(),
      new_password: newPassword.value.trim(),
      new_password_confirmation: newPasswordConfirmation.value.trim(),
    });
    alert("Đổi mật khẩu thành công. Vui lòng kiểm tra email.");
  } catch (err) {
    alert(err.response?.data?.message || "Lỗi đổi mật khẩu");
  }
};
</script>
