<template>
  <div v-if="can(`logs_access`)">
    <v-row>

      <v-col cols="12">
        <!-- <GenerateLog /> -->

        <Logs />
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>

<script>
import Logs from "../../components/employee/Logs.vue";

export default {
  layout: "employee",
  components: { Logs },
  data: () => ({}),

  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
  },
};
</script>
