import AdminLayout from "@/layouts/admin/AdminMaster.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Datatables from "@/views/admin/Datatables.vue";

//================= Products ============================
import Products from "@/views/admin/products/ProductsList.vue";
import AddProduct from "@/views/admin/products/AddProduct.vue";
import EditProduct from "@/views/admin/products/EditProduct.vue";
import detailProduct from "@/views/admin/products/DetailProduct.vue";
import ProductTrash from "@/views/admin/products/ProductTrash.vue";

import AttributeIndex from "@/views/admin/Attribute/AttributeIndex.vue";
import AttributeForm from "@/views/admin/Attribute/AttributeForm.vue";
import AttributeValueIndex from "@/views/admin/Attribute/AttributeValueIndex.vue";
import AttributeValueForm from "@/views/admin/AttributeValue/AttributeValueForm.vue";

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
        path: "products",
        name: "products",
        component: Products,
        meta: {
          title: "Danh sách sản phẩm",
        },
      },
      {
        path: "add-product",
        name: "addProduct",
        component: AddProduct,
        meta: {
          title: "Thêm mới sản phẩm",
        },
      },
      {
        path: "edit-product/:id",
        name: "editProduct",
        component: EditProduct,
        meta: {
          title: "Sửa sản phẩm",
        },
      },
      {
        path: "detail-product/:id",
        name: "detailProduct",
        component: detailProduct,
        meta: {
          title: "Chi tiết sản phẩm",
        },
      },
      {
        path: 'products/trash', // Đường dẫn cho thùng rác
        name: 'trashedProducts', // Tên route cho thùng rác
        component: ProductTrash,
        meta: { title: 'Thùng rác Sản phẩm' } // Tiêu đề cho trang thùng rác
      },
      {
        path: "attributes",
        name: "AttributeIndex",
        component: AttributeIndex,
        meta: {
          title: "Danh sách biến thể",
        },
      },
      {
        path: "attributes/create",
        name: "AttributeCreate",
        component: AttributeForm,
        meta: {
          title: "Danh sách biến thể",
        },
      },
      {
        path: "attributes/:id/edit",
        name: "AttributeEdit",
        component: AttributeForm,
        props: true,
        meta: {
          title: "Danh sách biến thể",
        },
      },
      {
        path: "attributes/:attributeId/values",
        name: "AttributeValueIndex",
        component: AttributeValueIndex,
        props: true,
        meta: {
          title: "Danh sách biến thể",
        },
      },
      {
        path: "attributes/:attributeId/values/create",
        name: "AttributeValueCreate",
        component: AttributeValueForm,
        props: true,
        meta: {
          title: "Danh sách biến thể",
        },
      },
      {
        path: "attributes/:attributeId/values/:valueId/edit",
        name: "AttributeValueEdit",
        component: AttributeValueForm,
        props: true,
        meta: {
          title: "Danh sách biến thể",
        },
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
        path: "/hang",
        name: "BrandList",
        component: BrandList,
      },
      {
        path: "/hang/them-moi",
        name: "BrandAdd",
        component: BrandAdd,
      },
      {
        path: "/hang/sua/:id",
        name: "BrandEdit",
        component: BrandEdit,
      },
      {
        path: "/hang/thung-rac",
        name: "BrandTrash",
        component: BrandTrash,
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
      {
        path: 'scent-groups/trash',
        name: 'ScentGroupTrash',
        component: ScentGroupTrash,
        meta: { title: 'Thùng Rác Nhóm Hương' }
      },

    ],
  },
];
