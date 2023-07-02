<template>
  <div class="flex flex-row justify-between pl-6 mb-4">
    <span class="text-2xl">{{ title }}</span>
    <div>
      <slot></slot>
    </div>
  </div>
</template>
<script>

export default {
  name: 'VueHeader',
  props: {
    title: {}
  }
}
</script>
