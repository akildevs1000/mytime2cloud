<template>
  <div v-if="can(`summary_report_access`)">
    <SummaryReport />
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
