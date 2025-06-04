import AdminLayout from "@/layouts/admin/AdminMaster.vue";
import AdminDashboard from "@/views/admin/AdminDashboard.vue";
import Datatables from "@/views/admin/Datatables.vue";

//================= Product============================
import Products from "@/views/admin/products/ProductsList.vue";
import AddProduct from "@/views/admin/products/AddProduct.vue";
import EditProduct from "@/views/admin/products/EditProduct.vue";
import detailProduct from "@/views/admin/products/DetailProduct.vue";

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
        ],
    },
];