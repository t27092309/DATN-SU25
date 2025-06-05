import AdminLayout from "@/layouts/AdminLayout.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Attribute from "@/views/admin/Attribute.vue";
import Category from "@/views/admin/Category.vue";
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
        {
          path: "cac-thuoc-tinh",
          name: "attribute",
          component: Attribute,
        },
                {
          path: "danh-muc",
          name: "category",
          component: Category,
        },
      ],
    }
]
