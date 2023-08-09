<template>
  <v-card>
    <v-toolbar class="background" dark> Page Builder </v-toolbar>
    <v-container fluid>
      <v-row>
        <v-col cols="12">
          <v-autocomplete
            label="Select Page"
            v-model="page"
            :items="[`dashboard1`, `dashboard2`]"
          >
          </v-autocomplete>
        </v-col>
        <v-col cols="12">
          <CardDesginer :page="page" />
        </v-col>
      </v-row>
    </v-container>
  </v-card>
</template>

<script>
import CardDesginer from "../../components/Theme/CardDesginer.vue";

export default {
  data: () => ({
    page: null,
  }),
  async created() {
    this.page = this.$route.params.id;
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.includes((e) => e == per || per == "/")) ||
        u.is_master
      );
    },
  },
  components: { CardDesginer },
};
</script>
