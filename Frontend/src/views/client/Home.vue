<template>
    <main>
        <Splide :options="{ type: 'loop', perPage: 1, autoplay: true }">
            <SplideSlide>
                <img :src="banner" class="w-full" />
            </SplideSlide>
            <SplideSlide>
                <img :src="banner" class="w-full" />
            </SplideSlide>
            <SplideSlide>
                <img :src="banner" class="w-full" />
            </SplideSlide>
        </Splide>

        <div class="mega-menu mx-auto max-w-[1200px] px-4">
            <h2 class="text-2xl font-bold text-gray-800 py-8">Nước hoa tại Florea</h2>

            <div class="py-4 px-4 items-center">
                <h2 class="text-2xl font-bold text-gray-800">Thương hiệu và danh mục</h2>
                <div class="mx-auto bg-white rounded-lg shadow-xl p-8 w-[1200px] -translate-x-4"
                    style="max-height: calc(100vh - 70px); overflow-y: auto;">
                    <div class="container mx-auto max-w-6xl">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Khám Phá Nước Hoa</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12">
                            <div
                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                <div class="relative w-full h-64 overflow-hidden">
                                    <img :src="cateNuocHoaNam" alt="Men's Perfume"
                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                <div class="relative w-full h-64 overflow-hidden">
                                    <img :src="cateNuocHoaNu" alt="Women's Perfume"
                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                <div class="relative w-full h-64 overflow-hidden">
                                    <img :src="cateNuocHoaUnisex" alt="Unisex Perfume"
                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                <div class="relative w-full h-64 overflow-hidden">
                                    <img :src="cateNuocHoaNiche" alt="Niche Perfume"
                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                <div class="relative w-full h-64 overflow-hidden">
                                    <img :src="cateNuocHoaMini" alt="Mini Perfume"
                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                </div>
                            </div>
                        </div>

                        <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center">Các thương Hiệu Nổi Bật
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-y-8 gap-x-4">
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-bvlgary.webp"
                                    alt="BVLGARI Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-calvin-klein.webp"
                                    alt="Parfums Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-chanel.webp"
                                    alt="CHANEL Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-dior.webp"
                                    alt="DIOR Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-giorgio-armani.webp"
                                    alt="GIORGIO ARMANI Logo" class=" h-16 max-w-full object-contain" />
                            </div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-gucci.webp"
                                    alt="GUCCI Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jean-paul-gaultier.webp"
                                    alt="Jean Paul GAULTIER Logo" class=" h-16 max-w-full object-contain" />
                            </div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jo-malone.webp"
                                    alt="Jo Malone Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-kilian.webp"
                                    alt="Kilian Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-lancome.webp"
                                    alt="LANCÔME Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-montblanc.webp"
                                    alt="MONTBLANC Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-narciso-rodriguez.webp"
                                    alt="narciso rodriguez Logo" class=" h-16 max-w-full object-contain" />
                            </div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-parfums-de-marly.webp"
                                    alt="PARFUMS DE MARLY Logo" class=" h-16 max-w-full object-contain" />
                            </div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-tom-ford.webp"
                                    alt="TOM FORD Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-versace.webp"
                                    alt="VERSACE Logo" class=" h-16 max-w-full object-contain" /></div>
                            <div class="flex justify-center items-center"><img
                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-ysl.webp"
                                    alt="Yves Saint Laurent Logo" class=" h-16 max-w-full object-contain" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ProductsByCategory />

        <div class="box-products-1 mx-auto max-w-[1200px] px-4">
            <div class=" py-4 px-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Nước hoa Nữ</h2>
                <a href="#" class=" text-blue-700 text-base hover:text-blue-400 flex items-center">
                    Xem thêm nước hoa nữ
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-43%</span>
                    <img src="https://via.placeholder.com/150" alt="NARCISO RODRIGUEZ For Her EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 100 ML 150 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">NARCISO RODRIGUEZ</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">For Her EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.650.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.080.000 ₫</p>
                </div>
                <!-- 
                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-51%</span>
                    <img src="https://via.placeholder.com/150" alt="GUCCI Bloom EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">GUCCI</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Bloom EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.390.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.280.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-15%</span>
                    <img src="https://via.placeholder.com/150" alt="YVES SAINT LAURENT Libre EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 90 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">YVES SAINT LAURENT</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Libre EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.800.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.180.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-51%</span>
                    <img src="https://via.placeholder.com/150" alt="ARMAF Club De Nuit Woman EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">105 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">ARMAF</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Club De Nuit Woman EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">1.580.000 ₫</p>
                    <p class="text-md font-bold text-red-600">780.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-13%</span>
                    <img src="https://via.placeholder.com/150" alt="DIOR J'adore EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML 150 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DIOR</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">J'adore EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.480.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-18%</span>
                    <img src="https://via.placeholder.com/150" alt="CHANEL Coco Mademoiselle EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">35 ML 50 ML 100 ML 200 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">CHANEL</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Coco Mademoiselle EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">5.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">4.280.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-6%</span>
                    <img src="https://via.placeholder.com/150" alt="DIOR Miss Dior EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DIOR</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Miss Dior EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.780.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-30%</span>
                    <img src="https://via.placeholder.com/150" alt="DOLCE & GABBANA Light Blue Eau Int..."
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DOLCE & GABBANA</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Light Blue Eau Int...</p>
                    <p class="text-xs text-gray-400 line-through mt-2">2.838.000 ₫</p>
                    <p class="text-md font-bold text-red-600">1.980.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-23%</span>
                    <img src="https://via.placeholder.com/150" alt="JEAN PAUL GAULTIER So Scandal EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 80 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">JEAN PAUL GAULTIER</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">So Scandal EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.500.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.680.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-27%</span>
                    <img src="https://via.placeholder.com/150" alt="VALENTINO Donna Born in Roma EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">VALENTINO</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Donna Born in Roma EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.560.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.580.000 ₫</p>
                </div> -->

            </div>
        </div>

        <div class="box-products-2 mx-auto max-w-[1200px] px-4 mt-20">
            <div class=" py-4 px-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Nước hoa Nam</h2>
                <a href="#" class=" text-blue-700 text-base hover:text-blue-400 flex items-center">
                    Xem thêm nước hoa nam
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-43%</span>
                    <img src="https://via.placeholder.com/150" alt="NARCISO RODRIGUEZ For Her EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 100 ML 150 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">NARCISO RODRIGUEZ</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">For Her EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.650.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.080.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-51%</span>
                    <img src="https://via.placeholder.com/150" alt="GUCCI Bloom EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">GUCCI</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Bloom EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.390.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.280.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-15%</span>
                    <img src="https://via.placeholder.com/150" alt="YVES SAINT LAURENT Libre EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 90 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">YVES SAINT LAURENT</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Libre EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.800.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.180.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-51%</span>
                    <img src="https://via.placeholder.com/150" alt="ARMAF Club De Nuit Woman EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">105 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">ARMAF</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Club De Nuit Woman EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">1.580.000 ₫</p>
                    <p class="text-md font-bold text-red-600">780.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-13%</span>
                    <img src="https://via.placeholder.com/150" alt="DIOR J'adore EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML 150 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DIOR</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">J'adore EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.480.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-18%</span>
                    <img src="https://via.placeholder.com/150" alt="CHANEL Coco Mademoiselle EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">35 ML 50 ML 100 ML 200 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">CHANEL</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Coco Mademoiselle EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">5.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">4.280.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-6%</span>
                    <img src="https://via.placeholder.com/150" alt="DIOR Miss Dior EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DIOR</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Miss Dior EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.780.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-30%</span>
                    <img src="https://via.placeholder.com/150" alt="DOLCE & GABBANA Light Blue Eau Int..."
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DOLCE & GABBANA</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Light Blue Eau Int...</p>
                    <p class="text-xs text-gray-400 line-through mt-2">2.838.000 ₫</p>
                    <p class="text-md font-bold text-red-600">1.980.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-23%</span>
                    <img src="https://via.placeholder.com/150" alt="JEAN PAUL GAULTIER So Scandal EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 80 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">JEAN PAUL GAULTIER</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">So Scandal EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.500.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.680.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-27%</span>
                    <img src="https://via.placeholder.com/150" alt="VALENTINO Donna Born in Roma EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">VALENTINO</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Donna Born in Roma EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.560.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.580.000 ₫</p>
                </div>

            </div>
        </div>

        <div class="box-products-3 mx-auto max-w-[1200px] px-4 mt-20">
            <div class=" py-4 px-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Nước hoa Niche</h2>
                <a href="#" class=" text-blue-700 text-base hover:text-blue-400 flex items-center">
                    Xem thêm nước hoa niche
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-43%</span>
                    <img src="https://via.placeholder.com/150" alt="NARCISO RODRIGUEZ For Her EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 100 ML 150 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">NARCISO RODRIGUEZ</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">For Her EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.650.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.080.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-51%</span>
                    <img src="https://via.placeholder.com/150" alt="GUCCI Bloom EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">GUCCI</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Bloom EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.390.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.280.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-15%</span>
                    <img src="https://via.placeholder.com/150" alt="YVES SAINT LAURENT Libre EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 90 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">YVES SAINT LAURENT</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Libre EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.800.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.180.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-51%</span>
                    <img src="https://via.placeholder.com/150" alt="ARMAF Club De Nuit Woman EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">105 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">ARMAF</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Club De Nuit Woman EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">1.580.000 ₫</p>
                    <p class="text-md font-bold text-red-600">780.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-13%</span>
                    <img src="https://via.placeholder.com/150" alt="DIOR J'adore EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML 150 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DIOR</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">J'adore EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.480.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-18%</span>
                    <img src="https://via.placeholder.com/150" alt="CHANEL Coco Mademoiselle EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">35 ML 50 ML 100 ML 200 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">CHANEL</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Coco Mademoiselle EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">5.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">4.280.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-6%</span>
                    <img src="https://via.placeholder.com/150" alt="DIOR Miss Dior EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DIOR</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Miss Dior EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.780.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-30%</span>
                    <img src="https://via.placeholder.com/150" alt="DOLCE & GABBANA Light Blue Eau Int..."
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DOLCE & GABBANA</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Light Blue Eau Int...</p>
                    <p class="text-xs text-gray-400 line-through mt-2">2.838.000 ₫</p>
                    <p class="text-md font-bold text-red-600">1.980.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-23%</span>
                    <img src="https://via.placeholder.com/150" alt="JEAN PAUL GAULTIER So Scandal EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 80 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">JEAN PAUL GAULTIER</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">So Scandal EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.500.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.680.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-27%</span>
                    <img src="https://via.placeholder.com/150" alt="VALENTINO Donna Born in Roma EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">VALENTINO</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Donna Born in Roma EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.560.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.580.000 ₫</p>
                </div>

            </div>
        </div>

        <div class="box-products-4 mx-auto max-w-[1200px] px-4 mt-20 mb-20">
            <div class=" py-4 px-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Nước hoa Mini</h2>
                <a href="#" class=" text-blue-700 text-base hover:text-blue-400 flex items-center">
                    Xem thêm nước hoa mini
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-43%</span>
                    <img src="https://via.placeholder.com/150" alt="NARCISO RODRIGUEZ For Her EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 100 ML 150 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">NARCISO RODRIGUEZ</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">For Her EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.650.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.080.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-51%</span>
                    <img src="https://via.placeholder.com/150" alt="GUCCI Bloom EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">GUCCI</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Bloom EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.390.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.280.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-15%</span>
                    <img src="https://via.placeholder.com/150" alt="YVES SAINT LAURENT Libre EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 50 ML 90 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">YVES SAINT LAURENT</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Libre EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.800.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.180.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-51%</span>
                    <img src="https://via.placeholder.com/150" alt="ARMAF Club De Nuit Woman EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">105 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">ARMAF</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Club De Nuit Woman EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">1.580.000 ₫</p>
                    <p class="text-md font-bold text-red-600">780.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-13%</span>
                    <img src="https://via.placeholder.com/150" alt="DIOR J'adore EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML 150 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DIOR</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">J'adore EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.480.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-18%</span>
                    <img src="https://via.placeholder.com/150" alt="CHANEL Coco Mademoiselle EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">35 ML 50 ML 100 ML 200 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">CHANEL</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Coco Mademoiselle EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">5.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">4.280.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-6%</span>
                    <img src="https://via.placeholder.com/150" alt="DIOR Miss Dior EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DIOR</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Miss Dior EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">4.000.000 ₫</p>
                    <p class="text-md font-bold text-red-600">3.780.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-30%</span>
                    <img src="https://via.placeholder.com/150" alt="DOLCE & GABBANA Light Blue Eau Int..."
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">DOLCE & GABBANA</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Light Blue Eau Int...</p>
                    <p class="text-xs text-gray-400 line-through mt-2">2.838.000 ₫</p>
                    <p class="text-md font-bold text-red-600">1.980.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-23%</span>
                    <img src="https://via.placeholder.com/150" alt="JEAN PAUL GAULTIER So Scandal EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">30 ML 80 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">JEAN PAUL GAULTIER</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">So Scandal EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.500.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.680.000 ₫</p>
                </div>

                <div
                    class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                    <span
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">-27%</span>
                    <img src="https://via.placeholder.com/150" alt="VALENTINO Donna Born in Roma EDP"
                        class="w-28 h-28 object-contain mb-2">
                    <p class="text-xs text-gray-500 mb-1">100 ML</p>
                    <p class="text-sm font-semibold mb-1 truncate w-full">VALENTINO</p>
                    <p class="text-xs font-medium mb-1 truncate w-full">Donna Born in Roma EDP</p>
                    <p class="text-xs text-gray-400 line-through mt-2">3.560.000 ₫</p>
                    <p class="text-md font-bold text-red-600">2.580.000 ₫</p>
                </div>

            </div>
        </div>
    </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ProductsByCategory from '@/components/ProductsByCategory.vue';

const categoriesData = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchMostViewedProducts = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/most-viewed-products-by-categories'); // Thay thế bằng URL API thực tế của bạn
        categoriesData.value = response.data;
    } catch (err) {
        error.value = 'Không thể tải dữ liệu sản phẩm. Vui lòng thử lại sau.';
        console.error('Lỗi khi fetch API:', err);
    } finally {
        loading.value = false;
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(value);
};

onMounted(() => {
    fetchMostViewedProducts();
});

import banner from '@/assets/images/banners/banner-happy-birthday-orchard-21st_desktop.webp'
import cateNuocHoaNam from '@/assets/images/categories/category-nuoc-hoa-nam.webp'
import cateNuocHoaNu from '@/assets/images/categories/category-nuoc-hoa-nu-600x720.webp'
import cateNuocHoaUnisex from '@/assets/images/categories/category-nuoc-hoa-unisex-600x720.webp'
import cateNuocHoaNiche from '@/assets/images/categories/category-nuoc-hoa-niche-600x720.webp'
import cateNuocHoaMini from '@/assets/images/categories/category-nuoc-hoa-mini-600x720.webp'
</script>