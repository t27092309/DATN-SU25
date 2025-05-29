import ClientLayout from '@/layouts/ClientLayout.vue'
import Home from '@/views/client/Home.vue'
import NuocHoaPage from '@/views/client/NuocHoaPage.vue'
import NuocHoaNamPage from '@/views/client/NuocHoaNamPage.vue'
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
    ]
  }
]
