<template>
  <div class="mx-auto container max-w-screen-sm relative">
    <Panel title="Login">
      <div class="mb-4">
        <label for="email">Email</label><br>
        <input id="email" v-model="email" class="rounded border border-gray-300 py-1 px-2 max-w-full" type="email">
      </div>
      <div class="mb-4">
        <label for="password">Password</label><br>
        <input id="password" v-model="password" class="rounded border border-gray-300 py-1 px-2" type="password">
      </div>
      <VueButton :onClick="login" label="Sign In"/>
      <Loader v-if="loading"/>
      <ul v-if="loadingErrors.length > 0" class="rounded bg-red-200 border border-red-600 p-4 mt-2">
        <li v-for="error in loadingErrors" :key="error">{{ error }}</li>
      </ul>
    </Panel>
  </div>
</template>

<script>
import Loader from "../components/Loader";
import VueButton from "../components/VueButton";
import Panel from "../components/Panel"

export default {
  name: "Login",
  components: {VueButton, Loader, Panel},
  data: function () {
    return {
      email: '',
      password: '',
      loading: false,
      loadingErrors: [],
    }
  },
  created: function () {
    if (this.isAuthorized) {
      this.$router.replace({
        name: 'queries',
      });
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

      fetch(
          process.env.API_URL + 'api/token',
          {
            method: 'POST',
            body: data,
          }
      ).then(
          (response) => {
            if (response.status !== 200 && response.status !== 401) {
              this.loading = false;
              this.loadingErrors.push('Oops... Something went wrong');
              console.error('Invalid response status from API: ' + response.status);
              return;
            }
            response.json().then(
                (data) => {
                  this.loading = false;
                  if (response.status === 401) {
                    this.loadingErrors.push(data.message);
                    return;
                  }

                  this.$store.commit(
                      'setUser',
                      {
                        email: this.email,
                        token: data.token,
                      }
                  );
                  this.$router.replace({
                    name: 'queries',
                    params: {page: 1}
                  });

                },
                (error) => {
                  this.loading = false;
                  this.loadingErrors.push('Oops... Something went wrong2');
                  console.error('Invalid response data from API: ' + error);
                }
            );
          },
          (any) => {
            this.loading = false;
            this.loadingErrors.push('Oops... Something went wrong3');
            console.error('Error while creating order', any);
          }
      );

    },

  }
}
</script>

<style scoped>

</style>
