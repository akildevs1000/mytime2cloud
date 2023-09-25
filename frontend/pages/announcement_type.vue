<template>
  <div v-if="can('announcement_access')">
    <AnnouncementType />
  </div>
  <NoAccess v-else />
</template>

<script>
import AnnouncementType from "../components/announcements/AnnouncementType.vue";

export default {
  components: { AnnouncementType },
  data: () => ({}),
  created() {},
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
