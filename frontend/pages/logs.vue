<template>
  <div v-if="can(`logs_access`)">
    <v-row>
      <v-col cols="6">
        <h3>Attendances Logs</h3>
        <div>Dashboard / Attendances Logs</div>
      </v-col>
      <v-col cols="12">
        <!-- <GenerateLog /> -->

        <Logs />
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({}),

  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e == per || per == "/")) || u.is_master
      );
    }
  }
};
</script>
