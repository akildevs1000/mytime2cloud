<template>
  <v-app class="login-app">
    <v-container class="fill-height" fluid>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="4">
          <v-card dark class="elevation-12 login-app">
            <v-card-title class="headline primary--text">Login</v-card-title>
            <v-card-text>
              <v-form @submit.prevent="login">
                <v-text-field
                  :hide-details="false"
                  v-model="email"
                  type="email"
                  color="white"
                  outlined
                  required
                ></v-text-field>
                <v-text-field
                  v-model="password"
                  type="password"
                  color="white"
                  outlined
                  required
                ></v-text-field>
                <v-btn type="submit" color="primary" block>Login</v-btn>
              </v-form>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <nuxt-link class="text-muted text-right" to="/reset-password"
                >Forgot password?</nuxt-link
              >
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-app>
</template>

<script>
export default {
  auth: false,
  layout: "login",
  data() {
    return {
      email: "",
      password: "",
    };
  },
  methods: {
    login() {
      // Implement your login logic here
      // For example, you can use Vuex to handle the authentication process
      // For simplicity, let's just display the input data in the console
      console.log("Email:", this.email);
      console.log("Password:", this.password);
    },
  },
};
</script>

<style>
/* Customize the background and font styles */
.login-app {
  margin: 0;
  padding: 0;
  /* You can also customize other background properties, e.g., background-size, background-repeat, etc. */
  background-size: cover; /* Adjust to your needs */
  background-position: center center; /* Adjust to your needs */
  background-image: url("https://th.bing.com/th/id/R.735714d9b29db50bab07b844f7f2dd2d?rik=tZeTGKp71Kvoig&pid=ImgRaw&r=0") !important;
  /* background-image: linear-gradient(
    to right top,
    #34444c,
    #556269,
    #5a717d,
    #a2becc,
    #ffffff
  ) !important; */
  /* font-family: "Roboto", sans-serif;
  font-size: 16px; */
}
</style>
