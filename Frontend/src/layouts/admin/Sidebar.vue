<template>
    <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
                <router-link to="/admin" class="home-link">
                    <img :src="logoUrl" alt="navbar brand" class="navbar-brand" height="20" />
                </router-link>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                        <i class="gg-menu-right"></i>
                    </button>
                    <button class="btn btn-toggle sidenav-toggler">
                        <i class="gg-menu-left"></i>
                    </button>
                </div>
                <button class="topbar-toggler more">
                    <i class="gg-more-vertical-alt"></i>
                </button>
            </div>
            <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <ul class="nav nav-secondary">
                    <li class="nav-item" :class="{ active: $route.name === 'AdminDashboard' }">
                        <router-link :to="{ name: 'AdminDashboard' }" class="collapsed" aria-expanded="false">
                            <i class="fas fa-home"></i>
                            <p>Dashboard</p>
                        </router-link>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                    </li>
                    <li class="nav-item" :class="{ active: $route.name === 'products'}">
                        <a data-bs-toggle="collapse" href="#base">
                            <i class="fas fa-tshirt"></i>
                            <p>Base</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="base">
                            <ul class="nav nav-collapse">
                                <li>
                                    <router-link :to="{ name: 'products' }">
                                        <span class="sub-item">Products</span>
                                    </router-link>
                                </li>
                                <li>
                                    <a href="icon-menu.html">
                                        <span class="sub-item">Category</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarLayouts">
                            <i class="fas fa-th-list"></i>
                            <p>Sidebar Layouts</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="sidebarLayouts">
                            <ul class="nav nav-collapse">
                                <li>
                                    <a href="sidebar-style-2.html">
                                        <span class="sub-item">Sidebar Style 2</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="icon-menu.html">
                                        <span class="sub-item">Icon Menu</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#forms">
                            <i class="fas fa-pen-square"></i>
                            <p>Forms</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="forms">
                            <ul class="nav nav-collapse">
                                <li>
                                    <a href="forms/forms.html">
                                        <span class="sub-item">Basic Form</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#tables">
                            <i class="fas fa-table"></i>
                            <p>Tables</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="tables">
                            <ul class="nav nav-collapse">
                                <li>
                                    <a href="tables/tables.html">
                                        <span class="sub-item">Basic Table</span>
                                    </a>
                                </li>
                                <li>
                                    <router-link to="/admin/datatables">
                                        <span class="sub-item">Datatables</span>
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#maps">
                            <i class="fas fa-map-marker-alt"></i>
                            <p>Maps</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="maps">
                            <ul class="nav nav-collapse">
                                <li>
                                    <a href="maps/googlemaps.html">
                                        <span class="sub-item">Google Maps</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="maps/jsvectormap.html">
                                        <span class="sub-item">Jsvectormap</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#charts">
                            <i class="far fa-chart-bar"></i>
                            <p>Charts</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="charts">
                            <ul class="nav nav-collapse">
                                <li>
                                    <a href="charts/charts.html">
                                        <span class="sub-item">Chart Js</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="charts/sparkline.html">
                                        <span class="sub-item">Sparkline</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="widgets.html">
                            <i class="fas fa-desktop"></i>
                            <p>Widgets</p>
                            <span class="badge badge-success">4</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../../documentation/index.html">
                            <i class="fas fa-file"></i>
                            <p>Documentation</p>
                            <span class="badge badge-secondary">1</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#submenu">
                            <i class="fas fa-bars"></i>
                            <p>Menu Levels</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="submenu">
                            <ul class="nav nav-collapse">
                                <li>
                                    <a data-bs-toggle="collapse" href="#subnav1">
                                        <span class="sub-item">Level 1</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse" id="subnav1">
                                        <ul class="nav nav-collapse subnav">
                                            <li>
                                                <a href="#">
                                                    <span class="sub-item">Level 2</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#">
                                                    <span class="sub-item">Level 2</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li>
                                    <a data-bs-toggle="collapse" href="#subnav2">
                                        <span class="sub-item">Level 1</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse" id="subnav2">
                                        <ul class="nav nav-collapse subnav">
                                            <li>
                                                <a href="#">
                                                    <span class="sub-item">Level 2</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li>
                                    <a href="#">
                                        <span class="sub-item">Level 1</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
    import logo from "@/assets/img/kaiadmin/logo_light.svg";
    const logoUrl = logo;
</script>

<style></style>