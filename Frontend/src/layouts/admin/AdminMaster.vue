<template>
    <div class="wrapper admin-root">

        <!-- Sidebar -->
        <Sidebar />
        <!-- End Sidebar -->

        <div class="main-panel">

            <!-- Header -->
            <div class="main-header">
                <div class="main-header-logo">
                    <!-- Logo Header -->
                    <LogoHeader />
                    <!-- End Logo Header -->
                </div>
                <!-- Navbar Header -->
                <Navbar />
                <!-- End Navbar -->

            </div>
            <!-- End Header -->

            <!-- Content -->

            <router-view></router-view>

            <!-- Footer -->
            <footer class="footer">
                <Footer />
            </footer>
            <!-- End Footer -->

        </div>

        <!-- Custom template | don't include it in your project! -->
        <CustomTemplate />
        <!-- End Custom template -->
    </div>
</template>

   <script setup>
import { onMounted } from 'vue';
import Sidebar from './Sidebar.vue';
import Footer from './Footer.vue';
import LogoHeader from './LogoHeader.vue';
import Navbar from './Navbar.vue';
import CustomTemplate from './CustomTemplate.vue';

onMounted(() => {
  const scripts = [
    "/tinymce/tinymce.min.js", // Ưu tiên tải TinyMCE
    "https://code.jquery.com/jquery-3.7.1.min.js",
    "https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js",
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js",
    "https://cdnjs.cloudflare.com/ajax/libs/jquery-sparkline/2.1.2/jquery.sparkline.min.js",
    "/assets/js/kaiadmin.min.js"
  ];

  scripts.forEach(src => {
    const script = document.createElement("script");
    script.src = src;
    script.async = false; // Tải tuần tự
    document.head.appendChild(script);
  });
});
</script>
    <style>
        @import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css';
        @import 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css';
        /* @import '/assets/css/bootstrap.min.css'; */
        @import '/assets/css/kaiadmin.min.css';


        .chart-container,
        .pull-in {
            position: relative;
            width: 100%;
            height: 375px;
        }

        canvas {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
        }

        .navbar-brand {
            display: block !important;
            width: auto;
            height: 20px;
        }
    </style>