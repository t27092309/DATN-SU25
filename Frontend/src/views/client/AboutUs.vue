<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-red-50 to-red-100">
      <div class="container mx-auto px-4 py-16 lg:py-24">
        <div class="text-center max-w-4xl mx-auto">
          <h1 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6">
            Về <span class="text-red-600">Floréa</span>
          </h1>
          <p class="text-xl lg:text-2xl text-gray-700 leading-relaxed">
            Hơn cả thơm & đẹp, nước hoa chính là cá tính và dấu ấn của mỗi người. 
            Hãy chọn Floréa - chọn lấy những mùi hương năng nở cùng với chúng tôi!
          </p>
        </div>
      </div>
    </div>

    <!-- Câu chuyện của chúng tôi -->
    <div class="container mx-auto px-4 py-16">
      <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
        <div class="order-2 lg:order-1">
          <div class="bg-gray-50 rounded-2xl p-8">
            <img 
              src="https://res.cloudinary.com/dfhextjvl/image/upload/v1756112130/4e73874e-3827-4bf9-8f08-f79f116560dc_fcafsg.png" 
              alt="Cửa hàng Floréa" 
              class="w-full h-220 object-cover rounded-xl shadow-lg"
            />
            <p class="text-center text-sm text-gray-600 mt-4 italic">
              Cửa hàng Floréa tại trung tâm Hà Nội
            </p>
          </div>
        </div>
        
        <div class="order-1 lg:order-2">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-8">
            Câu chuyện của <span class="text-red-600">chúng tôi</span>
          </h2>
          <div class="space-y-6 text-gray-700 text-lg leading-relaxed">
            <p>
              <strong class="text-red-600">Ra đời vào ngày 14/8/2004</strong>, hiện tại Floréa là nơi cung cấp 
              hơn 2000 nhãn hiệu nước hoa cao cấp tới thế giới. Là website nước hoa đầu tiên tại Việt Nam, 
              chúng tôi tự hào về hành trình hơn 20 năm đồng hành cùng người Việt trong việc khám phá và 
              yêu thương những mùi hương tuyệt vời.
            </p>
            <p>
              <strong>Định hướng của chúng tôi</strong> là trở thành nhà cung cấp nước hoa số 1 tại Việt Nam, 
              mang lại sự lựa chọn đa dạng, sự thuận tiện, tiết kiệm tiền & thời gian, đồng thời mang đến 
              sự yên tâm & hài lòng tối đa đến với khách hàng.
            </p>
          </div>
        </div>
      </div>

      <!-- Tầm nhìn & Sứ mệnh -->
      <div class="bg-gray-50 rounded-3xl p-8 lg:p-12 mb-20">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
            Tầm nhìn & <span class="text-red-600">Sứ mệnh</span>
          </h2>
        </div>
        
        <div class="grid md:grid-cols-2 gap-12">
          <div class="bg-white rounded-xl p-8 shadow-md">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Tầm nhìn</h3>
            <p class="text-gray-700 leading-relaxed">
              Trở thành nhà phân phối nước hoa hàng đầu Việt Nam, mang đến cho khách hàng 
              những trải nghiệm mua sắm tuyệt vời với đa dạng sản phẩm chất lượng cao từ 
              các thương hiệu nổi tiếng thế giới.
            </p>
          </div>
          
          <div class="bg-white rounded-xl p-8 shadow-md">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Sứ mệnh</h3>
            <p class="text-gray-700 leading-relaxed">
              Giúp mọi người tìm thấy mùi hương phù hợp với cá tính và phong cách riêng, 
              tạo nên dấu ấn đặc biệt và sự tự tin trong cuộc sống hàng ngày. Chúng tôi 
              cam kết mang đến sản phẩm chính hãng với giá tốt nhất.
            </p>
          </div>
        </div>
      </div>

      <!-- Giá trị cốt lõi -->
      <div class="mb-20">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
            Giá trị <span class="text-red-600">cốt lõi</span>
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Những nguyên tắc định hướng hoạt động của chúng tôi
          </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="value in coreValues" :key="value.id" class="text-center group">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-red-200 transition-colors">
              <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="value.icon" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ value.title }}</h3>
            <p class="text-gray-600">{{ value.description }}</p>
          </div>
        </div>
      </div>

      <!-- Thống kê -->
      <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-3xl p-8 lg:p-12 text-white mb-20">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold mb-4">
            Con số ấn tượng
          </h2>
          <p class="text-xl opacity-90">
            Những thành tựu mà chúng tôi đã đạt được
          </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="stat in statistics" :key="stat.id" class="text-center">
            <div class="text-4xl lg:text-5xl font-bold mb-2">{{ stat.number }}</div>
            <div class="text-lg opacity-90">{{ stat.label }}</div>
          </div>
        </div>
      </div>

      <!-- Cam kết -->
      <div class="mb-20">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
            Cam kết của <span class="text-red-600">Floréa</span>
          </h2>
        </div>
        
        <div class="grid lg:grid-cols-3 gap-8">
          <div v-for="commitment in commitments" :key="commitment.id" 
               class="bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg transition-shadow">
            <div class="text-center mb-6">
              <div :class="`w-16 h-16 ${commitment.bgColor} rounded-full flex items-center justify-center mx-auto mb-4`">
                <svg :class="`w-8 h-8 ${commitment.iconColor}`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="commitment.icon" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-3">{{ commitment.title }}</h3>
            </div>
            <p class="text-gray-600 leading-relaxed text-center">
              {{ commitment.description }}
            </p>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="text-center bg-gray-50 rounded-3xl p-12">
        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-6">
          Khám phá thế giới <span class="text-red-600">nước hoa</span> cùng Floréa
        </h2>
        <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
          Hãy để chúng tôi đồng hành cùng bạn trong hành trình tìm kiếm mùi hương hoàn hảo. 
          Liên hệ với chúng tôi ngay hôm nay để được tư vấn miễn phí!
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <div class="flex items-center text-gray-700">
            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <span class="font-semibold">028 999 999 42</span>
          </div>
          <div class="flex items-center text-gray-700">
            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span class="font-semibold">cskh@florea.vn</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Giá trị cốt lõi
const coreValues = ref([
  {
    id: 1,
    title: 'Chất lượng',
    description: 'Cam kết 100% sản phẩm chính hãng từ các thương hiệu uy tín trên thế giới',
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
  },
  {
    id: 2,
    title: 'Nhanh chóng',
    description: 'Giao hàng nhanh chóng trên toàn quốc với dịch vụ chuyển phát uy tín',
    icon: 'M13 10V3L4 14h7v7l9-11h-7z'
  },
  {
    id: 3,
    title: 'Tư vấn',
    description: 'Đội ngũ tư vấn chuyên nghiệp, nhiệt tình hỗ trợ khách hàng 24/7',
    icon: 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z'
  },
  {
    id: 4,
    title: 'Giá tốt',
    description: 'Cam kết giá tốt nhất thị trường với nhiều chương trình ưu đãi hấp dẫn',
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'
  }
])

// Thống kê
const statistics = ref([
  { id: 1, number: '20+', label: 'Năm kinh nghiệm' },
  { id: 2, number: '2000+', label: 'Nhãn hiệu nước hoa' },
  { id: 3, number: '100K+', label: 'Khách hàng tin tưởng' },
  { id: 4, number: '63', label: 'Tỉnh thành phủ sóng' }
])

// Cam kết
const commitments = ref([
  {
    id: 1,
    title: 'Sản phẩm chính hãng 100%',
    description: 'Tất cả sản phẩm đều được nhập khẩu trực tiếp từ các nhà phân phối chính thức, đảm bảo nguồn gốc xuất xứ rõ ràng và chất lượng tuyệt đối.',
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    bgColor: 'bg-green-100',
    iconColor: 'text-green-600'
  },
  {
    id: 2,
    title: 'Dịch vụ tận tâm',
    description: 'Đội ngũ nhân viên được đào tạo chuyên sâu về nước hoa, luôn sẵn sàng tư vấn và hỗ trợ khách hàng tìm được sản phẩm phù hợp nhất.',
    icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    bgColor: 'bg-blue-100',
    iconColor: 'text-blue-600'
  },
  {
    id: 3,
    title: 'Đổi trả dễ dàng',
    description: 'Chính sách đổi trả linh hoạt trong 7 ngày, hoàn tiền 100% nếu sản phẩm không đúng mô tả hoặc có lỗi từ nhà sản xuất.',
    icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
    bgColor: 'bg-purple-100',
    iconColor: 'text-purple-600'
  }
])
</script>