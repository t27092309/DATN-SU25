<template>
    <div class="scent-group-selector">
        <div class="mb-4">
            <select class="block appearance-none w-full bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded-md shadow-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" v-model="newScentGroupId" @change="addScentGroup">
                <option value="">Chọn nhóm hương để thêm</option>
                <option v-for="scentGroup in availableScentGroups" :key="scentGroup.id" :value="scentGroup.id">
                    {{ scentGroup.name }}
                </option>
            </select>
        </div>

        <div v-if="selectedScentGroupIdsInternal.length > 0" class="mt-6">
            <label class="block text-gray-700 text-sm font-bold mb-3">Cấu hình nhóm hương đã chọn:</label>
            <div class="space-y-3">
                <div v-for="scentId in selectedScentGroupIdsInternal" :key="scentId"
                    class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-between">
                    <div class="flex items-center flex-grow">
                        <div class="flex items-center flex-none" style="width: 180px;">
                            <div class="w-7 h-7 rounded mr-3"
                                :style="{ backgroundColor: getScentGroupColor(scentId) }">
                            </div>
                            <span class="font-semibold text-gray-800 truncate" :title="getScentGroupName(scentId)">
                                {{ getScentGroupName(scentId) }}
                            </span>
                        </div>
                        
                        <div class="flex-grow ml-4">
                            <label :for="`strength-${scentId}`" class="block text-gray-700 text-xs font-medium mb-1">Độ mạnh:</label>
                            <input type="range" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:-mt-[3px] [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-600 [&::-webkit-slider-thumb]:shadow-sm [&::-webkit-slider-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-blue-600 [&::-moz-range-thumb]:shadow-sm"
                                :id="`strength-${scentId}`" min="1" max="100" step="1"
                                v-model.number="scentGroupsDataInternal[scentId].strength"
                                @input="emitUpdates" />
                            <div class="flex justify-between text-gray-500 text-xs mt-1">
                                <span>1% (Rất nhẹ)</span>
                                <span>25%</span>
                                <span>50%</span>
                                <span>75%</span>
                                <span>100% (Rất mạnh)</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-sm ml-4 shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 flex-none" @click="removeScentGroup(scentId)">
                        Xóa
                    </button>
                </div>
            </div>
        </div>
        <p v-else class="text-gray-500 italic mt-4 p-3 bg-blue-50 border-l-4 border-blue-400 text-blue-800 rounded-md">
            <i class="fas fa-info-circle mr-2"></i> Chưa có nhóm hương nào được chọn.
        </p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
    selectedScentGroupIds: {
        type: Array,
        default: () => []
    },
    scentGroupsData: {
        type: Object,
        default: () => ({})
    },
    allScentGroups: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:selectedScentGroupIds', 'update:scentGroupsData']);

const newScentGroupId = ref('');

// Internal reactive states
const selectedScentGroupIdsInternal = ref([]);
const scentGroupsDataInternal = ref({});

// --- Initialization and Watchers ---

// This function ensures that internal data is correctly structured
const initializeInternalState = () => {
    console.log('ScentGroupSelector: Initializing state with props:', {
        selectedScentGroupIdsProp: props.selectedScentGroupIds,
        scentGroupsDataProp: props.scentGroupsData
    });

    // Cần tạo một bản sao thực sự của mảng IDs
    selectedScentGroupIdsInternal.value = [...props.selectedScentGroupIds];

    // Tạo một bản sao sâu cho scentGroupsData để đảm bảo không bị ảnh hưởng bởi Proxy/tham chiếu
    const newScentGroupsData = {};
    for (const id of selectedScentGroupIdsInternal.value) { // Lặp qua IDs đã được sao chép
        // Đảm bảo sao chép cả object con nếu có (ví dụ: { strength: X })
        newScentGroupsData[id] = { 
            strength: props.scentGroupsData[id]?.strength || 50 
        };
    }
    scentGroupsDataInternal.value = newScentGroupsData;

    console.log('ScentGroupSelector: Internal state after initialization:', {
        selectedScentGroupIdsInternal: selectedScentGroupIdsInternal.value,
        scentGroupsDataInternal: scentGroupsDataInternal.value
    });
};

// Call initialization on mount
onMounted(() => {
    initializeInternalState();
});

// Watch for changes in props and re-initialize if they change
// Using deep watch on the raw props to detect changes from the parent
watch([() => props.selectedScentGroupIds, () => props.scentGroupsData], ([newIds, newData]) => {
    console.log('ScentGroupSelector: Props changed, re-initializing state.');
    // Có thể so sánh sâu hơn để tránh re-init không cần thiết, nhưng với vấn đề hiện tại,
    // cứ re-init luôn để đảm bảo đồng bộ.
    initializeInternalState();
}, { deep: true });

// --- Computed Properties ---

const availableScentGroups = computed(() => {
    return props.allScentGroups.filter(
        sg => !selectedScentGroupIdsInternal.value.includes(sg.id)
    );
});

// --- Methods for User Interaction ---

const addScentGroup = () => {
    if (newScentGroupId.value && !selectedScentGroupIdsInternal.value.includes(newScentGroupId.value)) {
        selectedScentGroupIdsInternal.value.push(newScentGroupId.value);
        scentGroupsDataInternal.value[newScentGroupId.value] = { strength: 50 };
        newScentGroupId.value = ''; // Reset dropdown after adding
        emitUpdates(); // Emit immediately after internal state changes due to user action
    }
};

const removeScentGroup = (idToRemove) => {
    selectedScentGroupIdsInternal.value = selectedScentGroupIdsInternal.value.filter(id => id !== idToRemove);
    delete scentGroupsDataInternal.value[idToRemove];
    emitUpdates(); // Emit immediately after internal state changes due to user action
};

const getScentGroupName = (id) => {
    const group = props.allScentGroups.find(sg => sg.id === id);
    return group ? group.name : `Nhóm hương ID ${id}`;
};

const getScentGroupColor = (id) => {
    const group = props.allScentGroups.find(sg => sg.id === id);
    return group ? group.color_code : '#cccccc';
};

// Emits the updated internal state to the parent
const emitUpdates = () => {
    emit('update:selectedScentGroupIds', selectedScentGroupIdsInternal.value);
    emit('update:scentGroupsData', scentGroupsDataInternal.value);
};
</script>

<style scoped>
/* Your existing styles */
.color-box {
    border: 1px solid #ccc;
}

.scent-strength-bars .scent-bar-item .progress {
    height: 20px;
    background-color: #e9ecef;
}

.scent-strength-bars .scent-bar-item .progress-bar {
    text-align: center;
    color: #fff;
    font-weight: bold;
    line-height: 20px;
}
</style>