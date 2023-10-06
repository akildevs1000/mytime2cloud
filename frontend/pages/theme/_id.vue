<template>
  <div>
    <Back color="primary" />

    <v-card class="mt-2">
      <v-toolbar dense class="background" dark>
        Card Builder
        <v-spacer />
      </v-toolbar>
      <v-container fluid>
        <v-row>
          <v-col cols="12">
            <CardDesginer :propPage="page" />
          </v-col>
        </v-row>
      </v-container>
    </v-card>
  </div>
</template>

<script>
import CardDesginer from "../../components/Theme/CardDesginer.vue";
import Back from "../../components/Snippets/Back.vue";

export default {
  data: () => ({
    page: null,
  }),
  async created() {
    this.page = this.$route.params.id;
  },
  methods: {
    goBack() {
      // You can use the built-in window.history to navigate back
      window.history.back();
    },
    can(per) {
      return this.$dateFormat.can(per, this);
    },
    can_old(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.includes((e) => e == per || per == "/")) ||
        u.is_master
      );
    },
  },
  components: { CardDesginer, Back },
};
</script>
