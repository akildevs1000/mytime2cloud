<template>
  <v-icon :color="color" :size="size">
    <slot></slot>
  </v-icon>
</template>

<script>
export default {
  props: {
    lightColor: {
      type: String,
      default: "black",
    },
    darkColor: {
      type: String,
      default: "white",
    },
    size: {
      type: [Number, String],
      default: undefined, // use Vuetify default if not specified
    },
  },
  computed: {
    color() {
      return this.$vuetify.theme.dark ? this.darkColor : this.lightColor;
    },
  },
};
</script>
