<template>
  <button :class="{
    'text-white bg-indigo-600 hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-300 border border-transparent hover:border-indigo-700': type === 'default',
    'border-red-800 hover:text-red-600 bg-red-600 hover:bg-white text-white transition-colors duration-300': type === 'danger',
    'border-orange-800 hover:text-orange-600 bg-orange-600 hover:bg-white text-white transition-colors duration-300': type === 'warning',
    'px-1 py-0.5': size === 'small',
    'py-2 px-2': size === 'default'
          }"
          class="py-2 px-4 text-center rounded"
          @click="onClick">{{ label }}
  </button>
</template>
<script>
export default {
  name: 'VueButton',
  props: {
    label: String,
    onClick: Function,
    size: {
      type: String,
      default: 'default'
    },
    type: {
      type: String,
      default: 'default'
    },
  }
}
</script>
