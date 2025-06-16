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

    import Sidebar from './Sidebar.vue'
    import Footer from './Footer.vue'
    import LogoHeader from './LogoHeader.vue'
    import Navbar from './Navbar.vue'
    import CustomTemplate from './CustomTemplate.vue'

    onMounted(() => {
        const scripts = [
            "https://code.jquery.com/jquery-3.7.1.min.js",
            "https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js",
            "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js",
            "https://cdnjs.cloudflare.com/ajax/libs/jquery-sparkline/2.1.2/jquery.sparkline.min.js",
            "/assets/js/kaiadmin.min.js",
        ];

        scripts.forEach(src => {
            const script = document.createElement("script");
            script.src = src;
            script.async = true;
            document.head.appendChild(script);
        });

        // Khởi tạo Sparkline và Chart.js
        const jqueryScript = document.createElement('script');
        jqueryScript.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
        jqueryScript.onload = () => {
            // Sparkline
            $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
                type: "line",
                height: "70",
                width: "100%",
                lineWidth: "2",
                lineColor: "#177dff",
                fillColor: "rgba(23, 125, 255, 0.14)",
            });

            $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
                type: "line",
                height: "70",
                width: "100%",
                lineWidth: "2",
                lineColor: "#f3545d",
                fillColor: "rgba(243, 84, 93, .14)",
            });

            $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
                type: "line",
                height: "70",
                width: "100%",
                lineWidth: "2",
                lineColor: "#ffa534",
                fillColor: "rgba(255, 165, 52, .14)",
            });
        };
        document.head.appendChild(jqueryScript);

        // Khởi tạo Chart.js
        const chartScript = document.createElement('script');
        chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js';
        chartScript.onload = () => {
            const ctxStats = document.getElementById('statisticsChart').getContext('2d');
            new Chart(ctxStats, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Visitors',
                            data: [1200, 1500, 1700, 1300, 1600, 1800],
                            borderColor: '#177dff',
                            fill: true,
                            backgroundColor: 'rgba(23, 125, 255, 0.14)',
                        },
                        {
                            label: 'Sales',
                            data: [300, 500, 400, 600, 700, 800],
                            borderColor: '#ffa534',
                            fill: true,
                            backgroundColor: 'rgba(255, 165, 52, 0.14)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            const ctxDaily = document.getElementById('dailySalesChart').getContext('2d');
            new Chart(ctxDaily, {
                type: 'bar',
                data: {
                    labels: ['Mar 25', 'Mar 26', 'Mar 27', 'Mar 28', 'Mar 29'],
                    datasets: [{
                        label: 'Sales',
                        data: [500, 700, 600, 800, 900],
                        backgroundColor: '#177dff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        };
        document.head.appendChild(chartScript);
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