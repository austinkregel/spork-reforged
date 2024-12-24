<template>
    <div class="w-full relative flex flex-col divide-y divide-stone-700 dark:divide-stone-800">
        <input
            :value="modelValue.name"
            @input="$emit('update:modelValue', {
                ...modelValue,
                name: $event.target.value
            })"
            :disabled="!editableLabel"
            :class="inputClasses(!editableLabel)"
            class="rounded-t-md text-xs leading-loose tracking-wide font-bold py-0 px-4" placeholder="text" type="text"
        />
        <label
            class="flex dark:bg-stone-600 dark:placeholder-stone-300 rounded-b-md"
            v-if="type !== 'object' && type !== 'select'"
            :class="[type === 'checkbox' ? 'pl-4 pt-4': 'p-0']"
        >
            <input
                class="py-2 px-3 block sm:text-sm rounded-b-md"
                :class="inputClasses(disabledInput)"
                :value="modelValue.value"
                @input="$emit('update:modelValue', {
                    ...modelValue,
                    value: $event.target.value
                })"
                v-if="type !== 'textarea'"
                :type="type"
                :disabled="disabledInput"
            />
            <textarea
                class="py-2 px-3 block sm:text-sm rounded-b-md h-20"
                :class="inputClasses(disabledInput)"
                :value="modelValue.value"
                @input="$emit('update:modelValue', {
                    ...modelValue,
                    value: $event.target.value
                })"
                v-else
                :disabled="disabledInput"
            ></textarea>

        </label>

        <SporkSelect
            v-if="type === 'select'"
            :modelValue="modelValue"
            :disabled="disabledInput"
            @update:modelValue="$emit('update:modelValue', $event)"
        >
            <template #options>
            <option v-for="option in options" :key="option">{{ prettyOptionName(option?.name)}}</option>
            </template>
        </SporkSelect>


        <div v-if="errors" class="flex flex-col">
            <div v-for="error in errors" :key="error" class="text-red-500 dark:text-red-400 px-4 text-xs py-1"> {{ error }}</div>
        </div>

        <div class="absolute top-0 right-0 mt-8 mr-10" v-if="modelValue?.name === 'uuid'">
            <button @click="fillUuid" class="border py-0.5 px-1 rounded-lg text-xs tracking-wider font-bold" >
                Fill
            </button>
        </div>

    </div>
</template>

<script setup>
import SporkSelect from "@/Components/Spork/SporkSelect.vue";

const $emit = defineEmits(['update:modelValue']);

const {
  modelValue,
  type,
  autofocus,
  disabledInput,
  editableLabel,
  error,
  options,
} = defineProps({
  modelValue: Object,
  type: String,
  autofocus: Boolean,
  disabledInput: {
    type: Boolean,
    default: () => true,
  },
  editableLabel: {
    type: Boolean,
    default: () => false,
  },
    errors: Array | null,
    options: {
        type: Array,
        default: () => [],
    },
})
// emits: ['update:modelValue'],
const prettyOptionName = (option) => {
  if (typeof option === 'object') {
    return option.en;
  }

  return option;
};
const inputClasses = (disabled) => {
  let baseClasses = [];
  if (type !== 'checkbox') {
    baseClasses.push('w-full');
  }

  baseClasses.push('border', 'p-1');

  if (disabled) {
    baseClasses.push('bg-stone-200', 'border-stone-200', 'dark:border-stone-900', 'dark:bg-slate-800/50', 'dark:placeholder-stone-300')
  } else {
    baseClasses.push('bg-stone-50', 'border-stone-50', 'dark:border-stone-700', 'dark:bg-stone-700', 'dark:placeholder-stone-300')
  }

  return baseClasses;
};

const fillUuid = () => {
    axios.get(route('spork.uuid'))
        .then(({ data }) => {
            $emit('update:modelValue', {
                ...modelValue,
                value: data.uuid
            });
        })
        .catch(error => {
            console.log(error);
        });
}
</script>

