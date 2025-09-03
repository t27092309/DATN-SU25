import { createRouter, createWebHistory } from "vue-router";
import adminRoutes from "./admin";
import clientRoutes from "./client";
import NotFound from "@/views/errors/NotFound.vue";
import { useAuthStore } from "@/stores/auth";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    ...adminRoutes,
    ...clientRoutes,
    {
      path: "/:pathMatch(.*)*",
      name: "NotFound",
      component: NotFound,
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0, behavior: "smooth" };
    }
  },
});
// Navigation guard để kiểm tra quyền truy cập admin
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const user = authStore.user;

  // Kiểm tra nếu đang truy cập trang admin
  if (to.path.startsWith("/admin")) {
    // Nếu chưa đăng nhập, chuyển về trang login
    if (!user) {
      next('/login');
      return;
    }

    // Nếu user có role 'user', không cho phép truy cập admin
    if (user.role === 'user') {
      next('/');
      return;
    }

    // Chỉ cho phép admin và staff truy cập
    if (user.role === 'admin' || user.role === 'staff') {
      next();
      return;
    }

    // Các trường hợp khác, chuyển về trang chủ
    next('/');
    return;
  }

  // Kiểm tra nếu đang truy cập trang user (không phải admin)
  if (!to.path.startsWith("/admin") && to.path !== "/login" && to.path !== "/register") {
    // Nếu user có role admin hoặc staff, không cho phép truy cập trang user
    if (user && (user.role === 'admin' || user.role === 'staff')) {
      next('/admin/dashboard');
      return;
    }
  }

  next();
});

router.afterEach((to) => {
  if (to.path.startsWith("/admin")) {
    document.title = to.meta.title || "Trang Admin";
  }
});

export default router;
