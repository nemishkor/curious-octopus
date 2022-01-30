<template>
  <div v-if="violations.length > 0" class="rounded bg-red-200 border border-red-600 p-4 mt-2">
    <ul>
      <li v-for="(violation, index) in violations" :key="index">
        <span>{{ violation.propertyPath }}: </span>{{ violation.title }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: "Violations",
  props: {
    violations: Array,
    showPropertyPath: true,
  }
}
</script>

<style scoped>

</style>
