<template>
  <nav class="bg-white py-2">
    <div class="container px-4 mx-auto md:flex md:items-center">
      <div class="flex justify-between items-center">
        <router-link :to="{ name: 'home' }"
                     class="font-bold text-xl text-indigo-600">Curious octopus
        </router-link>
      </div>
      <div class="flex flex-row ml-auto">
        <MenuItem v-if="isAuthorized"
                  :to="{name: 'databases', params: {page: 1}}"
                  class="ml-4">Databases
        </MenuItem>
        <MenuItem v-if="isAuthorized"
                  :to="{name: 'queries', params: {page: 1}}"
                  class="ml-4">Queries
        </MenuItem>
        <button v-if="isAuthorized"
                class="ml-4 px-4 text-gray-600 rounded hover:bg-gray-200 hover:text-gray-700 transition-colors duration-300"
                @click="logout">Logout
        </button>
        <div v-if="isAuthorized" class="py-2 px-4">{{ user.email }}</div>
      </div>
    </div>
  </nav>
</template>

<script>
import VueButton from "./VueButton";
import MenuItem from "./MenuItem";

export default {
  name: "Header",
  components: {MenuItem, VueButton},
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
      this.$store.commit('deleteUser')
      this.$router.replace({name: 'login'})
    },
  }
}
</script>

<style scoped>

</style>
