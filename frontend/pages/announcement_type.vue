<template>
  <AnnouncementType />
</template>

<script>
import AnnouncementType from "../components/announcements/AnnouncementType.vue";

export default {
  components: { AnnouncementType },
  data: () => ({}),
  created() {},
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
};
</script>
