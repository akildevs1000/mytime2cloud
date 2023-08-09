<template>
  <v-container fluid>
    <DashboardCardComponent page="dashboard1" />
    <DashboardComponent page="dashboard1" />
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
