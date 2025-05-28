import AdminLayout from "@/layouts/AdminLayout.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Datatables from "@/views/admin/Datatables.vue";

export default [
    {
      path: "/admin",
      component: AdminLayout, // Layout cho Admin
      children: [
        {
          path: "",
          name: "AdminDashboard",
          component: AdminDashboard,
        },
        {
          path: "datatables",
          name: "Datatables",
          component: Datatables,
        },
      ],
    }
]
