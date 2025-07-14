<template>
    <div class="card p-3 mb-4 border">
        <div class="card-title mb-3 h5">Cấu hình sử dụng sản phẩm</div>

        <div class="row mb-3 align-items-center">
            <label class="col-sm-3 col-form-label">Mức độ phù hợp mùa</label>
            <div class="col-sm-9">
                <div class="level-item mb-3" v-for="(season, key) in seasons" :key="key">
                    <label :for="key" class="form-label d-block text-capitalize mb-1">{{ season.label }}:</label>
                    <div class="d-flex align-items-center">
                        <input type="range" class="form-range flex-grow-1 me-3" :id="key" min="0" max="100"
                            v-model.number="localUsageProfile[key]"
                            @input="emitUpdate" /> <span class="percentage-display me-3" :style="{ color: season.color }">
                            {{ localUsageProfile[key] }}%
                        </span>
                        <div class="level-bar-container">
                            <div class="level-bar" :style="{ width: localUsageProfile[key] + '%', backgroundColor: season.color }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3 align-items-center">
            <label class="col-sm-3 col-form-label">Mức độ phù hợp ngày/đêm</label>
            <div class="col-sm-9">
                <div class="level-item mb-3">
                    <label for="suitable_day" class="form-label d-block mb-1">Ngày:</label>
                    <div class="d-flex align-items-center">
                        <input type="range" class="form-range flex-grow-1 me-3" id="suitable_day" min="0" max="100"
                            v-model.number="localUsageProfile.suitable_day"
                            @input="emitUpdate" /> <span class="percentage-display me-3" style="color: #FFD700;">
                            {{ localUsageProfile.suitable_day }}%
                        </span>
                        <div class="level-bar-container">
                            <div class="level-bar" :style="{ width: localUsageProfile.suitable_day + '%', backgroundColor: '#FFD700' }"></div>
                        </div>
                    </div>
                </div>
                <div class="level-item mb-3">
                    <label for="suitable_night" class="form-label d-block mb-1">Đêm:</label>
                    <div class="d-flex align-items-center">
                        <input type="range" class="form-range flex-grow-1 me-3" id="suitable_night" min="0" max="100"
                            v-model.number="localUsageProfile.suitable_night"
                            @input="emitUpdate" /> <span class="percentage-display me-3" style="color: #4682B4;">
                            {{ localUsageProfile.suitable_night }}%
                        </span>
                        <div class="level-bar-container">
                            <div class="level-bar" :style="{ width: localUsageProfile.suitable_night + '%', backgroundColor: '#4682B4' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <label for="longevity_hours" class="col-sm-3 col-form-label">Độ lưu hương (giờ)</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="longevity_hours" step="0.1" min="0"
                    v-model.number="localUsageProfile.longevity_hours"
                    @input="emitUpdate" /> </div>
        </div>

        <div class="row">
            <label for="sillage_range_m" class="col-sm-3 col-form-label">Độ tỏa hương (m)</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="sillage_range_m"
                    v-model="localUsageProfile.sillage_range_m" placeholder="Ví dụ: 0.5-1m, >2m, Gần, Vừa, Xa"
                    @input="emitUpdate" /> </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

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
            sillage_range_m: '',
        }),
    },
});

const emit = defineEmits(['update:usageProfileData']);

const localUsageProfile = ref({ ...props.usageProfileData });

// This watcher syncs the internal state with the prop from the parent, preventing infinite loops
watch(() => props.usageProfileData, (newVal) => {
    // Perform a deep comparison to only update if content truly differs
    if (JSON.stringify(newVal) !== JSON.stringify(localUsageProfile.value)) {
        localUsageProfile.value = { ...newVal };
    }
}, { deep: true });

// Removed the watch on localUsageProfile that emitted on every change.
// Instead, we will call emitUpdate directly from user interaction events.

const emitUpdate = () => {
    // This function is now called by @input on the form elements
    // when the user directly modifies the data.
    emit('update:usageProfileData', localUsageProfile.value);
};


const seasons = ref({
    spring_percent: { label: 'Xuân', color: '#8BC34A' },
    summer_percent: { label: 'Hạ', color: '#FFEB3B' },
    autumn_percent: { label: 'Thu', color: '#FF9800' },
    winter_percent: { label: 'Đông', color: '#2196F3' }
});
</script>

<style scoped>
/* Your existing styles */
.level-bar-container {
    flex-shrink: 0;
    width: 100px;
    height: 10px;
    background-color: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
}

.level-bar {
    height: 100%;
    transition: width 0.2s ease-in-out;
    border-radius: 5px;
}

.d-flex.align-items-center>.form-range {
    width: auto;
}

.percentage-display {
    font-weight: bold;
    font-size: 0.95rem;
    min-width: 45px;
    text-align: right;
}

/* Custom Range Input Styles */
input[type="range"] {
    -webkit-appearance: none;
    width: 100%;
    height: 8px;
    background: #d3d3d3;
    outline: none;
    opacity: 0.7;
    -webkit-transition: .2s;
    transition: opacity .2s;
    border-radius: 4px;
}

input[type="range"]:hover {
    opacity: 1;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    background: #007bff;
    cursor: pointer;
    border-radius: 50%;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
    margin-top: -6px;
}

input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    background: #007bff;
    cursor: pointer;
    border-radius: 50%;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
}

input[type="range"]::-moz-range-track {
    background: #d3d3d3;
    border-radius: 4px;
    height: 8px;
}

input[type="range"]::-ms-track {
    background: transparent;
    border-color: transparent;
    color: transparent;
    height: 8px;
}

input[type="range"]::-ms-fill-lower {
    background: #d3d3d3;
    border-radius: 4px;
}

input[type="range"]::-ms-fill-upper {
    background: #d3d3d3;
    border-radius: 4px;
}

input[type="range"]::-ms-thumb {
    width: 20px;
    height: 20px;
    background: #007bff;
    cursor: pointer;
    border-radius: 50%;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
    margin-top: 0;
}
</style>