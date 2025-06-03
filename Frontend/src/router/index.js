import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Datatables from "@/views/admin/Datatables.vue";
import adminRoutes from "./admin";
import clientRoutes from "./client";
import NotFound from "@/views/errors/NotFound.vue";

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
});

router.afterEach((to) => {
    const title = to.meta.title || "Trang mặc định";
    document.title = title;
});

export default router;
