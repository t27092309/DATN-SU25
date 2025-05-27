<template>
  <div class="not-found">
    <div class="content">
      <h1>404</h1>
      <p>Trang bạn tìm không tồn tại.</p>
      <router-link to="/admin" class="home-link">Quay về trang chủ</router-link>
    </div>
  </div>
</template>

<script setup>
// Không cần logic JS cho trang này
</script>

<style scoped>
.not-found {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: #f5f5f5;
}

.content {
  text-align: center;
}

h1 {
  font-size: 6rem;
  margin-bottom: 1rem;
  color: #e74c3c;
}

p {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
}

.home-link {
  display: inline-block;
  padding: 10px 20px;
  background-color: #3498db;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  transition: 0.3s;
}

.home-link:hover {
  background-color: #2980b9;
}
</style>
