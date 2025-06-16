<style scoped>
@import '@/assets/tailwind.css';
</style>
<template>
    <div class="container mx-auto p-4 max-w-[1200px]">
        <p class="font-bold text-3xl text-black">Thương hiệu</p>
        <nav class="text-sm breadcrumbs mb-6 ">
            <ul class="flex items-center space-x-2">
                <li><router-link to="/" class="text-2xl"><span class="text-base text-gray-500">Trang
                            chủ</span></router-link>
                </li>
                <span class="text-gray-500 text-base">/</span>
                <li class="ml-2"><router-link to="/nuoc-hoa"><span class="text-base text-gray-500">Nước
                            Hoa</span></router-link>
                </li>
                <span class="text-gray-500 text-base">/</span>
                <li class="text-gray-900 font-bold text-base">Thương hiệu nước hoa</li>
            </ul>
        </nav>
        <h2 class="text-3xl font-bold text-center mb-10">Thương hiệu Nước hoa Nổi bật</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-5">
            <div v-for="brand in brands" :key="brand.name"
                class="flex items-center justify-center p-2 bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                <img :src="brand.logoUrl" :alt="brand.name" class="w-full h-full object-fit">
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const brands = ref([
    { name: 'AFNAN', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-afnan.jpg' },
    { name: 'ARMAF', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-armaf.webp' },
    { name: 'BURBERRY', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-burberry.webp' },
    { name: 'BVLGARI', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-bvlgary.webp' },
    { name: 'Calvin Klein', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/04/logo-brand-calvin-klein.webp' },
    { name: 'CHANNEL', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-chanel.webp' },
    { name: 'Carolina Herrera', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-carolina-herrera.webp' },
    { name: 'Chloe', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-chloe.webp' },
    { name: 'Creed', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-creed.jpg' },
    { name: 'Dior', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-dior.webp' },
    { name: 'Diptyque', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-diptyque.webp' },
    { name: 'Dolce and Gabbana', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-dolce-and-gabbana.jpg' },
    { name: 'Elizabeth Arden', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-elizabeth-arden.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-frederic-malle.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/04/logo-brand-giorgio-armani.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-gucci.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-hermes.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jean-paul-gaultier.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jo-malone.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-kenzo.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-kilian.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-lalique.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-lancome.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-le-labo.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-mfkp.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-maison-margiela.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/08/fragnance-logo-text-mancera.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-marc-jacobs.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-mcm.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-montblanc.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-moschino.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/04/logo-brand-narciso-rodriguez.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-nautica.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-paco-rabanne.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-parfums-de-marly.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-ralph-lauren.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-roja.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-thierry-mugler.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/08/fragnance-logo-text-thomas-kosmale.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-tom-ford.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-tommy-hilfiger.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-trussardi.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-valentino.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-versace.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/07/fragnance-logo-text-victoria-s-secret.jpg' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-viktor-rolf.webp' },
    { name: '', logoUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-ysl.webp' },

    // Thêm các thương hiệu khác nếu có
]);
</script>

<style scoped>
/* Không cần thêm CSS tùy chỉnh nếu chỉ dùng Tailwind */
</style>