<template>
  <div v-if="isVisible"
       class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 p-4"
       @click.self="closeModal"> <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto relative transform transition-all duration-300 scale-100 opacity-100">
      <div class="p-6">
        <h3 class="text-xl font-semibold mb-4 text-gray-800">Chọn hoặc Thêm Địa chỉ</h3>

        <div class="flex border-b border-gray-200 mb-4">
          <button @click="currentTab = 'existing'"
                  :class="{'border-b-2 border-blue-500 text-blue-600': currentTab === 'existing', 'text-gray-600': currentTab !== 'existing'}"
                  class="py-2 px-4 text-sm font-medium focus:outline-none">
            Địa chỉ có sẵn
          </button>
          <button @click="currentTab = 'new'"
                  :class="{'border-b-2 border-blue-500 text-blue-600': currentTab === 'new', 'text-gray-600': currentTab !== 'new'}"
                  class="py-2 px-4 text-sm font-medium focus:outline-none">
            Địa chỉ mới
          </button>
        </div>

        <div v-if="currentTab === 'existing'">
          <div v-if="addresses.length === 0" class="text-gray-600 text-center py-4">
            Bạn chưa có địa chỉ nào. Vui lòng thêm địa chỉ mới.
          </div>
          <div v-else class="space-y-3 max-h-80 overflow-y-auto pr-2">
            <div v-for="address in addresses" :key="address.id"
                 class="border p-3 rounded-md flex items-center cursor-pointer transition-colors duration-200"
                 :class="{ 'border-blue-500 ring-1 ring-blue-500 bg-blue-50': internalSelectedAddressId === address.id, 'border-gray-200 hover:bg-gray-50': internalSelectedAddressId !== address.id }"
                 @click="internalSelectedAddressId = address.id">
              <input type="radio" :value="address.id" v-model="internalSelectedAddressId" class="form-radio text-blue-600 mr-3">
              <div>
                <p class="font-medium text-gray-800">{{ address.recipient_name }} - {{ address.phone_number }}</p>
                <p class="text-sm text-gray-600">
                  {{ address.address_line }}, {{ address.ward }}, {{ address.district }}, {{ address.province }}
                </p>
                <span v-if="address.is_default" class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full mt-1">Mặc định</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="currentTab === 'new'" class="space-y-4 max-h-80 overflow-y-auto pr-2">
          <form @submit.prevent="saveAddress">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label for="modal_recipient_name" class="block text-gray-700 text-sm font-bold mb-2">Tên người nhận:</label>
                <input type="text" id="modal_recipient_name" v-model="internalNewAddressDetails.recipient_name"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                       required />
              </div>
              <div>
                <label for="modal_phone_number" class="block text-gray-700 text-sm font-bold mb-2">Số điện thoại:</label>
                <input type="text" id="modal_phone_number" v-model="internalNewAddressDetails.phone_number"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                       required />
              </div>
            </div>
            <div class="mb-4">
              <label for="modal_address_line" class="block text-gray-700 text-sm font-bold mb-2">Địa chỉ cụ thể:</label>
              <input type="text" id="modal_address_line" v-model="internalNewAddressDetails.address_line"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                     required />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div>
                <label for="modal_province" class="block text-gray-700 text-sm font-bold mb-2">Tỉnh/Thành phố:</label>
                <select id="modal_province" v-model="selectedProvinceCode" @change="fetchDistricts"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                        required>
                  <option value="">-- Chọn Tỉnh/Thành phố --</option>
                  <option v-for="province in provinces" :key="province.code" :value="province.code">
                    {{ province.name }}
                  </option>
                </select>
              </div>
              <div>
                <label for="modal_district" class="block text-gray-700 text-sm font-bold mb-2">Quận/Huyện:</label>
                <select id="modal_district" v-model="selectedDistrictCode" @change="fetchWards"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                        required :disabled="!selectedProvinceCode">
                  <option value="">-- Chọn Quận/Huyện --</option>
                  <option v-for="district in districts" :key="district.code" :value="district.code">
                    {{ district.name }}
                  </option>
                </select>
              </div>
              <div>
                <label for="modal_ward" class="block text-gray-700 text-sm font-bold mb-2">Phường/Xã:</label>
                <select id="modal_ward" v-model="selectedWardCode"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                        required :disabled="!selectedDistrictCode">
                  <option value="">-- Chọn Phường/Xã --</option>
                  <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                    {{ ward.name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="flex items-center space-x-4 mb-6">
              <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="internalNewAddressDetails.is_default"
                       class="form-checkbox h-5 w-5 text-green-600 rounded" />
                <span class="ml-2 text-gray-700">Đặt làm địa chỉ mặc định</span>
              </label>
            </div>
          </form>
        </div>
      </div>

      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3 rounded-b-lg">
        <button type="button" @click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
          Hủy
        </button>
        <button type="submit" @click.prevent="saveAddress"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
          Xác nhận
        </button>
      </div>

      <button @click="closeModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios'; // Import axios here

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  addresses: {
    type: Array,
    default: () => [],
  },
  selectedAddressId: {
    type: [Number, null],
    default: null,
  },
  newAddressDetails: {
    type: Object,
    default: () => ({
      recipient_name: '',
      phone_number: '',
      address_line: '',
      ward: '',
      district: '',
      province: '',
      is_default: false, // Thêm trường is_default
    }),
  },
  useNewAddressForm: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['update:isVisible', 'addressSelected', 'newAddressAdded']);

const currentTab = ref(props.useNewAddressForm ? 'new' : 'existing');
const internalSelectedAddressId = ref(props.selectedAddressId);
// Make sure to deep copy newAddressDetails for independent manipulation
const internalNewAddressDetails = ref(JSON.parse(JSON.stringify(props.newAddressDetails)));

// State for address dropdowns
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const selectedProvinceCode = ref('');
const selectedDistrictCode = ref('');
const selectedWardCode = ref('');

// --- Address API Functions (MOVED TO TOP) ---
const fetchProvinces = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/provinces');
        provinces.value = response.data;
    } catch (error) {
        console.error("Error fetching provinces:", error);
        provinces.value = [];
        alert('Không thể tải danh sách Tỉnh/Thành phố. Vui lòng thử lại.');
    }
};

const fetchDistricts = async () => {
    if (!selectedProvinceCode.value) {
        districts.value = [];
        wards.value = [];
        selectedDistrictCode.value = '';
        selectedWardCode.value = '';
        internalNewAddressDetails.value.district = '';
        internalNewAddressDetails.value.ward = '';
        return;
    }
    try {
        const response = await axios.get(`http://localhost:8000/api/provinces/${selectedProvinceCode.value}/districts`);
        districts.value = response.data;
        // Reset district and ward if the previously selected one is not in the new list
        if (!districts.value.some(d => d.code === selectedDistrictCode.value)) {
            selectedDistrictCode.value = '';
            internalNewAddressDetails.value.district = '';
        }
        wards.value = []; // Clear wards when districts change
        selectedWardCode.value = '';
        internalNewAddressDetails.value.ward = '';
    } catch (error) {
        console.error("Error fetching districts:", error);
        districts.value = [];
        selectedDistrictCode.value = '';
        selectedWardCode.value = '';
        internalNewAddressDetails.value.district = '';
        internalNewAddressDetails.value.ward = '';
        alert('Không thể tải danh sách Quận/Huyện. Vui lòng thử lại.');
    }
};

const fetchWards = async () => {
    if (!selectedDistrictCode.value) {
        wards.value = [];
        selectedWardCode.value = '';
        internalNewAddressDetails.value.ward = '';
        return;
    }
    try {
        const response = await axios.get(`http://localhost:8000/api/districts/${selectedDistrictCode.value}/wards`);
        wards.value = response.data;
        // Reset ward if the previously selected one is not in the new list
        if (!wards.value.some(w => w.code === selectedWardCode.value)) {
            selectedWardCode.value = '';
            internalNewAddressDetails.value.ward = '';
        }
    } catch (error) {
        console.error("Error fetching wards:", error);
        wards.value = [];
        selectedWardCode.value = '';
        internalNewAddressDetails.value.ward = '';
        alert('Không thể tải danh sách Phường/Xã. Vui lòng thử lại.');
    }
};


// --- Watchers ---
watch(() => props.selectedAddressId, (newVal) => {
  internalSelectedAddressId.value = newVal;
});

// Update internalNewAddressDetails and dropdowns when newAddressDetails prop changes
watch(() => props.newAddressDetails, (newVal) => {
  internalNewAddressDetails.value = JSON.parse(JSON.stringify(newVal));
  // This block tries to pre-fill dropdowns if newAddressDetails has values
  // It relies on provinces being loaded first
  if (newVal.province && provinces.value.length > 0) {
    const p = provinces.value.find(prov => prov.name === newVal.province);
    if (p) {
      selectedProvinceCode.value = p.code;
      fetchDistricts();
      if (newVal.district) {
        // Use a temporary watcher to ensure districts are loaded before attempting to find district code
        const unwatchDistricts = watch(districts, (newDistricts) => {
          if (newDistricts.length > 0) { // Ensure districts are loaded
            const d = newDistricts.find(dist => dist.name === newVal.district);
            if (d) {
              selectedDistrictCode.value = d.code;
              fetchWards();
              if (newVal.ward) {
                const unwatchWards = watch(wards, (newWards) => {
                  if (newWards.length > 0) { // Ensure wards are loaded
                    const w = newWards.find(ward => ward.name === newVal.ward);
                    if (w) {
                      selectedWardCode.value = w.code;
                    }
                    unwatchWards(); // Stop watching wards
                  }
                }, { immediate: true }); // Run immediately if wards are already loaded
              }
            }
            unwatchDistricts(); // Stop watching districts
          }
        }, { immediate: true }); // Run immediately if districts are already loaded
      }
    }
  } else {
    // Reset dropdowns if new address details are empty or not found
    selectedProvinceCode.value = '';
    selectedDistrictCode.value = '';
    selectedWardCode.value = '';
    districts.value = [];
    wards.value = [];
  }
}, { deep: true, immediate: true }); // immediate: true to run on component mount if newAddressDetails has initial data

watch(() => props.useNewAddressForm, (newVal) => {
  currentTab.value = newVal ? 'new' : 'existing';
  // If switching to new address tab, ensure provinces are loaded
  if (newVal && provinces.value.length === 0) {
    fetchProvinces();
  }
});

// If there are no existing addresses, automatically switch to "New Address" tab
watch(() => props.addresses, (newAddresses) => {
  if (newAddresses.length === 0) {
    currentTab.value = 'new';
    fetchProvinces(); // Make sure provinces are loaded for new address form
  }
}, { immediate: true });

// Watch for changes in dropdowns to update internalNewAddressDetails names
watch(selectedProvinceCode, (newCode) => {
  const province = provinces.value.find(p => p.code === newCode);
  internalNewAddressDetails.value.province = province ? province.name : '';
  internalNewAddressDetails.value.district = ''; // Reset district/ward names
  internalNewAddressDetails.value.ward = '';
  selectedDistrictCode.value = ''; // Reset codes
  selectedWardCode.value = '';
  districts.value = []; // Clear districts list
  wards.value = []; // Clear wards list
});

watch(selectedDistrictCode, (newCode) => {
  const district = districts.value.find(d => d.code === newCode);
  internalNewAddressDetails.value.district = district ? district.name : '';
  internalNewAddressDetails.value.ward = ''; // Reset ward name
  selectedWardCode.value = ''; // Reset code
  wards.value = []; // Clear wards list
});

watch(selectedWardCode, (newCode) => {
  const ward = wards.value.find(w => w.code === newCode);
  internalNewAddressDetails.value.ward = ward ? ward.name : '';
});


// --- Modal Logic ---
const closeModal = () => {
  emit('update:isVisible', false);
};

const saveAddress = () => {
  if (currentTab.value === 'existing') {
    if (internalSelectedAddressId.value === null && props.addresses.length > 0) {
      alert('Vui lòng chọn một địa chỉ.');
      return;
    }
    emit('addressSelected', {
      type: 'existing',
      id: internalSelectedAddressId.value
    });
  } else { // currentTab === 'new'
    const { recipient_name, phone_number, address_line, is_default } = internalNewAddressDetails.value;
    const provinceName = provinces.value.find(p => p.code === selectedProvinceCode.value)?.name;
    const districtName = districts.value.find(d => d.code === selectedDistrictCode.value)?.name;
    const wardName = wards.value.find(w => w.code === selectedWardCode.value)?.name;

    if (!recipient_name || !phone_number || !address_line || !selectedProvinceCode.value || !selectedDistrictCode.value || !selectedWardCode.value) {
      alert('Vui lòng điền đầy đủ thông tin địa chỉ mới.');
      return;
    }

    emit('addressSelected', {
      type: 'new',
      details: {
          recipient_name,
          phone_number,
          address_line,
          ward: wardName,
          district: districtName,
          province: provinceName,
          is_default: is_default || false,
          province_code: selectedProvinceCode.value,
          district_code: selectedDistrictCode.value,
          ward_code: selectedWardCode.value,
      }
    });
  }
  closeModal();
};

// --- Lifecycle Hook ---
// Fetch provinces when the modal is first opened to the 'new' tab
// Or when it becomes visible and current tab is 'new'
onMounted(() => {
    // If the component is mounted and the initial tab is 'new'
    if (currentTab.value === 'new') {
        fetchProvinces();
    }
});

// Watch visibility to fetch provinces if modal becomes visible and is on 'new' tab
watch(() => props.isVisible, (newVal) => {
    if (newVal && currentTab.value === 'new' && provinces.value.length === 0) {
        fetchProvinces();
    }
}, { immediate: true }); // immediate: true to handle initial visibility if already true

</script>

<style scoped>
/* Scoped styles for the modal */@import '@/assets/tailwind.css';

</style>