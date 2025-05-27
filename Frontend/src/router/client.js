export default [
  {
    path: '/',
    component: () => import('@/layouts/ClientLayout.vue'),
    children: [
      {
        path: '',
        name: 'Home',
        component: () => import('@/pages/client/HomeView.vue'),
      },
      {
        path: 'about',
        name: 'About',
        component: () => import('@/pages/client/AboutView.vue'),
      }
    ]
  }
]
