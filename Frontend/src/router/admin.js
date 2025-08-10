import AdminLayout from "@/layouts/admin/AdminMaster.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Datatables from "@/views/admin/Datatables.vue";

//================= Products ============================
import Products from "@/views/admin/products/ProductsList.vue";
import AddProduct from "@/views/admin/products/AddProduct.vue";
import EditProduct from "@/views/admin/products/EditProduct.vue";
import detailProduct from "@/views/admin/products/DetailProduct.vue";
import ProductTrash from "@/views/admin/products/ProductTrash.vue";

import AttributeManagementPage from "@/views/admin/Attribute/AttributeManagementPage.vue";

import CategoryManager from "@/views/admin/Categories/CategoryManager.vue";
import EditCategory from "@/views/admin/Categories/EditCategory.vue";

import CouponManager from "@/views/admin/Coupons/CouponManager.vue";
import EditCoupon from "@/views/admin/Coupons/EditCoupon.vue";

import ScentGroupManager from "@/views/admin/ScentGroups/ScentGroupManager.vue";
import EditScentGroup from "@/views/admin/ScentGroups/EditScentGroup.vue";
import ScentGroupTrash from "@/views/admin/ScentGroups/ScentGroupTrash.vue";

import BrandList from "@/views/admin/Brands/BrandList.vue";
import BrandAdd from "@/views/admin/Brands/BrandAdd.vue";
import BrandEdit from "@/views/admin/Brands/BrandEdit.vue";
import BrandTrash from "@/views/admin/Brands/BrandTrash.vue";
import CouponTrash from "@/views/admin/Coupons/CouponTrash.vue";

import OrderList from "@/views/admin/Orders/OrderList.vue";
import ShippingMethodList from "@/views/admin/ShippingMethod/ShippingMethodList.vue";

export default [
  {
    path: "/admin",
    component: AdminLayout, // Layout cho Admin
    children: [
      {
        path: "",
        name: "AdminDashboard",
        component: AdminDashboard,
        meta: { requiresAdmin: true, title: "Trang quản trị" }, // Thêm meta
      },
      {
        path: "datatables",
        name: "Datatables",
        component: Datatables,
        meta: { requiresAdmin: true, title: "Datatables" }, // Thêm meta
      },
      {
        path: "products",
        name: "products",
        component: Products,
        meta: {
          requiresAdmin: true, // Thêm meta
          title: "Danh sách sản phẩm",
        },
      },
      {
        path: "add-product",
        name: "addProduct",
        component: AddProduct,
        meta: {
          requiresAdmin: true, // Thêm meta
          title: "Thêm mới sản phẩm",
        },
      },
      {
        path: "edit-product/:id",
        name: "editProduct",
        component: EditProduct,
        meta: {
          requiresAdmin: true, // Thêm meta
          title: "Sửa sản phẩm",
        },
      },
      {
        path: "detail-product/:id",
        name: "detailProduct",
        component: detailProduct,
        meta: {
          requiresAdmin: true, // Thêm meta
          title: "Chi tiết sản phẩm",
        },
      },
      {
        path: "products/trash", // Đường dẫn cho thùng rác
        name: "trashedProducts", // Tên route cho thùng rác
        component: ProductTrash,
        meta: { requiresAdmin: true, title: "Thùng rác Sản phẩm" }, // Thêm meta và tiêu đề
      },
      {
        path: "attributes",
        name: "AttributeIndex",
        component: AttributeManagementPage,
        meta: {
          requiresAdmin: true, // Thêm meta
          title: "Danh sách biến thể",
        },
      },
      {
        path: "categories",
        name: "Categories",
        component: CategoryManager,
        meta: { requiresAdmin: true, title: "Quản lý danh mục" }, // Thêm meta và tiêu đề
      },
      {
        path: "/categories/edit/:id", // Đây là route con nhưng path bắt đầu bằng `/`, sẽ không kế thừa `/admin`
        name: "EditCategory",
        component: EditCategory,
        meta: { requiresAdmin: true, title: "Sửa danh mục" }, // Thêm meta và tiêu đề
      },
      {
        path: "brands",
        name: "BrandList",
        component: BrandList,
        meta: { requiresAdmin: true, title: "Quản lý thương hiệu" }, // Thêm meta và tiêu đề
      },
      {
        path: "/brand/them-moi", // Đây là route con nhưng path bắt đầu bằng `/`, sẽ không kế thừa `/admin`
        name: "BrandAdd",
        component: BrandAdd,
        meta: { requiresAdmin: true, title: "Thêm thương hiệu" }, // Thêm meta và tiêu đề
      },
      {
        path: "/brand/sua/:id", // Đây là route con nhưng path bắt đầu bằng `/`, sẽ không kế thừa `/admin`
        name: "BrandEdit",
        component: BrandEdit,
        meta: { requiresAdmin: true, title: "Sửa thương hiệu" }, // Thêm meta và tiêu đề
      },
      {
        path: "/brand/thung-rac", // Đây là route con nhưng path bắt đầu bằng `/`, sẽ không kế thừa `/admin`
        name: "BrandTrash",
        component: BrandTrash,
        meta: { requiresAdmin: true, title: "Thùng rác Thương hiệu" }, // Thêm meta và tiêu đề
      },
      {
        path: "coupons",
        name: "Coupons",
        component: CouponManager,
        meta: { requiresAdmin: true, title: "Quản lý mã giảm giá" }, // Thêm meta và tiêu đề
      },
      {
        path: "/coupons/edit/:id", // Đây là route con nhưng path bắt đầu bằng `/`, sẽ không kế thừa `/admin`
        name: "EditCoupons",
        component: EditCoupon,
        meta: { requiresAdmin: true, title: "Sửa mã giảm giá" }, // Thêm meta và tiêu đề
      },
      {
        path: "/coupons/trash",
        name: "CouponTrash",
        component: CouponTrash,
        meta: { requiresAdmin: true, title: "Thùng Rác Mã Giảm Giá" }, // Thêm meta
      },
      {
        path: "scent-groups",
        name: "ScentGroups",
        component: ScentGroupManager,
        meta: { requiresAdmin: true, title: "Quản lý nhóm hương" }, // Thêm meta và tiêu đề
      },
      {
        path: "/scent-group/edit/:id", // Đây là route con nhưng path bắt đầu bằng `/`, sẽ không kế thừa `/admin`
        name: "EditScentGroup",
        component: EditScentGroup,
        meta: { requiresAdmin: true, title: "Sửa nhóm hương" }, // Thêm meta và tiêu đề
      },
      {
        path: "scent-groups/trash",
        name: "ScentGroupTrash",
        component: ScentGroupTrash,
        meta: { requiresAdmin: true, title: "Thùng Rác Nhóm Hương" }, // Thêm meta
      },
      {
        path: "orders",
        name: "OrderList",
        component: OrderList,
        meta: { requiresAdmin: true, title: "Danh sách đơn hàng" }, // Thêm meta và tiêu đề
      },
      {
        path: "shipping-methods",
        name: "DonViVanChuyen",
        component: ShippingMethodList,
        meta: { requiresAdmin: true, title: "Quản lý đơn vị vận chuyển" }, // Thêm meta và tiêu đề
      }
    ],
  },
];