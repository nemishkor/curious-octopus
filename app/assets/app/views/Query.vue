<template>
  <div>
    <VueHeader title="Create new query"/>
    <Panel>
      <label class="block mb-4">
        Query string<br>
        <textarea v-model="query.string" class="rounded border border-gray-300 py-1 px-2 max-w-full w-full"/>
      </label>
      <VueButton :onClick="submit" label="Create and run"/>
      <Loader v-if="loading"/>
      <SomethingWentWrongMessage v-if="error"/>
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

export default {
  name: "Query",
  components: {VueInput, Panel, VueButton, VueHeader, SomethingWentWrongMessage, Violations, Loader},
  data: function () {
    return {
      query: {
        string: '',
      },
      error: false,
      loading: false,
      violations: [],
    }
  },
  computed: {
    isAuthorized() {
      return this.$store.state.user !== null
    },
  },
  created: function () {
    if (!this.isAuthorized) {
      this.$router.replace({name: 'login'})
    }
  },
  methods: {
    submit() {
      this.error = false
      this.loading = true
      this.violations = []
      fetch(
          `${process.env.API_URL}api/queries`,
          {
            method: 'POST',
            headers: {'X-API-TOKEN': this.$store.getters.token},
            body: JSON.stringify({
              string: this.query.string,
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
                  this.$router.push({name: 'queries', params: {page: 1}})
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
    }
  }
}
</script>

<style scoped>

</style>
