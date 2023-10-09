<template>
  <div v-if="can('announcement_access')">
    <Announcements />
  </div>
  <NoAccess v-else />
</template>

<script>
import Announcements from "../components/announcements/Announcements.vue";

export default {
  components: { Announcements },
  data: () => ({}),
  created() {},
  methods: {
    can(per) {
      return this.$dateFormat.can(per, this);
    },
    can_old(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
  },
};
</script>
