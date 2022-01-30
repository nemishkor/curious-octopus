<template>
  <div class="relative bg-white border border-gray-100 rounded-md p-6">
    <div v-if="title !== ''" class="text-center text-2xl">{{ title }}</div>
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
