<template>
  <input :value="computedValue"
         class="rounded border border-gray-300 py-1 px-2 max-w-full w-full"
         type="text"
         @change="updateValue"
         @input="updateValue"/>
</template>

<script>
export default {
  name: "VueInput",
  props: {value: String},
  data() {
    return {
      newValue: this.value,
    }
  },
  computed: {
    computedValue: {
      get() {
        return this.newValue
      },
      set(value) {
        this.newValue = value
        this.$emit('input', value)
      }
    },
  },
  watch: {
    /**
     * When v-model is changed:
     *   1. Set internal value.
     */
    value(value) {
      this.newValue = value
    }
  },
  methods: {
    updateValue(event) {
      this.computedValue = event.target.value
    },
  },
}
</script>

<style scoped>

</style>
