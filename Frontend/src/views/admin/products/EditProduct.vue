<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6">
        <ul class="flex items-center space-x-2 text-gray-600 text-sm">
          <li>
            <router-link
              :to="{ name: 'AdminDashboard' }"
              class="hover:text-blue-800"
            >
              <i class="fas fa-home"></i>
            </router-link>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right text-xs"></i>
          </li>
          <li>
            <router-link :to="{ name: 'products' }" class="hover:text-blue-800"
              >Danh sách sản phẩm</router-link
            >
          </li>
          <li>
            <i class="fas fa-chevron-right text-gray-400"></i>
          </li>
          <li>
            <span class="font-semibold">{{ route.meta.title }}</span>
          </li>
        </ul>
      </div>

      <div class="bg-white shadow-lg rounded-lg">
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <div class="text-xl font-semibold">{{ route.meta.title }}</div>
          </div>
        </div>

        <div class="p-6">
          <form @submit.prevent="updateProduct">
            <div
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-6"
            >
              <div>
                <div class="mb-4">
                  <label
                    for="name"
                    class="block text-gray-700 text-sm font-bold mb-2"
                    >Tên sản phẩm <span class="text-red-500">*</span></label
                  >
                  <input
                    type="text"
                    class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    id="name"
                    placeholder="Nhập tên sản phẩm"
                    v-model="product.name"
                  />
                  <p
                    v-if="errors.name"
                    class="text-red-500 text-xs italic mt-1"
                  >
                    {{ errors.name }}
                  </p>
                </div>
                <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2"
                    >Giới tính <span class="text-red-500">*</span></label
                  >
                  <div class="flex gap-6 mt-1">
                    <div class="flex items-center">
                      <input
                        class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                        type="radio"
                        name="gender"
                        id="male"
                        value="male"
                        v-model="product.gender"
                      />
                      <label class="ml-2 text-gray-700" for="male">Nam</label>
                    </div>
                    <div class="flex items-center">
                      <input
                        class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                        type="radio"
                        name="gender"
                        id="female"
                        value="female"
                        v-model="product.gender"
                      />
                      <label class="ml-2 text-gray-700" for="female">Nữ</label>
                    </div>
                    <div class="flex items-center">
                      <input
                        class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                        type="radio"
                        name="gender"
                        id="unisex"
                        value="unisex"
                        v-model="product.gender"
                      />
                      <label class="ml-2 text-gray-700" for="unisex"
                        >Unisex</label
                      >
                    </div>
                  </div>
                  <p
                    v-if="errors.gender"
                    class="text-red-500 text-xs italic mt-1"
                  >
                    {{ errors.gender }}
                  </p>
                </div>
              </div>

              <div>
                <div class="mb-4">
                  <label
                    for="categorySelect"
                    class="block text-gray-700 text-sm font-bold mb-2"
                    >Danh mục <span class="text-red-500">*</span></label
                  >
                  <select
                    class="block appearance-none w-full bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded-md shadow-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    id="categorySelect"
                    v-model="product.category_id"
                  >
                    <option value="" disabled>Chọn danh mục</option>
                    <option
                      v-for="category in categories"
                      :key="category.id"
                      :value="category.id"
                    >
                      {{ category.name }}
                    </option>
                  </select>
                  <p
                    v-if="errors.category_id"
                    class="text-red-500 text-xs italic mt-1"
                  >
                    {{ errors.category_id }}
                  </p>
                </div>
                <div class="mb-4">
                  <label
                    for="brandSelect"
                    class="block text-gray-700 text-sm font-bold mb-2"
                    >Thương hiệu <span class="text-red-500">*</span></label
                  >
                  <select
                    class="block appearance-none w-full bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded-md shadow-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    id="brandSelect"
                    v-model="product.brand_id"
                    v-if="brands.length > 0"
                  >
                    <option value="" disabled>Chọn thương hiệu</option>
                    <option
                      v-for="brand in brands"
                      :key="brand.id"
                      :value="brand.id"
                    >
                      {{ brand.name }}
                    </option>
                  </select>
                  <p v-else class="text-gray-500 text-sm italic mt-1">
                    Đang tải thương hiệu...
                  </p>
                  <p
                    v-if="errors.brand_id"
                    class="text-red-500 text-xs italic mt-1"
                  >
                    {{ errors.brand_id }}
                  </p>
                </div>
              </div>

              <div>
                <div class="mb-4">
                  <label
                    for="slug"
                    class="block text-gray-700 text-sm font-bold mb-2"
                    >Slug (Tự động tạo)</label
                  >
                  <input
                    type="text"
                    class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight cursor-not-allowed"
                    id="slug"
                    placeholder="Slug sản phẩm"
                    v-model="product.slug"
                    disabled
                  />
                </div>
                <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2"
                    >Hình ảnh chính</label
                  >
                  <input
                    type="file"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer"
                    @change="onFileChangeMainImage"
                    accept="image/*"
                  />
                  <p
                    v-if="errors.image"
                    class="text-red-500 text-xs italic mt-1"
                  >
                    {{ errors.image[0] }}
                  </p>
                </div>

                <div
                  v-if="currentImageUrl"
                  class="mt-2 flex items-center space-x-4"
                >
                  <label class="block text-gray-700 text-sm font-bold"
                    >Ảnh hiện tại:</label
                  >
                  <div class="relative group">
                    <img
                      :src="currentImageUrl"
                      alt="Ảnh chính"
                      class="w-32 h-32 object-cover rounded-md shadow-md border border-gray-200"
                    />
                    <button
                      type="button"
                      @click="removeMainImage = true"
                      class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-8 border-gray-300" />

            <div class="mb-6">
              <label
                for="description"
                class="block text-gray-700 text-sm font-bold mb-2"
                >Mô tả</label
              >
              <textarea
                class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                id="description"
                rows="5"
                v-model="product.description"
                placeholder="Nhập mô tả sản phẩm"
              ></textarea>
              <p
                v-if="errors.description"
                class="text-red-500 text-xs italic mt-1"
              >
                {{ errors.description }}
              </p>
            </div>

            <hr class="my-8 border-gray-300 col-span-full" />

            <div class="mb-8">
              <h4 class="text-xl font-semibold mb-4 text-gray-800">
                Thư viện hình ảnh
              </h4>
              <label
                for="galleryImages"
                class="block text-gray-700 text-sm font-bold mb-2"
                >Chọn thêm ảnh</label
              >
              <input
                type="file"
                multiple
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer"
                @change="onGalleryFilesChange"
                accept="image/*"
              />
              <div
                v-if="product.gallery_images.length > 0"
                class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4"
              >
                <div
                  v-for="(image, index) in product.gallery_images"
                  :key="image.id"
                  class="relative group"
                >
                  <img
                    :src="getImageUrl(image.path)"
                    :alt="'Gallery Image ' + (index + 1)"
                    class="w-full h-32 object-cover rounded-md shadow-sm border border-gray-200"
                  />
                  <button
                    type="button"
                    @click="removeGalleryImage(image)"
                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>

            <hr class="my-8 border-gray-300" />

            <!-- <ScentGroupSelector
              :selected-scent-group-ids="
                product.scentGroups.selectedScentGroupIds
              "
              @update:selected-scent-group-ids="
                (newIds) => (product.scentGroups.selectedScentGroupIds = newIds)
              "
              :scent-groups-data="product.scentGroups.scentGroupsData"
              @update:scent-groups-data="
                (newData) => (product.scentGroups.scentGroupsData = newData)
              "
              :all-scent-groups="allScentGroups"
            /> -->

            <ScentGroupSelector
              :selected-scent-group-ids="
                product.scentGroups.selectedScentGroupIds
              "
              @update:selected-scent-group-ids="
                (newIds) => (product.scentGroups.selectedScentGroupIds = newIds)
              "
              :scent-groups-data="product.scentGroups.scentGroupsData"
              @update:scent-groups-data="
                (newData) => (product.scentGroups.scentGroupsData = newData)
              "
              :all-scent-groups="allScentGroups"
            />

            <div v-if="sortedScentProfiles.length > 0" class="mt-8">
              <h6 class="text-lg font-semibold mb-4 text-gray-800">
                Mức độ hương:
              </h6>
              <div class="space-y-4">
                <div
                  v-for="scent in sortedScentProfiles"
                  :key="scent.scent_group_id"
                  class="flex items-center"
                >
                  <span
                    class="scent-name mr-4 text-gray-700 font-medium w-32 truncate"
                    :title="scent.scent_group_name"
                  >
                    {{ scent.scent_group_name }}:
                  </span>
                  <div
                    class="flex-grow bg-gray-200 rounded-full h-6 overflow-hidden"
                  >
                    <div
                      class="h-full flex items-center justify-center text-sm font-bold transition-all duration-300 ease-out px-2"
                      role="progressbar"
                      :style="{
                        width: scent.strength + '%',
                        backgroundColor: scent.scent_group_color_code,
                      }"
                      :aria-valuenow="scent.strength"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    >
                      <span
                        :style="{
                          color: isDarkColor(scent.scent_group_color_code)
                            ? 'white'
                            : 'black',
                        }"
                      >
                        {{ scent.strength }}%
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-8 border-gray-300" />

            <!-- <UsageProfile v-model:usage-profile-data="product.usageProfile" :errors="errors" /> -->
            <UsageProfile
              v-model:usageProfileData="product.usageProfile"
              :errors="errors"
            />

            <hr class="my-8 border-gray-300 col-span-full" />

            <div class="mb-8 col-span-full">
              <label class="block text-gray-700 text-sm font-bold mb-3"
                >Loại sản phẩm <span class="text-red-500">*</span></label
              >
              <div class="flex items-center space-x-6">
                <div class="flex items-center">
                  <input
                    class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                    type="radio"
                    id="noVariants"
                    :value="false"
                    v-model="product.has_variants"
                  />
                  <label class="ml-2 text-gray-700" for="noVariants"
                    >Sản phẩm đơn giản</label
                  >
                </div>
                <div class="flex items-center">
                  <input
                    class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                    type="radio"
                    id="hasVariants"
                    :value="true"
                    v-model="product.has_variants"
                  />
                  <label class="ml-2 text-gray-700" for="hasVariants"
                    >Sản phẩm có biến thể</label
                  >
                </div>
              </div>
              <p
                v-if="errors.has_variants"
                class="text-red-500 text-xs italic mt-1"
              >
                {{ errors.has_variants[0] }}
              </p>
            </div>

            <hr class="my-8 border-gray-300 col-span-full" />

            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6"
              v-if="product.has_variants === false"
            >
              <div>
                <div class="mb-4">
                  <label
                    for="simplePrice"
                    class="block text-gray-700 text-sm font-bold mb-2"
                    >Giá sản phẩm <span class="text-red-500">*</span></label
                  >
                  <input
                    type="number"
                    class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    id="simplePrice"
                    placeholder="Nhập giá sản phẩm"
                    v-model="product.price"
                    min="0"
                  />
                  <p
                    v-if="errors.price"
                    class="text-red-500 text-xs italic mt-1"
                  >
                    {{ errors.price[0] }}
                  </p>
                </div>
              </div>
              <div>
                <div class="mb-4">
                  <label
                    for="simpleStock"
                    class="block text-gray-700 text-sm font-bold mb-2"
                    >Số lượng tồn kho <span class="text-red-500">*</span></label
                  >
                  <input
                    type="number"
                    class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    id="simpleStock"
                    placeholder="Nhập số lượng tồn kho"
                    v-model="product.stock"
                    min="0"
                  />
                  <p
                    v-if="errors.stock"
                    class="text-red-500 text-xs italic mt-1"
                  >
                    {{ errors.stock[0] }}
                  </p>
                </div>
              </div>
            </div>

            <div v-if="product.has_variants === true">
              <h4 class="text-xl font-semibold mt-8 mb-6 text-gray-800">
                Chọn thuộc tính và giá trị
              </h4>
              <div>
                <p
                  v-if="errors.variants"
                  class="text-red-500 text-xs italic mb-4"
                >
                  {{ errors.variants[0] }}
                </p>
                <div
                  v-for="attribute in attributes"
                  :key="attribute.id"
                  class="mb-6 p-5 border border-gray-200 rounded-lg shadow-sm bg-gray-50"
                >
                  <h6 class="text-lg font-semibold mb-4 text-gray-800">
                    {{ attribute.name }}
                  </h6>
                  <div class="flex flex-wrap gap-x-6 gap-y-3">
                    <div
                      v-for="value in attribute.attribute_values"
                      :key="value.id"
                      class="flex items-center"
                    >
                      <input
                        class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500 transition duration-150 ease-in-out"
                        type="checkbox"
                        :id="`attr-${attribute.id}-val-${value.id}`"
                        :value="value.id"
                        v-model="selectedAttributeValues[attribute.id]"
                      />
                      <label
                        class="ml-2 text-gray-700 select-none cursor-pointer"
                        :for="`attr-${attribute.id}-val-${value.id}`"
                      >
                        {{ value.value }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <h4 class="text-xl font-semibold mt-10 mb-6 text-gray-800">
                Các biến thể đã tạo
              </h4>
              <div
                v-if="product.variants.length > 0"
                class="overflow-x-auto rounded-lg shadow-md border border-gray-200"
              >
                <table class="min-w-full bg-white divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th
                        class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                      >
                        Tên biến thể
                      </th>
                      <th
                        class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                      >
                        SKU <span class="text-red-500">*</span>
                      </th>
                      <th
                        class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                      >
                        Giá <span class="text-red-500">*</span>
                      </th>
                      <th
                        class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                      >
                        Tồn kho <span class="text-red-500">*</span>
                      </th>
                      <th
                        class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                      >
                        Hành động
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <!-- <tr
                      v-for="(variant, index) in product.variants"
                      :key="index"
                      class="hover:bg-gray-50"
                    > -->
                   <tr v-for="(variant, index) in product.variants" :key="variant.id" class="hover:bg-gray-50">

                      <td
                        class="py-3 px-4 whitespace-nowrap text-sm font-medium text-gray-900"
                      >
                        {{
                          variant.attributes
                            .map((a) => a.value_name)
                            .join(" - ")
                        }}
                        <!-- {{ variant.attribute_values.map(av => av.value + ' (' + av.attribute.name + ')').join(' - ') }} -->
                      </td>
                      <td
                        class="py-3 px-4 whitespace-nowrap text-sm text-gray-700"
                      >
                        <input
                          type="text"
                          class="w-full text-sm py-1 px-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                          v-model="variant.sku"
                          :id="'variantSku' + index"
                        />
                        <p
                          v-if="errors[`variants.${index}.sku`]"
                          class="text-red-500 text-xs italic mt-1"
                        >
                          {{ errors[`variants.${index}.sku`][0] }}
                        </p>
                      </td>
                      <td
                        class="py-3 px-4 whitespace-nowrap text-sm text-gray-700"
                      >
                        <input
                          type="number"
                          class="w-full text-sm py-1 px-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                          v-model="variant.price"
                          min="0"
                          :id="'variantPrice' + index"
                        />
                        <p
                          v-if="errors[`variants.${index}.price`]"
                          class="text-red-500 text-xs italic mt-1"
                        >
                          {{ errors[`variants.${index}.price`][0] }}
                        </p>
                      </td>
                      <td
                        class="py-3 px-4 whitespace-nowrap text-sm text-gray-700"
                      >
                        <input
                          type="number"
                          class="w-full text-sm py-1 px-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                          v-model="variant.stock"
                          min="0"
                          :id="'variantStock' + index"
                        />
                        <p
                          v-if="errors[`variants.${index}.stock`]"
                          class="text-red-500 text-xs italic mt-1"
                        >
                          {{ errors[`variants.${index}.stock`][0] }}
                        </p>
                      </td>
                      <td
                        class="py-3 px-4 whitespace-nowrap text-sm text-gray-700"
                      >
                        <!-- <button
                          type="button"
                          class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                          @click="removeSpecificVariant(index)"
                        >
                          Xóa
                        </button> -->
                        <!-- <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">
                          <label class="inline-flex items-center space-x-2">
                            <input
                              type="checkbox"
                              v-model="variant.active"
                                :true-value="'1'"
                                :false-value="'0'"
                              class="form-checkbox h-4 w-4 text-green-600"
                              :id="'variantActive' + index"
                            />
                            <span>Hoạt động</span>
                          </label>
                        </td> -->
                        <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">
                          <label class="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              class="sr-only peer"
                              v-model="variant.active"
                              :true-value="1"
                              :false-value="0"
                            />

                            <div
                              class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition-colors"
                            ></div>
                            <div
                              class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"
                            ></div>
                          </label>
                        </td>
                        
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p
                v-else
                class="text-gray-500 text-base italic mt-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-md"
              >
                <i class="fas fa-info-circle mr-2"></i> Chưa có biến thể nào
                được tạo. Vui lòng chọn thuộc tính và giá trị ở trên để tạo biến
                thể.
              </p>
            </div>

            <div class="flex justify-start space-x-4 mt-8 col-span-full">
              <button
                type="submit"
                class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
              >
                Cập nhật Sản phẩm
              </button>
              <router-link
                :to="{ name: 'products' }"
                class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Quay lại
              </router-link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch, computed } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import router from "@/router";
import Swal from "sweetalert2";

import ScentGroupSelector from "@/components/admin/product/ScentGroupSelector.vue";
import UsageProfile from "@/components/admin/product/UsageProfile.vue";

const route = useRoute();
const productId = ref(null);

// Reactive state variables
const categories = ref([]);
const brands = ref([]);
const attributes = ref([]); // Stores all attributes with their nested values
const newGalleryImages = ref([]); // Lưu trữ các File object của ảnh mới upload
const galleryImagesToDelete = ref([]); // Lưu trữ IDs của các ảnh cũ muốn xóa
const allScentGroups = ref([]);
let unwatchVariants = null;
const updateVariants = () => {
  // Gọi hàm generateVariants đã được sửa đổi
  generateVariants();
};
// Product data structure with default values
const product = ref({
  name: "",
  slug: "",
  image: null,
  description: "",
  gender: "",
  price: null,
  stock: null,
  category_id: "",
  brand_id: "",
  has_variants: false,
  scentGroups: {
    selectedScentGroupIds: [],
    scentGroupsData: {},
  },
  usageProfile: {
    spring_percent: 0,
    summer_percent: 0,
    autumn_percent: 0,
    winter_percent: 0,
    suitable_day: 0,
    suitable_night: 0,
    longevity_hours: 0.0,
    sillage_range_m: "",
  },
  variants: [],
  gallery_images: [],
});

// Form-related state
const imageFile = ref(null);
const currentImageUrl = ref("");
const removeMainImage = ref(false);
const errors = ref({});
const props = defineProps({
  usageProfileData: {
    type: Object,
    default: () => ({
      spring_percent: 0,
      summer_percent: 0,
      autumn_percent: 0,
      winter_percent: 0,
      suitable_day: 0,
      suitable_night: 0,
      longevity_hours: 0.0,
      sillage_range_m: "",
    }),
  },
});
const localUsageProfile = ref({ ...props.usageProfileData });

// --- CÁC BIẾN LIÊN QUAN ĐẾN BIẾN THỂ ---
const selectedAttributeValues = ref({});

// Watcher for product.name to auto-generate slug
watch(
  () => product.value.name,
  (newName) => {
    product.value.slug = generateSlug(newName);
  }
);

// Watcher cho `has_variants` để quản lý giao diện
watch(
  () => product.value.has_variants,
  (newVal) => {
    if (newVal === true) {
      product.value.price = "";
      product.value.stock = "";
      generateVariants();
    } else {
      product.value.variants.forEach((variant) => {
        if (variant.imageFile) URL.revokeObjectURL(variant.imageFile);
      });
      product.value.variants = [];
      for (const attrId in selectedAttributeValues.value) {
        selectedAttributeValues.value[attrId] = [];
      }
    }
  }
);

// Watcher for selectedAttributeValues
watch(
  selectedAttributeValues,
  () => {
    generateVariants();
  },
  { deep: true }
);

// --- Image Handling ---

const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath instanceof File) {
    return URL.createObjectURL(imagePath);
  }
  if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
    return imagePath;
  }
  return `http://localhost:8000/storage/${imagePath}`;
};

const onFileChangeMainImage = (e) => {
  const file = e.target.files[0];
  if (file) {
    imageFile.value = file;
    currentImageUrl.value = URL.createObjectURL(file);
    removeMainImage.value = false;
  } else {
    imageFile.value = null;
    currentImageUrl.value = product.value.image
      ? getImageUrl(product.value.image)
      : null;
  }
};

watch(removeMainImage, (newValue) => {
  if (newValue) {
    imageFile.value = null;
    currentImageUrl.value = null;
  } else {
    if (product.value.image && !imageFile.value) {
      currentImageUrl.value = getImageUrl(product.value.image);
    }
  }
});

const onGalleryFilesChange = (e) => {
  const files = Array.from(e.target.files);
  files.forEach((file) => {
    newGalleryImages.value.push(file);
    product.value.gallery_images.push({
      id: -Date.now() - newGalleryImages.value.length,
      path: file,
      isNew: true,
      order: product.value.gallery_images.length + 1,
    });
  });
  e.target.value = "";
};

const removeGalleryImage = (imageToRemove) => {
  Swal.fire({
    title: "Bạn có chắc chắn?",
    text: "Ảnh này sẽ bị xóa khỏi thư viện!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Có, xóa nó đi!",
    cancelButtonText: "Hủy",
  }).then((result) => {
    if (result.isConfirmed) {
      product.value.gallery_images = product.value.gallery_images.filter(
        (img) => {
          if (img.id === imageToRemove.id) {
            if (!img.isNew) {
              galleryImagesToDelete.value.push(img.id);
            }
            return false;
          }
          return true;
        }
      );
      updateGalleryImageOrder();
      Swal.fire(
        "Đã xóa!",
        "Ảnh đã được đánh dấu để xóa khi lưu sản phẩm.",
        "success"
      );
    }
  });
};

const updateGalleryImageOrder = () => {
  product.value.gallery_images.forEach((image, index) => {
    image.order = index + 1;
  });
};

// --- Utility Functions ---

const generateSlug = (text) => {
  if (!text) return "";
  return text
    .toString()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .trim()
    .replace(/\s+/g, "-")
    .replace(/[^\w-]+/g, "")
    .replace(/--+/g, "-");
};

const getUniqueAttributeValues = (variants) => {
  const allAttributeValues = variants.flatMap((v) => v.attributes);
  const uniqueValues = allAttributeValues.reduce((acc, current) => {
    if (!acc.some((item) => item.value_id === current.value_id)) {
      acc.push(current);
    }
    return acc;
  }, []);
  return uniqueValues;
};

// Hàm populateSelectedAttributeValues đã được cập nhật để khớp với template mới
const populateSelectedAttributeValues = (variants) => {
  // Tạo một mảng duy nhất chứa tất cả các giá trị thuộc tính (ID) từ các biến thể
  const allAttributeValueIds = variants.flatMap((v) =>
    v.attributes.map((attr) => attr.value_id)
  );
  const uniqueValueIds = [...new Set(allAttributeValueIds)];

  // Khởi tạo lại selectedAttributeValues
  const newSelectedAttributeValues = {};
  for (const attr of attributes.value) {
    newSelectedAttributeValues[attr.id] = [];
  }

  // Gán các ID đã chọn vào đúng mảng thuộc tính
  uniqueValueIds.forEach((valueId) => {
    const attribute = attributes.value.find((attr) =>
      attr.attribute_values.some((val) => val.id === valueId)
    );
    if (attribute) {
      newSelectedAttributeValues[attribute.id].push(valueId);
    }
  });

  selectedAttributeValues.value = newSelectedAttributeValues;
};

// --- Data Fetching ---

const fetchCategory = async () => {
  try {
    const response = await axios.get(
      "http://localhost:8000/api/admin/categories"
    );
    categories.value = response.data.data;
  } catch (error) {
    console.error("Lỗi khi lấy danh mục:", error);
  }
};

const fetchBrand = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/brands");
    brands.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy thương hiệu:", error);
  }
};

const fetchAttributes = async () => {
  try {
    const response = await axios.get(
      "http://localhost:8000/api/admin/attributes"
    );
    // Sửa lỗi ở đây: truy cập vào `response.data.data`
    attributes.value = response.data.data;
    // Khởi tạo selectedAttributeValues với các key từ ID thuộc tính
    attributes.value.forEach((attr) => {
      selectedAttributeValues.value[attr.id] = [];
    });
  } catch (error) {
    console.error("Lỗi khi lấy thuộc tính:", error);
    // Thêm một giá trị mặc định để tránh lỗi
    attributes.value = [];
  }
};

const fetchAllScentGroups = async () => {
  try {
    const response = await axios.get(
      "http://localhost:8000/api/admin/scent-groups"
    );
    allScentGroups.value = response.data;
  } catch (error) {
    console.error("Lỗi khi lấy nhóm hương:", error);
  }
};

const fetchProduct = async () => {
  try {
    const { data } = await axios.get(
      `http://localhost:8000/api/admin/products/${productId.value}`
    );
    const fetchedProduct = data.data;

    // Populate main product fields
    product.value.name = fetchedProduct.name;
    product.value.slug = fetchedProduct.slug;
    product.value.description = fetchedProduct.description;
    product.value.gender = fetchedProduct.gender;
    product.value.category_id = fetchedProduct.category_id;
    product.value.brand_id = fetchedProduct.brand_id;

    // Gán giá trị `has_variants` và các trường khác
    product.value.has_variants = !!fetchedProduct.has_variants;
    if (!product.value.has_variants) {
      product.value.price = fetchedProduct.price;
      product.value.stock = fetchedProduct.stock;
    }

    // Handle scent groups
    if (fetchedProduct.scent_profiles) {
      product.value.scentGroups.selectedScentGroupIds =
        fetchedProduct.scent_profiles.map((sp) => sp.scent_group_id);
      product.value.scentGroups.scentGroupsData =
        fetchedProduct.scent_profiles.reduce((acc, sp) => {
          acc[sp.scent_group_id] = { strength: sp.strength || 50 };
          return acc;
        }, {});
    }

    // Handle usage profile
    if (fetchedProduct.usage_profile) {
      product.value.usageProfile = fetchedProduct.usage_profile;
    }

    // Handle images
    product.value.image = fetchedProduct.image;
    currentImageUrl.value = getImageUrl(fetchedProduct.image);
    if (fetchedProduct.images && Array.isArray(fetchedProduct.images)) {
      product.value.gallery_images = fetchedProduct.images.map(
        (url, index) => ({
          // Tạo một ID tạm thời cho ảnh cũ để quản lý trong giao diện
          id: `old-${index}-${Date.now()}`,
          // Gán trực tiếp URL làm đường dẫn
          path: url,
          isNew: false,
          order: index + 1,
        })
      );
    }

    // Populate variants and attributes if they exist
    if (
      product.value.has_variants &&
      fetchedProduct.variants &&
      fetchedProduct.variants.length > 0
    ) {
      // Bước 1: Điền dữ liệu biến thể đã có vào form.
      product.value.variants = fetchedProduct.variants.map((variant) => {
        return {
          id: variant.id,
          sku: variant.sku,
          price: parseFloat(variant.price),
          stock: parseInt(variant.stock),
          sold: variant.sold,
          status: variant.status,
          barcode: variant.barcode,
          description: variant.description,
          active: Number(variant.active), // <--- Ép kiểu từ string sang số
          attributes: variant.attributes, // Giữ nguyên mảng attributes
        };
      });

      // Bước 2: Dựa vào các biến thể đã có, chúng ta sẽ tự động tích các checkbox thuộc tính
      // và giá trị tương ứng để form hiển thị đúng.
      // Gọi hàm populateSelectedAttributeValues với dữ liệu biến thể đã có.
      populateSelectedAttributeValues(fetchedProduct.variants);
    } else {
      // Đảm bảo variants là mảng rỗng nếu không có biến thể
      product.value.variants = [];
    }
  } catch (error) {
    console.error("Lỗi khi lấy sản phẩm:", error);
    Swal.fire({
      title: "Lỗi!",
      text:
        "Lỗi xảy ra khi lấy sản phẩm: " +
        (error.response?.data?.message || error.message),
      icon: "error",
      confirmButtonText: "Đóng",
    });
    router.push("/admin/products");
  }
};

const generateVariants = () => {
  // Bước 1: Lấy các ID giá trị thuộc tính đã được chọn
  // Lấy ra các mảng ID giá trị của từng thuộc tính
  const activeAttributeValueIdGroups = Object.values(
    selectedAttributeValues.value
  ).filter((group) => group.length > 0);

  // Nếu không có giá trị nào được chọn, xóa hết biến thể
  if (activeAttributeValueIdGroups.length === 0) {
    product.value.variants = [];
    return;
  }

  // Bước 2: Tạo tất cả các tổ hợp (combinations) có thể có của các ID đã chọn
  const combinations = activeAttributeValueIdGroups.reduce(
    (acc, currentGroup) => {
      if (acc.length === 0) return currentGroup.map((val) => [val]);
      const newCombinations = [];
      acc.forEach((prevCombination) => {
        currentGroup.forEach((currentVal) => {
          newCombinations.push([...prevCombination, currentVal]);
        });
      });
      return newCombinations;
    },
    []
  );

  // Bước 3: Tạo các biến thể mới từ các tổ hợp ID
  const newVariants = combinations.map((combination) => {
    // Lấy thông tin đầy đủ của thuộc tính từ ID
    const attribute_values = combination.map((valueId) => {
      let foundValue = null;
      let foundAttribute = null;

      // Tìm giá trị thuộc tính và thuộc tính cha của nó
      for (const attr of attributes.value) {
        foundValue = attr.attribute_values.find((val) => val.id === valueId);
        if (foundValue) {
          foundAttribute = attr;
          break;
        }
      }

      // Trả về một đối tượng có đầy đủ thông tin
      return {
        attribute_id: foundAttribute.id,
        value_id: foundValue.id,
        value_name: foundValue.value,
        attribute_name: foundAttribute.name,
      };
    });

    // Bước 4: So sánh với các biến thể cũ để giữ lại dữ liệu
    const existingVariant = product.value.variants.find((v) => {
      const existingAttrIds = v.attributes.map((attr) => attr.value_id).sort();
      const newAttrIds = attribute_values.map((attr) => attr.value_id).sort();

      // So sánh các mảng ID để tìm biến thể khớp
      if (existingAttrIds.length !== newAttrIds.length) {
        return false;
      }
      return existingAttrIds.every((id, index) => id === newAttrIds[index]);
    });

    // Bước 5: Tạo đối tượng biến thể cuối cùng
    return {
      id: existingVariant ? existingVariant.id : null,
      sku: existingVariant ? existingVariant.sku : "",
      price: existingVariant ? existingVariant.price : null,
      stock: existingVariant ? existingVariant.stock : null,
      sold: existingVariant ? existingVariant.sold : 0,
      status: existingVariant ? existingVariant.status : "available",
      barcode: existingVariant ? existingVariant.barcode : "",
      description: existingVariant ? existingVariant.description : "",
      attributes: attribute_values,
      active: existingVariant ? existingVariant.active : 0, // <- thêm dòng này
    };
  });

  // Cập nhật mảng variants
  product.value.variants = newVariants;
};

const activeVariants = computed(() => {
  return product.value.variants.filter(v => v.active === 1);
});


watch(
  () => props.usageProfileData,
  (newVal) => {
    // Only update if the prop changes to avoid infinite loops
    if (JSON.stringify(newVal) !== JSON.stringify(localUsageProfile.value)) {
      // Map the new values and ensure longevity_hours is a number
      localUsageProfile.value = {
        ...newVal,
        longevity_hours: parseFloat(newVal.longevity_hours) || 0,
      };
    }
  },
  { deep: true, immediate: true } // Thêm immediate: true để chạy watcher ngay lần đầu tiên
);

// Bổ sung hàm này để đảm bảo giá trị là số khi emit
const emitUpdate = () => {
  // Ép kiểu longevity_hours về number trước khi emit
  const dataToEmit = {
    ...localUsageProfile.value,
    longevity_hours: parseFloat(localUsageProfile.value.longevity_hours) || 0,
  };
  emit("update:usageProfileData", dataToEmit);
};

//update

// Khởi tạo selectedAttributeValues khi load product
// Khởi tạo selectedAttributeValues từ product khi load
const existingGalleryImages = ref([]); // ảnh gallery hiện có (từ API)

const loadProductForEdit = async () => {
  try {
    const { data } = await axios.get(
      `http://localhost:8000/api/admin/products/${productId.value}`
    );

    // Gán product
    product.value = {
      ...data,
      variants: data.variants.map((v) => ({
        ...v,
        attributes: v.attributes || [],
        imageFile: null,
        imageUrlPreview: v.image || null,
      })),
      gallery_images: data.gallery_images || [],
      // Nhóm hương chuẩn: luôn là mảng object {id, name, strength}
      scentGroups: {
        selectedScentGroupIds: (data.scent_groups || []).map((g) => g.id),
        scentGroupsData: Object.fromEntries(
          (data.scent_groups || []).map((g) => [
            g.id,
            { strength: g.strength ?? 50 },
          ])
        ),
      },
    };

    // Gán ảnh gallery hiện có
    existingGalleryImages.value = (data.gallery_images || []).map((img) => ({
      id: img.id,
      path: img.path,
      order: img.order ?? 0,
    }));

    // Khởi tạo selectedAttributeValues từ variants
    initSelectedAttributeValues();
  } catch (error) {
    console.error("Lỗi load product:", error);
    Swal.fire({
      icon: "error",
      title: "Lỗi!",
      text: "Không thể load sản phẩm để sửa.",
    });
  }
};

const initSelectedAttributeValues = () => {
  selectedAttributeValues.value = {};
  if (product.value.has_variants && product.value.variants.length > 0) {
    product.value.variants.forEach((variant) => {
      variant.attributes.forEach((attr) => {
        if (!selectedAttributeValues.value[attr.attribute_id]) {
          selectedAttributeValues.value[attr.attribute_id] = [];
        }
        if (
          attr.value_id &&
          !selectedAttributeValues.value[attr.attribute_id].includes(
            attr.value_id
          )
        ) {
          selectedAttributeValues.value[attr.attribute_id].push(attr.value_id);
        }
      });
    });
  }
};

const updateProduct = async () => {
  errors.value = {};
  try {
    const formData = new FormData();

    // Append các trường cơ bản
    for (const key in product.value) {
      if (key === "has_variants") {
        formData.append("has_variants", product.value.has_variants ? 1 : 0);
      } else if (
        key !== "slug" &&
        key !== "variants" &&
        key !== "gallery_images" &&
        product.value[key] != null
      ) {
        formData.append(key, product.value[key]);
      }
    }

    // Ảnh chính
    // if (imageFile.value && imageFile.value instanceof File) {
    //   formData.append("image", imageFile.value);
    // } else if (removeMainImage.value) {
    //   formData.append("remove_main_image", true);
    // }

    if (imageFile.value && imageFile.value instanceof File) {
      formData.append("image", imageFile.value);
    } else if (removeMainImage.value) {
      formData.append("remove_main_image", true);
    } else {
      // Do not append image or remove_main_image if no change is intended
      // The server will retain the existing image
    }

    // Ảnh gallery
    newGalleryImages.value.forEach((file, index) => {
      formData.append(`new_additional_images[${index}]`, file);
    });

    // Xoá ảnh gallery
    if (galleryImagesToDelete.value.length > 0) {
      galleryImagesToDelete.value.forEach((id) => {
        const numericId = Number(id);
        if (Number.isInteger(numericId)) {
          formData.append("deleted_image_ids[]", numericId);
        } else {
          console.warn("⚠️ ID không hợp lệ:", id);
        }
      });
    }

    // Cập nhật order ảnh cũ
    const existingGalleryImagesData = existingGalleryImages.value.map(
      (img, index) => ({
        id: img.id,
        order: img.order ?? index,
      })
    );
    if (existingGalleryImagesData.length > 0) {
      formData.append(
        "gallery_images_order",
        JSON.stringify(existingGalleryImagesData)
      );
    }

    // Nhóm hương (id + strength)
    const scentGroupSyncData = (
      product.value.scentGroups.selectedScentGroupIds || []
    ).map((id) => ({
      id: id,
      strength: product.value.scentGroups.scentGroupsData[id]?.strength ?? 50,
    }));
    console.log("🚀 scentGroupSyncData object:", scentGroupSyncData);
    formData.set("scent_groups", JSON.stringify(scentGroupSyncData));

    //usageProfile
    if (product.value.usageProfile) {
      Object.keys(product.value.usageProfile).forEach((key) => {
        formData.append(
          `usage_profile[${key}]`,
          product.value.usageProfile[key]
        );
      });
    }

    // Variants
    if (product.value.has_variants && product.value.variants.length > 0) {
      const variantsData = product.value.variants.map((v) => ({
        id: v.id || null, // giữ id để API update, nếu null là thêm mới
        sku: v.sku || "",
        price: v.price || 0,
        stock: v.stock || 0,
        active: v.active == 1 ? true : false, // chuyển thành boolean cho API
        attribute_values: v.attributes.map((attr) => attr.value_id),
      }));
      formData.append("variants", JSON.stringify(variantsData));
    }

    formData.append("_method", "PUT");

    await axios.post(
      `http://localhost:8000/api/admin/products/${productId.value}`,
      formData
    );

    Swal.fire({
      icon: "success",
      title: "Cập nhật thành công!",
      text: "Sản phẩm đã được cập nhật.",
    }).then(() => router.push("/admin/products"));
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
      const errorMessages = Object.values(errors.value).flat();
      Swal.fire({
        icon: "error",
        title: "Lỗi Validation",
        html:
          "<ul>" +
          errorMessages.map((msg) => `<li>${msg}</li>`).join("") +
          "</ul>",
      });
    } else {
      console.error(error);
      Swal.fire({
        icon: "error",
        title: "Lỗi",
        text: error.response?.data?.message || error.message,
      });
    }
  }
};

// --- Computed property for sortedScentProfiles ---
const sortedScentProfiles = computed(() => {
  const selectedIds = product.value.scentGroups?.selectedScentGroupIds || [];
  const scentData = product.value.scentGroups?.scentGroupsData || {};
  const allGroups = allScentGroups.value || [];

  // Nếu không có dữ liệu, trả về mảng rỗng
  if (selectedIds.length === 0 || allGroups.length === 0) {
    return [];
  }

  const profiles = selectedIds
    .map((id) => {
      const groupInfo = allGroups.find((group) => group.id === id);
      const strength = scentData[id]?.strength || 0;

      if (groupInfo) {
        return {
          scent_group_id: groupInfo.id,
          scent_group_name: groupInfo.name,
          scent_group_color_code: groupInfo.color_code,
          strength: strength,
        };
      }
      return null;
    })
    .filter((profile) => profile !== null); // Lọc bỏ các giá trị null

  // Sắp xếp giảm dần theo độ mạnh (strength)
  return profiles.sort((a, b) => b.strength - a.strength);
});

// --- Function to check if a color is dark ---
const isDarkColor = (hexColor) => {
  if (!hexColor || hexColor.length < 7) {
    return true;
  }
  const r = parseInt(hexColor.substring(1, 3), 16);
  const g = parseInt(hexColor.substring(3, 5), 16);
  const b = parseInt(hexColor.substring(5, 7), 16);
  const brightness = (r * 299 + g * 587 + b * 114) / 1000;
  return brightness < 128;
};

// --- Lifecycle Hook ---
onMounted(async () => {
  productId.value = route.params.id;
  await fetchAttributes();
  await fetchAllScentGroups();
  await fetchCategory();
  await fetchBrand();
  if (productId.value) {
    await fetchProduct();
  }
  unwatchVariants = watch(selectedAttributeValues, updateVariants, {
    deep: true,
  });
});
</script>

<style scoped>
.custom-hover-link:hover {
  color: white !important;
}

.form-check {
  margin-right: 1.5rem;
  /* Add some space between radio buttons */
}
</style>
