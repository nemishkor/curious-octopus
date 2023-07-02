<template>
  <div class="container mx-auto">Home</div>
</template>

<script>
export default {
  name: "Home",
  computed: {
    isAuthorized() {
      return this.$store.state.user !== null
    },
  },
  created: function () {
    if (!this.isAuthorized) {
      this.$router.replace({name: 'login'})
    }
  }
}
</script>

<style scoped>

</style>
