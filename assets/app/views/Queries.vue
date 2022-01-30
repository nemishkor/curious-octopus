<template>
  <div class="relative">
    <VueHeader title="Queries">
      <VueButton :onClick="createNew" label="Create new"/>
    </VueHeader>
    <Loader v-if="loading"/>
    <SomethingWentWrongMessage v-if="error"/>

    <table class="bg-white rounded-xl mt-4 w-full shadow-md overflow-hidden">
      <thead>
      <tr class="bg-gray-50 border-b border-gray-200">
        <th class="py-2 px-1">
          <span>ID</span>
        </th>
        <th class="py-2 px-1">
          <span>State</span>
        </th>
        <th class="py-2 px-1">
          <span></span>
        </th>
        <th class="py-2 px-1">
          <span>Date creation</span>
        </th>
        <th class="py-2 px-1" colspan="2">
          <span>Progress</span>
        </th>
        <th class="py-2 px-1">
          <span>Download</span>
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="query in items" :key="query.id" class="border-t border-gray-200">
        <td class="py-2.5 text-center">{{ query.id }}</td>
        <td class="py-2.5 px-0.5 text-center">{{ query.state }}</td>
        <td class="py-2.5 px-0.5">{{ query.string.substr(0, 50) }}...</td>
        <td class="py-2.5 px-0.5 text-center">{{ query.created.substr(0, 16).replace('T', ' ') }}</td>
        <td class="py-2.5 px-0.5 text-center">
          <span v-if="Number(query.progress_total) !== 0">{{
              (query.progress_current || 0) * 100 / (query.progress_total || 1)
            }}%</span>
        </td>
        <td class="py-2.5 px-0.5 text-center">
          {{ query.progress_current || 0 }}/{{ query.progress_total || 0 }}
        </td>
        <td class="text-center">
          <VueButton v-if="query.state === 'done'" :onClick="function(){ download(query.id, 'json') }" label="json"
                     size="small"/>
        </td>
      </tr>
      </tbody>
    </table>

    <div class="mt-4 flex flex-auto justify-center">
      <button :class="{
                'border border-black hover:text-black bg-black hover:bg-white text-white': hasPreviousPage,
                'bg-gray-300 text-gray-500 cursor-not-allowed': !hasPreviousPage}"
              class="rounded py-2 px-2 mr-2"
              @click="goToPreviousPage">
        Previous page
      </button>
      <div class="text-lg py-2 px-4">Page: {{ this.$route.params.page }}</div>
      <div class="text-lg py-2 px-4">Limit: {{ this.limit }}</div>
      <div class="text-lg py-2 px-4">Total: {{ this.total }}</div>
      <button :class="{ 'border border-black hover:text-black bg-black hover:bg-white text-white': hasNextPage,
                'bg-gray-300 text-gray-500 cursor-not-allowed': !hasNextPage }"
              class="rounded py-2 px-2 ml-2"
              @click="goToNextPage">
        Next page
      </button>
    </div>
  </div>
</template>

<script>
import Loader from '../components/Loader'
import SomethingWentWrongMessage from "../components/SomethingWentWrongMessage";
import VueHeader from "../components/VueHeader";
import VueButton from "../components/VueButton"

export default {
  name: "Queries",
  components: {VueHeader, VueButton, SomethingWentWrongMessage, Loader},
  data: function () {
    return {
      error: false,
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
        name: 'query',
        params: {id: 'new'}
      });
    },
    fetchData() {
      this.error = false
      this.loading = true
      let url = new URL(`${process.env.API_URL}api/queries`);
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
      this.$router.push({name: 'queries', params: {page: this.$route.params.page - 1}})
    },
    goToNextPage() {
      if (!this.hasNextPage) {
        return
      }
      this.$router.push({name: 'queries', params: {page: this.$route.params.page + 1}})
    },
    download(id, format) {
      let url = new URL(`${process.env.API_URL}api/queries/${id}/download-results`);
      url.searchParams.append('format', format)
      fetch(url, {method: 'GET', headers: {'X-API-TOKEN': this.$store.getters.token}})
          .then(response => response.blob())
          .then(blob => URL.createObjectURL(blob))
          .then(url => {
            window.open(url, '_blank');
            URL.revokeObjectURL(url);
          });
    },
  }
}
</script>

