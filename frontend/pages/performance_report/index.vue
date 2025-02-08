<template>
  <div v-if="can(`attendance_report_access`)">
    <Performance />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
};
</script>
