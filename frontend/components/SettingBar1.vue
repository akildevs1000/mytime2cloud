<template>
  <div>
    <v-row>
      <v-col>
        <v-card class="pa-2" elevation="0">
          <v-col cols="12">
            <div class="mb-5">
              <Strong>Top Bar</Strong>
            </div>
            <div class="d-flex">
              <v-btn
                class="mx-2"
                fab
                dark
                x-small
                color="primary"
                @click="changeTopBarColor('primary')"
              ></v-btn>
              <v-btn
                class="mx-2"
                fab
                dark
                x-small
                color="error"
                @click="changeTopBarColor('error')"
              ></v-btn>
              <v-btn
                class="mx-2"
                fab
                dark
                x-small
                color="indigo"
                @click="changeTopBarColor('indigo')"
              ></v-btn>
            </div>
          </v-col>
          <v-divider></v-divider>
          <v-col cols="12">
            <div class="mb-5">
              <Strong>Side Bar</Strong>
            </div>
            <div class="d-flex">
              <v-btn
                class="mx-2"
                fab
                dark
                x-small
                color="primary"
                @click="changeSideBarColor('primary')"
              ></v-btn>
              <v-btn
                class="mx-2"
                fab
                dark
                x-small
                color="error"
                @click="changeSideBarColor('error')"
              ></v-btn>
              <v-btn
                class="mx-2"
                fab
                dark
                x-small
                color="indigo"
                @click="changeSideBarColor('indigo')"
              ></v-btn>
              <v-btn
                class="mx-2"
                fab
                dark
                x-small
                color="background"
                @click="changeSideBarColor('background')"
              >
              </v-btn>
            </div>
          </v-col>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  data() {
    return {
      color: "",
      sideBarcolor: ""
    };
  },

  methods: {
    changeTopBarColor(color) {
      this.color = color;
      // this.$store.commit("change_color", color);
    },
    changeSideBarColor(color) {
      this.sideBarcolor = color;
    }
  }
};
</script>

<style scoped src="@/assets/dashtem.css"></style>
