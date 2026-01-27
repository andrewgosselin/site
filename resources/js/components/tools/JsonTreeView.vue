<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
    data: any;
    depth?: number;
    last?: boolean;
    name?: string;
}

const props = withDefaults(defineProps<Props>(), {
    depth: 0,
    last: true,
    name: ''
});

const isObject = computed(() => props.data !== null && typeof props.data === 'object');
const isArray = computed(() => Array.isArray(props.data));
const isEmpty = computed(() => isObject.value && Object.keys(props.data || {}).length === 0);

const isOpen = ref(true);
const toggle = () => {
    isOpen.value = !isOpen.value;
};

const type = computed(() => {
    if (props.data === null) return 'null';
    if (isArray.value) return 'array';
    return typeof props.data;
});

const formatValue = (val: any) => {
    if (val === null) return 'null';
    if (typeof val === 'string') return `"${val}"`;
    return val;
};
</script>

<template>
    <div :style="{ marginLeft: depth === 0 ? '0' : '20px' }" class="font-mono text-sm leading-6">
        <!-- Object/Array -->
        <template v-if="isObject">
            <div class="flex items-start">
                <button 
                    v-if="!isEmpty"
                    @click="toggle"
                    class="mr-1 w-4 h-6 flex items-center justify-center text-gray-400 hover:text-black dark:hover:text-white transition-colors"
                >
                    <i :class="['pi', isOpen ? 'pi-angle-down' : 'pi-angle-right', 'text-xs']"></i>
                </button>
                <span v-else class="mr-1 w-4"></span>

                <div class="flex-1">
                    <span 
                        @click="!isEmpty && toggle()"
                        :class="['cursor-pointer hover:underline decoration-gray-400 decoration-dotted underline-offset-2', !isEmpty ? '' : 'cursor-default no-underline']"
                    >
                        <span v-if="name" class="mr-1 text-purple-600 dark:text-purple-400">"{{ name }}":</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ isArray ? '[' : '{' }}</span>
                        
                        <span v-if="!isOpen" class="text-gray-400 px-1 italic text-xs">...</span>
                        
                        <span class="text-gray-500 dark:text-gray-400">{{ isArray ? ']' : '}' }}<span v-if="!last">,</span></span>
                    </span>

                    <template v-if="isOpen && !isEmpty">
                        <div v-for="(value, key, index) in (data as Record<string, any>)" :key="key">
                            <JsonTreeView 
                                :data="value" 
                                :name="isArray ? '' : String(key)" 
                                :depth="depth + 1"
                                :last="index === Object.keys(data || {}).length - 1"
                            />
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Primitive -->
        <template v-else>
            <div class="flex items-start">
                 <!-- Spacer for alignment with arrows -->
                <span class="mr-1 w-4 h-6"></span>
                <div>
                <span v-if="name" class="mr-1 text-purple-400">"{{ name }}":</span>
                <span 
                    :class="{
                        'text-green-400': type === 'string',
                        'text-orange-400': type === 'number',
                        'text-blue-400': type === 'boolean',
                        'text-red-400': type === 'null'
                    }"
                >
                    {{ formatValue(data) }}
                </span>
                <span v-if="!last" class="text-gray-400">,</span>
            </div>
            </div>
        </template>
    </div>
</template>
