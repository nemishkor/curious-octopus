<template>
  <nav class="bg-white py-2">
    <div class="container px-4 mx-auto md:flex md:items-center">

      <div class="flex justify-between items-center">
        <router-link :to="{ name: 'home' }"
                     class="font-bold text-xl text-indigo-600">Curious octopus
        </router-link>
      </div>

      <div class="flex flex-row ml-auto">
        <div v-if="isAuthorized" class="py-2 px-4">{{ user.email }}</div>
        <VueButton v-if="isAuthorized" :onClick="logout" label="Logout"/>
      </div>
    </div>
  </nav>
</template>

<script>
import VueButton from "./VueButton";

export default {
  name: "Header",
  components: {VueButton},
  computed: {
    isAuthorized() {
      return this.$store.state.user !== null
    },
    user() {
      return this.$store.state.user
    },
  },
  methods: {
    logout: function () {
      this.$store.commit('deleteUser');
      this.login()
    },
    login: function () {
      this.$router.replace({name: 'login'})
    }
  }
}
</script>

<style scoped>

</style>
