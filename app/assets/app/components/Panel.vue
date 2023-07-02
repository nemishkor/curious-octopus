<template>
  <div class="relative bg-white border border-gray-100 rounded-md pt-4 pb-5 px-6">
    <div v-if="title !== ''" class="text-center text-xl mb-4">{{ title }}</div>
    <slot></slot>
  </div>
</template>

<script>
export default {
  name: "Panel",
  props: {
    title: {
      type: String,
      default: ''
    }
  }
}
</script>

<style scoped>

</style>
