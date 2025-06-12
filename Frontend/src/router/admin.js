import AdminLayout from "@/layouts/AdminLayout.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Attribute from "@/views/admin/Attribute.vue";
import CategoryManager from "@/views/admin/Categories/CategoryManager.vue";
import CouponManager from "@/views/admin/Coupons/CouponManager.vue";
import Datatables from "@/views/admin/Datatables.vue";
import EditCategory from "@/views/admin/Categories/EditCategory.vue";
import EditCoupon from "@/views/admin/Coupons/EditCoupon.vue";
import ScentGroupManager from "@/views/admin/ScentGroups/ScentGroupManager.vue";
import EditScentGroup from "@/views/admin/ScentGroups/EditScentGroup.vue";

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
        component: CategoryManager,
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
      {
        path: "nhom-huong",
        name: "nhom-huong",
        component: ScentGroupManager,
      },
      {
        path: "/scent-group/edit/:id",
        name: "EditScentGroup",
        component: EditScentGroup,
      },
    ],
  },
];
