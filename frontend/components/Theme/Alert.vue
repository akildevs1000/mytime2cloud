<template>
  <span>
    <style scoped>
      .v-alert.theme--dark {
        background-color: #696cff !important;
        color: #fff !important;
      }
    </style>
    <v-alert
      :class="`${$vuetify.theme.dark ? '' : 'primary white--text'}`"
      dense
      flat
      style="font-weight: 400"
    >
      <slot></slot>
    </v-alert>
  </span>
</template>