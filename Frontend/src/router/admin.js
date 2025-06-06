import AdminLayout from "@/layouts/AdminLayout.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Attribute from "@/views/admin/Attribute.vue";
import Category from "@/views/admin/Category.vue";
import CouponManager from "@/views/admin/CouponManager.vue";
import Datatables from "@/views/admin/Datatables.vue";
import EditCategory from "@/views/admin/EditCategory.vue";
import EditCoupon from "@/views/admin/EditCoupon.vue";

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
      {
        path: "/categories/edit/:id",
        name: "EditCategory",
        component: EditCategory,
      },
      {
        path: "ma-giam-gia",
        name: "ma-giam-gia",
        component: CouponManager,
      },
      {
        path: "/coupons/edit/:id",
        name: "EditCoupons",
        component: EditCoupon,
      },
    ],
  },
];
