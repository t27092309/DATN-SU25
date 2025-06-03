import ClientLayout from '@/layouts/ClientLayout.vue'
import Home from '@/views/client/Home.vue'
import NuocHoaPage from '@/views/client/NuocHoaPage.vue'
import NuocHoaNamPage from '@/views/client/NuocHoaNamPage.vue'
import NuocHoaNuPage from '@/views/client/NuocHoaNuPage.vue'
import ThuongHieuPage from '@/views/client/ThuongHieuPage.vue'
import NuocHoaMiniPage from '@/views/client/NuocHoaMiniPage.vue'
import NuocHoaChietPage from '@/views/client/NuocHoaChietPage.vue'
import NuocHoaNichePage from '@/views/client/NuocHoaNichePage.vue'
import NuocHoaUnisexPage from '@/views/client/NuocHoaUnisexPage.vue'
import Product from '@/views/client/Product.vue'

export default [
  {
    path: '/',
    component: ClientLayout,
    children: [
      {
        path: '',
        name: 'Home',
        component: Home,
      },
      {
        path: 'nuoc-hoa',
        name: 'NuocHoa',
        component: NuocHoaPage,
      },
      {
        path: 'nuoc-hoa-nam',
        name: 'NuocHoaNam',
        component: NuocHoaNamPage,
      },
      {
        path: 'nuoc-hoa-nu',
        name: 'NuocHoaNu',
        component: NuocHoaNuPage,
      },
      {
        path: 'thuong-hieu',
        name: 'ThuongHieu',
        component: ThuongHieuPage,
      },
      {
        path: 'nuoc-hoa-mini',
        name: 'NuocHoaMini',
        component: NuocHoaMiniPage,
      },
      {
        path: 'nuoc-hoa-chiet',
        name: 'NuocHoaChiet',
        component: NuocHoaChietPage,
      },
      {
        path: 'nuoc-hoa-unisex',
        name: 'NuocHoaUnisex',
        component: NuocHoaUnisexPage,
      },
      {
        path: 'nuoc-hoa-niche',
        name: 'NuocHoaNiche',
        component: NuocHoaNichePage,
      },
      {
        path: 'san-pham',
        name: 'SanPham',
        component: Product,
      },
    ]
  }
]
