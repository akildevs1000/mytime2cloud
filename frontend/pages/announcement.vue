<template>
  <div v-if="can('announcement_access')">
    <v-card class="mt-2">
      <v-tabs class="pt-3" right>
        <v-tabs-slider color="violet"></v-tabs-slider>
        <v-tab>
          <span>Announcements</span>
        </v-tab>
        <v-tab>
          <span>Announcement Type</span>
        </v-tab>
        <v-tab-item>
          <Announcements />
        </v-tab-item>
        <v-tab-item>
          <AnnouncementType />
        </v-tab-item>
      </v-tabs>
    </v-card>
  </div>
  <NoAccess v-else />
</template>

<script>
import Announcements from "../components/announcements/Announcements_old.vue";
import AnnouncementType from "../components/announcements/AnnouncementType.vue";

export default {
  components: { Announcements, AnnouncementType },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
};
</script>
