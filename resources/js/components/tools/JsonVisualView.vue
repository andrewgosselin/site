<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    data: any;
    name?: string;
    depth?: number;
}

const props = withDefaults(defineProps<Props>(), {
    name: '',
    depth: 0
});

const isObject = computed(() => props.data !== null && typeof props.data === 'object');
const isArray = computed(() => Array.isArray(props.data));
const isEmpty = computed(() => isObject.value && Object.keys(props.data || {}).length === 0);

// Generate a color based on depth for visual distinction
const borderColor = computed(() => {
    const colors = [
        'border-purple-200 dark:border-purple-900',
        'border-blue-200 dark:border-blue-900',
        'border-emerald-200 dark:border-emerald-900',
        'border-orange-200 dark:border-orange-900',
        'border-pink-200 dark:border-pink-900'
    ];
    return colors[props.depth % colors.length];
});

const bgColor = computed(() => {
    const colors = [
        'bg-purple-50/50 dark:bg-purple-900/10',
        'bg-blue-50/50 dark:bg-blue-900/10',
        'bg-emerald-50/50 dark:bg-emerald-900/10',
        'bg-orange-50/50 dark:bg-orange-900/10',
        'bg-pink-50/50 dark:bg-pink-900/10'
    ];
    return colors[props.depth % colors.length];
});

const labelColor = computed(() => {
    const colors = [
        'bg-purple-100 dark:bg-purple-900',
        'bg-blue-100 dark:bg-blue-900',
        'bg-emerald-100 dark:bg-emerald-900',
        'bg-orange-100 dark:bg-orange-900',
        'bg-pink-100 dark:bg-pink-900'
    ];
    return colors[props.depth % colors.length];
});
</script>

<template>
    <div class="font-mono text-sm">
        <!-- Object/Array -->
        <template v-if="isObject">
            <div 
                class="border rounded-lg overflow-hidden mb-2"
                :class="[borderColor, bgColor]"
            >
                <!-- Header/Label -->
                <div 
                    v-if="name || isArray"
                    class="px-2 py-1 text-xs font-bold uppercase tracking-wider flex items-center gap-2 border-b"
                    :class="[borderColor, labelColor]"
                >
                    <span v-if="name">{{ name }}</span>
                    <span v-else class="opacity-50">Item</span>
                    <span class="opacity-50 text-[10px] lowercase font-normal">
                        ({{ isArray ? 'Array' : 'Object' }})
                    </span>
                </div>

                <!-- Content -->
                <div class="p-2">
                    <div v-if="isEmpty" class="text-gray-400 italic text-xs">Empty</div>
                    <div v-else class="flex flex-col gap-2">
                        <div v-for="(value, key) in (data as Record<string, any>)" :key="key">
                            <JsonVisualView 
                                :data="value" 
                                :name="isArray ? '' : String(key)"
                                :depth="depth + 1"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Primitive -->
        <template v-else>
            <div class="flex items-start gap-2 p-2 bg-white dark:bg-white/5 border border-gray-100 dark:border-white/5 rounded">
                <span v-if="name" class="font-bold text-gray-500 dark:text-gray-400 shrink-0">{{ name }}:</span>
                <span 
                    class="break-all"
                    :class="{
                        'text-green-600 dark:text-green-400': typeof data === 'string',
                        'text-orange-600 dark:text-orange-400': typeof data === 'number',
                        'text-blue-600 dark:text-blue-400': typeof data === 'boolean',
                        'text-red-500': data === null
                    }"
                >
                    {{ data === null ? 'null' : String(data) }}
                </span>
            </div>
        </template>
    </div>
</template>
