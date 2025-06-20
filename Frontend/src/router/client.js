import { createRouter, createWebHistory } from "vue-router";

import ClientLayout from "@/layouts/ClientLayout.vue";
import Home from "@/views/client/Home.vue";
import NuocHoaPage from "@/views/client/NuocHoaPage.vue";
import NuocHoaNamPage from "@/views/client/NuocHoaNamPage.vue";
import NuocHoaNuPage from "@/views/client/NuocHoaNuPage.vue";
import ThuongHieuPage from "@/views/client/ThuongHieuPage.vue";
import NuocHoaMiniPage from "@/views/client/NuocHoaMiniPage.vue";
import NuocHoaChietPage from "@/views/client/NuocHoaChietPage.vue";
import NuocHoaNichePage from "@/views/client/NuocHoaNichePage.vue";
import NuocHoaUnisexPage from "@/views/client/NuocHoaUnisexPage.vue";
import Cart from "@/views/client/Cart.vue";
import Register from "@/views/client/account/Register.vue";
import Login from "@/views/client/account/Login.vue";

//-----User--------
import UserLayout from "@/layouts/UserLayout.vue";
import Profile from "@/views/client/user/Profile.vue";
import Address from "@/views/client/user/Address.vue";
import Bank from "@/views/client/user/Bank.vue";
import Orders from "@/views/client/user/Orders.vue";
import CategoryProducts from "@/views/client/CategoryProducts.vue";
import ProductDetail from "@/views/client/Product.vue";

const routes = [
  {
    path: "/",
    component: ClientLayout,
    children: [
      {
        path: "",
        name: "Home",
        component: Home,
      },
      {
        path: "nuoc-hoa",
        name: "NuocHoa",
        component: NuocHoaPage,
      },
      {
        path: "nuoc-hoa-nam",
        name: "NuocHoaNam",
        component: NuocHoaNamPage,
      },
      {
        path: "nuoc-hoa-nu",
        name: "NuocHoaNu",
        component: NuocHoaNuPage,
      },
      {
        path: "thuong-hieu",
        name: "ThuongHieu",
        component: ThuongHieuPage,
      },
      {
        path: "nuoc-hoa-mini",
        name: "NuocHoaMini",
        component: NuocHoaMiniPage,
      },
      {
        path: "nuoc-hoa-chiet",
        name: "NuocHoaChiet",
        component: NuocHoaChietPage,
      },
      {
        path: "nuoc-hoa-unisex",
        name: "NuocHoaUnisex",
        component: NuocHoaUnisexPage,
      },
      {
        path: "nuoc-hoa-niche",
        name: "NuocHoaNiche",
        component: NuocHoaNichePage,
      },
      {
        path: "gio-hang",
        name: "GioHang",
        component: Cart,
      },
      {
        path: "dang-ky",
        name: "DangKy",
        component: Register,
      },
      {
        path: "dang-nhap",
        name: "DangNhap",
        component: Login,
      },
      {
        path: "/category/:categorySlug",
        name: "CategoryProducts",
        component: CategoryProducts,
        props: true,
      },
      {
        path: "/san-pham/:slug",
        name: "ProductDetail",
        component: ProductDetail,
        props: true,
      },

      //----User Path-----
      {
        path: "/tai-khoan",
        name: "User",
        component: UserLayout,
        redirect: { name: "Profile" },
        children: [
          {
            path: "ho-so", // Path will be /user/profile
            name: "Profile",
            component: Profile, // A component for detailed profile
          },
          {
            path: "dia-chi", // Path will be /user/profile
            name: "Address",
            component: Address, // A component for detailed profile
          },
          {
            path: "ngan-hang", // Path will be /user/profile
            name: "Bank",
            component: Bank, // A component for detailed profile
          },
          {
            path: "don-hang", // Path will be /user/profile
            name: "Orders",
            component: Orders, // A component for detailed profile
          },
        ],
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default routes; // Export instance router đã được tạo