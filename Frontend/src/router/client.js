import { createRouter, createWebHistory } from "vue-router";

import ClientLayout from "@/layouts/ClientLayout.vue";
import Home from "@/views/client/Home.vue";
import NuocHoaPage from "@/views/client/NuocHoaPage.vue";
import ThuongHieuPage from "@/views/client/ThuongHieuPage.vue";
import Cart from "@/views/client/Cart.vue";
import Register from "@/views/client/account/Register.vue";
import Login from "@/views/client/account/Login.vue";
import Checkout from "@/views/client/Checkout.vue";
import OrderConfirmation from "@/views/client/order/OrderConfirmation.vue";
import SearchResult from "@/views/client/SearchResult.vue";
//-----User--------
import UserLayout from "@/layouts/UserLayout.vue";
import Profile from "@/views/client/user/Profile.vue";
import Address from "@/views/client/user/Address.vue";
import Bank from "@/views/client/user/Bank.vue";
import Orders from "@/views/client/user/Orders.vue";
import CategoryProducts from "@/views/client/CategoryProducts.vue";
import ProductDetail from "@/views/client/Product.vue";
import OrderDetail from "@/views/client/order/OrderDetail.vue";

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
        path: "thuong-hieu",
        name: "ThuongHieu",
        component: ThuongHieuPage,
      },
      {
        path: "gio-hang",
        name: "GioHang",
        component: Cart,
      },
      {
        path: "thanh-toan",
        name: "ThanhToan",
        component: Checkout,
      },
      {
        path: "dat-hang-thanh-cong/:ma_don_hang",
        name: "DatHangThanhCong",
        component: OrderConfirmation,
        props: true,
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
      {
        path: "/search/:keyword",
        name: "SearchResult",
        component: SearchResult,
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
            path: "ho-so",
            name: "Profile",
            component: Profile,
          },
          {
            path: "dia-chi",
            name: "Address",
            component: Address,
          },
          {
            path: "ngan-hang",
            name: "Bank",
            component: Bank,
          },
          {
            path: "don-hang",
            name: "Orders",
            component: Orders,
          },
          {
            path: "chi-tiet-don-hang/:idDonHang",
            name: "OrderDetail",
            component: OrderDetail,
            props: true,
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