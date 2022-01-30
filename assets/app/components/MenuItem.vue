<template>
  <router-link :to="to"
               active-class="px-4 py-2 text-gray-100 hover:text-gray-100 rounded bg-indigo-600 hover:bg-indigo-600"
               class="px-4 py-2 text-gray-600 rounded hover:bg-gray-200 hover:text-gray-700 transition-colors duration-300">
    <slot/>
  </router-link>
</template>

<script>
export default {
  name: "MenuItem",
  props: {
    to: {},
  },
}
</script>

<style scoped>

</style>
