<template>
  <div>
    <Header/>
    <div class="py-6 md:py-12">
      <div class="container px-4 mx-auto">
        <router-view></router-view>
      </div>
    </div>
  </div>
</template>

<script>
import Header from "./components/Header"

export default {
  name: "App",
  components: {Header}
}
</script>
