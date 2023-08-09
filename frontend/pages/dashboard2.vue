<template>
  <v-container fluid>
    <DashboardCardComponent page="dashboard2" />
    <DashboardComponent page="dashboard2" />
  </v-container>
</template>

<script>
import DashboardCardComponent from "../components/widgets/DashboardCardComponent.vue";
import DashboardComponent from "../components/widgets/DashboardComponent.vue";

export default {
  components: {
    DashboardCardComponent,
    DashboardComponent,
  },
};
</script>
