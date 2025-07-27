<template>
    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <div class="text-xl font-semibold mb-6 text-gray-800">Cấu hình sử dụng sản phẩm</div>

        <div class="mb-6 pb-6 border-b border-gray-200">
            <label class="block text-gray-700 text-sm font-bold mb-4">Mức độ phù hợp mùa</label>
            <div class="space-y-4">
                <div class="flex items-center" v-for="(season, key) in seasons" :key="key">
                    <label :for="key" class="w-24 text-gray-700 font-medium capitalize flex-shrink-0">{{ season.label }}:</label>
                    <div class="flex-grow flex items-center ml-4">
                        <input type="range" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:-mt-[3px] [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-600 [&::-webkit-slider-thumb]:shadow-sm [&::-webkit-slider-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-blue-600 [&::-moz-range-thumb]:shadow-sm"
                            :id="key" min="0" max="100"
                            v-model.number="localUsageProfile[key]"
                            @input="emitUpdate" />
                        <span class="font-bold text-base w-12 text-right ml-3 flex-shrink-0" :style="{ color: season.color }">
                            {{ localUsageProfile[key] }}%
                        </span>
                        <div class="flex-shrink-0 w-24 h-2 bg-gray-200 rounded-full overflow-hidden ml-4">
                            <div class="h-full rounded-full transition-all duration-200 ease-in-out"
                                :style="{ width: localUsageProfile[key] + '%', backgroundColor: season.color }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6 pb-6 border-b border-gray-200">
            <label class="block text-gray-700 text-sm font-bold mb-4">Mức độ phù hợp ngày/đêm</label>
            <div class="space-y-4">
                <div class="flex items-center">
                    <label for="suitable_day" class="w-24 text-gray-700 font-medium flex-shrink-0">Ngày:</label>
                    <div class="flex-grow flex items-center ml-4">
                        <input type="range" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:-mt-[3px] [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-600 [&::-webkit-slider-thumb]:shadow-sm [&::-webkit-slider-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-blue-600 [&::-moz-range-thumb]:shadow-sm"
                            id="suitable_day" min="0" max="100"
                            v-model.number="localUsageProfile.suitable_day"
                            @input="emitUpdate" />
                        <span class="font-bold text-base w-12 text-right ml-3 flex-shrink-0" style="color: #FFD700;">
                            {{ localUsageProfile.suitable_day }}%
                        </span>
                        <div class="flex-shrink-0 w-24 h-2 bg-gray-200 rounded-full overflow-hidden ml-4">
                            <div class="h-full rounded-full transition-all duration-200 ease-in-out"
                                :style="{ width: localUsageProfile.suitable_day + '%', backgroundColor: '#FFD700' }"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <label for="suitable_night" class="w-24 text-gray-700 font-medium flex-shrink-0">Đêm:</label>
                    <div class="flex-grow flex items-center ml-4">
                        <input type="range" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:-mt-[3px] [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-600 [&::-webkit-slider-thumb]:shadow-sm [&::-webkit-slider-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-blue-600 [&::-moz-range-thumb]:shadow-sm"
                            id="suitable_night" min="0" max="100"
                            v-model.number="localUsageProfile.suitable_night"
                            @input="emitUpdate" />
                        <span class="font-bold text-base w-12 text-right ml-3 flex-shrink-0" style="color: #4682B4;">
                            {{ localUsageProfile.suitable_night }}%
                        </span>
                        <div class="flex-shrink-0 w-24 h-2 bg-gray-200 rounded-full overflow-hidden ml-4">
                            <div class="h-full rounded-full transition-all duration-200 ease-in-out"
                                :style="{ width: localUsageProfile.suitable_night + '%', backgroundColor: '#4682B4' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6 flex items-center">
            <label for="longevity_hours" class="w-48 text-gray-700 font-medium flex-shrink-0">Độ lưu hương (giờ)</label>
            <div class="flex-grow ml-4">
                <input type="number" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="longevity_hours" step="0.1" min="0"
                    v-model.number="localUsageProfile.longevity_hours"
                    @input="emitUpdate" />
            </div>
        </div>

        <div class="flex items-center">
            <label for="sillage_range_m" class="w-48 text-gray-700 font-medium flex-shrink-0">Độ tỏa hương (m)</label>
            <div class="flex-grow ml-4">
                <input type="text" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="sillage_range_m"
                    v-model="localUsageProfile.sillage_range_m" placeholder="Ví dụ: 0.5-1m, >2m, Gần, Vừa, Xa"
                    @input="emitUpdate" />
            </div>
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

const emitUpdate = () => {
    emit('update:usageProfileData', localUsageProfile.value);
};

const seasons = ref({
    spring_percent: { label: 'Xuân', color: '#8BC34A' }, // Light Green
    summer_percent: { label: 'Hạ', color: '#FFEB3B' }, // Yellow
    autumn_percent: { label: 'Thu', color: '#FF9800' }, // Orange
    winter_percent: { label: 'Đông', color: '#2196F3' } // Blue
});
</script>