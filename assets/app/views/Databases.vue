<template>
  <div class="relative">
    <VueHeader title="Databases">
      <VueButton :onClick="createNew" label="Create new"/>
    </VueHeader>
    <Loader v-if="loading"/>
    <SomethingWentWrongMessage v-if="error"/>
    <Alert v-if="errorMessage !== ''">{{ errorMessage }}</Alert>

    <table class="bg-white rounded-xl mt-4 w-full shadow-md overflow-hidden">
      <thead>
      <tr class="bg-gray-50 border-b border-gray-200">
        <th class="py-2 px-1">
          <span>ID</span>
        </th>
        <th class="py-2 px-1">
          <span>Host</span>
        </th>
        <th class="py-2 px-1">
          <span>User</span>
        </th>
        <th class="py-2 px-1">
          <span>Name</span>
        </th>
        <th class="py-2 px-1">
          <span></span>
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="database in items" :key="database.id" class="border-t border-gray-200">
        <td class="py-2.5 text-center">{{ database.id }}</td>
        <td class="py-2.5 px-0.5 text-center">{{ database.host }}</td>
        <td class="py-2.5 px-0.5 text-center">{{ database.user }}</td>
        <td class="py-2.5 px-0.5 text-center">{{ database.name }}</td>
        <td class="text-center">
          <VueButton :onClick="function(){ edit(database.id) }" label="Edit" size="small"/>
          <VueButton :onClick="function(){ deleteDatabase(database.id) }" label="Delete" size="small" type="danger"/>
        </td>
      </tr>
      </tbody>
    </table>

    <div class="mt-4 flex flex-auto justify-center">
      <VueButton v-if="hasPreviousPage" :onClick="goToPreviousPage" label="Previous page"/>
      <div class="text-lg py-2 px-4">Page: {{ this.$route.params.page }}</div>
      <div class="text-lg py-2 px-4">Limit: {{ this.limit }}</div>
      <div class="text-lg py-2 px-4">Total: {{ this.total }}</div>
      <VueButton v-if="hasNextPage" :onClick="goToNextPage" label="Next page"/>
    </div>
  </div>
</template>

<script>
import Loader from '../components/Loader'
import SomethingWentWrongMessage from "../components/SomethingWentWrongMessage";
import VueHeader from "../components/VueHeader";
import VueButton from "../components/VueButton"
import Alert from "../components/Alert";

export default {
  name: "Databases",
  components: {Alert, VueHeader, VueButton, SomethingWentWrongMessage, Loader},
  data: function () {
    return {
      error: false,
      errorMessage: '',
      loading: false,
      items: [],
      limit: 0,
      total: 0,
    }
  },
  computed: {
    isAuthorized() {
      return this.$store.state.user !== null
    },
    hasPreviousPage: function () {
      return this.$route.params.page > 1
    },
    hasNextPage: function () {
      return this.total > ((this.$route.params.page - 1) * this.limit + this.items.length)
    }
  },
  created: function () {
    if (!this.isAuthorized) {
      this.$router.replace({name: 'login'})
    }
    this.fetchData()
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    createNew() {
      this.$router.replace({
        name: 'database',
        params: {id: 'new'}
      });
    },
    fetchData() {
      this.error = false
      this.loading = true
      let url = new URL(`${process.env.API_URL}api/databases`);
      url.searchParams.append('page', this.$route.params.page)
      fetch(url, {method: 'GET', headers: {'X-API-TOKEN': this.$store.getters.token}}).then(
          (response) => {
            this.loading = false
            if (response.status === 403) {
              this.$store.commit('deleteUser')
              this.$router.replace({name: 'login'})
              return
            }
            if (response.status !== 200) {
              this.error = true
              console.error('Invalid response status from API: ' + response.status)
              return
            }
            response.json().then(
                (data) => {
                  this.items = data.items
                  this.limit = data.limit
                  this.total = data.total
                },
                (error) => {
                  this.error = true;
                  console.error('Invalid response data from API: ' + error)
                }
            )
          },
          (any) => {
            this.loading = false
            this.error = true
            console.error('Request to server is failed', any)
          }
      )
    },
    goToPreviousPage() {
      if (!this.hasPreviousPage) {
        return
      }
      this.$router.push({name: 'databases', params: {page: Number(this.$route.params.page) - 1}})
    },
    goToNextPage() {
      if (!this.hasNextPage) {
        return
      }
      this.$router.push({name: 'databases', params: {page: Number(this.$route.params.page) + 1}})
    },
    edit(id) {
      this.$router.push({name: 'database', params: {id: id}})
    },
    deleteDatabase(id) {
      this.error = false
      this.loading = true
      this.errorMessage = ''
      fetch(
          `${process.env.API_URL}api/databases/${id}`,
          {method: 'DELETE', headers: {'X-API-TOKEN': this.$store.getters.token}}
      ).then(
          (response) => {
            this.loading = false
            if (response.status === 403) {
              this.$store.commit('deleteUser')
              this.$router.replace({name: 'login'})
              return
            }
            if (response.status !== 204 && response.status !== 400) {
              this.error = true
              console.error('Invalid response status from API: ' + response.status)
              return
            }
            if (response.status === 400) {
              response.json().then(
                  (data) => {
                    this.errorMessage = data.title
                  },
                  (error) => {
                    this.error = true
                    console.error('Invalid response data from API with code 400: ' + error)
                  }
              )
              return
            }
            this.fetchData()
          },
          (any) => {
            this.loading = false
            this.error = true
            console.error('Request to server is failed', any)
          }
      );
    },
  }
}
</script>

