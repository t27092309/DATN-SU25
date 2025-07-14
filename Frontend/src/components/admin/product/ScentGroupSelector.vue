<template>
    <div class="scent-group-selector">
        <div class="input-group mb-3">
            <select class="form-select" v-model="newScentGroupId" @change="addScentGroup">
                <option value="">Chọn nhóm hương để thêm</option>
                <option v-for="scentGroup in availableScentGroups" :key="scentGroup.id" :value="scentGroup.id">
                    {{ scentGroup.name }}
                </option>
            </select>
        </div>

        <div v-if="selectedScentGroupIdsInternal.length > 0" class="mt-3">
            <label class="form-label">Cấu hình nhóm hương đã chọn:</label>
            <div class="selected-scent-groups-list">
                <div v-for="scentId in selectedScentGroupIdsInternal" :key="scentId"
                    class="card mb-2 p-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="color-box me-3 rounded"
                            :style="{ backgroundColor: getScentGroupColor(scentId), width: '25px', height: '25px' }">
                        </div>
                        <span class="fw-bold me-3">{{ getScentGroupName(scentId) }}</span>
                        <div class="flex-grow-1">
                            <label :for="`strength-${scentId}`" class="form-label mb-0">Độ mạnh:</label>
                            <input type="range" class="form-range" :id="`strength-${scentId}`" min="1" max="100" step="1"
                                v-model.number="scentGroupsDataInternal[scentId].strength"
                                @input="emitUpdates" />
                            <div class="d-flex justify-content-between text-muted small mt-1">
                                <span>1% (Rất nhẹ)</span>
                                <span>25%</span>
                                <span>50%</span>
                                <span>75%</span>
                                <span>100% (Rất mạnh)</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm ms-3" @click="removeScentGroup(scentId)">Xóa</button>
                </div>
            </div>
        </div>
        <p v-else class="text-muted">Chưa có nhóm hương nào được chọn.</p>
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