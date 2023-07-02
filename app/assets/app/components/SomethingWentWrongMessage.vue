<template>
  <Alert>
    Something went wrong. Try to <a class="underline" href="#" @click="refreshPage">refresh page</a>
  </Alert>
</template>

<script>
import Alert from "./Alert";

export default {
  name: "SomethingWentWrongMessage",
  components: {Alert},
  methods: {
    refreshPage: function () {
      window.location.reload(false);
    },
  },
}
</script>

<style scoped>

</style>
