import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import AdminLayout from "@/layouts/AdminLayout.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Datatables from "@/views/admin/Datatables.vue";
import adminRoutes from './admin';
// import clientRoutes from './client'
import NotFound from "@/views/errors/NotFound.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
  // ...clientRoutes,
  ...adminRoutes,
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound
  }

  ],
});

export default router;
