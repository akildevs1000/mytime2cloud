<template>
  <div v-if="can(`visitors_log_access`)">
    <VisitorLogs></VisitorLogs>
  </div>
  <NoAccess v-else />
</template>

<script>
import VisitorLogs from "../components/Visitor/VisitorLogs.vue";

export default {
  data: () => ({}),
  created() {},
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
  components: { VisitorLogs },
};
</script>
