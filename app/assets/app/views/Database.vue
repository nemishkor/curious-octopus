<template>
  <div>
    <VueHeader :title="title">
      <VueButton v-if="entity.id !== null" :onClick="deleteEntity" label="Delete" type="danger"/>
      <VueButton v-if="entity.id !== null" :onClick="createNew" label="Create new"/>
    </VueHeader>
    <Panel>
      <label class="block mb-4">
        Host<br>
        <VueInput v-model="entity.host"/>
      </label>
      <label class="block mb-4">
        User<br>
        <VueInput v-model="entity.user"/>
      </label>
      <label class="block mb-4">
        Password<br>
        <VueInput v-model="entity.password"/>
      </label>
      <label class="block mb-4">
        Name<br>
        <VueInput v-model="entity.name"/>
      </label>
      <VueButton :label="submitLabel" :onClick="submit"/>
      <Loader v-if="loading"/>
      <SomethingWentWrongMessage v-if="error"/>
      <Alert v-if="errorMessage !== ''">{{ errorMessage }}</Alert>
      <Violations :violations="violations"/>
    </Panel>
  </div>
</template>

<script>
import Violations from "../components/Violations"
import Loader from '../components/Loader'
import SomethingWentWrongMessage from "../components/SomethingWentWrongMessage";
import VueHeader from "../components/VueHeader";
import VueButton from "../components/VueButton";
import Panel from "../components/Panel";
import VueInput from "../components/VueInput";
import Alert from "../components/Alert";

export default {
  name: "Database",
  components: {
    Alert, VueInput, Panel, VueButton, VueHeader, SomethingWentWrongMessage, Violations, Loader
  },
  data: function () {
    return {
      entity: {
        id: null,
        host: '',
        user: '',
        password: '',
        name: '',
      },
      error: false,
      errorMessage: '',
      loading: false,
      violations: [],
    }
  },
  computed: {
    isAuthorized() {
      return this.$store.state.user !== null
    },
    title() {
      return this.entity.id === null ? 'Create new' : `Edit #${this.entity.id}`
    },
    submitLabel() {
      return this.entity.id === null ? 'Create' : 'Update'
    },
  },
  created: function () {
    if (!this.isAuthorized) {
      this.$router.replace({name: 'login'})
    }
    this.routeChanged()
  },
  watch: {
    '$route': 'routeChanged'
  },
  methods: {
    routeChanged() {
      this.entity.id = null;
      this.entity.host = '';
      this.entity.user = '';
      this.entity.password = '';
      this.entity.name = ''
      if (this.$route.params.id !== 'new') {
        this.entity.id = this.$route.params.id
        this.fetchData()
      }
    },
    createNew() {
      this.$router.push({name: 'database', params: {id: 'new'}})
    },
    fetchData() {
      this.error = false
      this.loading = true
      this.errorMessage = ''
      fetch(
          process.env.API_URL + 'api/databases/' + this.entity.id,
          {method: 'GET', headers: {'X-API-TOKEN': this.$store.getters.token}}
      ).then(
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
                  this.entity.id = data.id
                  this.entity.host = data.host
                  this.entity.user = data.user
                  this.entity.password = '********'
                  this.entity.name = data.name
                },
                (error) => {
                  this.error = true
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
    submit() {
      this.error = false
      this.errorMessage = ''
      this.loading = true
      this.violations = []
      let url = `${process.env.API_URL}api/databases`
      let method = 'POST'
      let successCallback = (data) => {
        this.entity.id = data.id
        this.entity.host = data.host
        this.entity.user = data.user
        this.entity.password = '********'
        this.entity.name = data.name
      }
      if (this.entity.id !== null) {
        url = `${process.env.API_URL}api/databases/${this.entity.id}`
        method = 'PUT'
        successCallback = (data) => {
          this.entity.id = data.id
          this.$router.push({name: 'database', params: {id: data.id}})
        }
      }
      fetch(
          url,
          {
            method: method,
            headers: {'X-API-TOKEN': this.$store.getters.token},
            body: JSON.stringify({
              host: this.entity.host,
              user: this.entity.user,
              password: this.entity.password,
              name: this.entity.name,
            }),
          }
      ).then(
          (response) => {
            this.loading = false
            if (response.status !== 200 && response.status !== 400) {
              this.error = true
              console.error('Invalid response status from API: ' + response.status)
              return
            }
            response.json().then(
                (data) => {
                  if (response.status === 400) {
                    this.violations = data.violations
                    return
                  }
                  successCallback(data)
                },
                (error) => {
                  this.error = true
                  console.error('Invalid response data from API: ' + error)
                }
            )
          },
          (any) => {
            this.loading = false
            this.error = true
            console.error('Error while creating order', any)
          }
      )
    },
    deleteEntity() {
      this.error = false
      this.loading = true
      fetch(
          `${process.env.API_URL}api/databases/${id}`,
          {method: 'GET', headers: {'X-API-TOKEN': this.$store.getters.token}}
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

<style scoped>

</style>
