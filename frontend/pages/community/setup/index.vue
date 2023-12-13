<template>
  <v-tabs color="deep-purple accent-4" right>
    <v-tab>Room</v-tab>
    <v-tab>Floor</v-tab>
    <v-tab>Category</v-tab>
    <v-tab>Community</v-tab>

    <v-tab-item> <SnippetsCommunityRoom :id="companyId" /> </v-tab-item>
    <v-tab-item> <SnippetsCommunityFloor :id="companyId" /> </v-tab-item>
    <v-tab-item> <SnippetsCommunityCategory :id="companyId" /> </v-tab-item>
    <v-tab-item> <SnippetsCommunitySingle :id="companyId" /> </v-tab-item>
  </v-tabs>
</template>
<script>
export default {
  data: () => ({
    companyId: 0,
  }),
  created() {
    this.companyId = this.$auth.user.company_id;
  },
};
</script>
