<template>
  <div class="mx-auto container max-w-screen-sm relative">
    <Panel>
      <div class="flex flex-auto flex-col text-center">
        <div class="mb-4">
          <label for="email">Email</label><br>
          <input id="email" v-model="email" class="rounded border border-gray-300 py-1 px-2 max-w-full" type="email">
        </div>
        <div class="mb-4">
          <label for="password">Password</label><br>
          <input id="password" v-model="password" class="rounded border border-gray-300 py-1 px-2" type="password">
        </div>
        <div>
          <VueButton :onClick="login" label="Sign In"/>
          <Loader v-if="loading"/>
          <Alert v-if="error !== ''">{{ error }}</Alert>
        </div>
      </div>
    </Panel>
  </div>
</template>

<script>
import Loader from "../components/Loader";
import VueButton from "../components/VueButton";
import Panel from "../components/Panel"
import Alert from "../components/Alert";

export default {
  name: "Login",
  components: {Alert, VueButton, Loader, Panel},
  data: function () {
    return {
      email: '',
      password: '',
      loading: false,
      error: '',
    }
  },
  created: function () {
    console.log(process.env);
    console.log(process.env.API_URL);
    if (this.isAuthorized) {
      this.$router.replace({name: 'home'})
    }
  },
  computed: {
    isAuthorized() {
      return this.$store.state.user !== null
    },
  },
  methods: {
    login(e) {
      e.preventDefault();
      this.loading = true;
      this.loadingErrors = [];
      let data = new FormData();
      data.append('email', this.email)
      data.append('password', this.password)
      console.log(process.env);
      console.log(process.env.API_URL);
      fetch(
          process.env.API_URL + 'api/token',
          {
            method: 'POST',
            body: data,
          }
      ).then(
          (response) => {
            this.loading = false
            if (response.status !== 200 && response.status !== 401) {
              this.error = 'Oops... Something went wrong. Unexpected response code from server'
              return
            }
            response.json().then(
                (data) => {
                  if (response.status === 401) {
                    this.error = data.message
                    return
                  }
                  this.$store.commit('setUser', {email: this.email, token: data.token})
                  this.$router.replace({name: 'home', params: {page: 1}})
                },
                () => {
                  this.error = 'Oops... Something went wrong. Unexpected response body from server'
                }
            );
          },
          () => {
            this.loading = false
            this.error = 'Oops... Something went wrong. Request to server is failed'
          }
      );
    },
  }
}
</script>

<style scoped>

</style>
