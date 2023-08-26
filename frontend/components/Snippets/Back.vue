<template>
  <v-btn x-small fab :color="color" @click="goBack">
    <v-avatar>
      <v-icon small>mdi-arrow-left</v-icon>
    </v-avatar>
  </v-btn>
</template>

<script>
export default {
  props: ["color"],
  methods: {
    goBack() {
      window.history.back();
    },
  },
};
</script>
