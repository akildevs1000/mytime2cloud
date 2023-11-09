<template>
  <div v-if="can(`dashboard_access`)">
    <div v-if="!loading">
      <v-dialog
        persistent
        v-model="dialogGeneralreport"
        :fullscreen="false"
        max-width="1200px"
      >
        <iframe
          v-if="iframeDisplay"
          :src="iframeUrl"
          frameborder="0"
          style="width: 100%; height: 600px"
        ></iframe>
      </v-dialog>
      <v-row>
        <v-col
          v-for="(i, index) in items.statusCounts"
          :key="index"
          xs="12"
          sm="12"
          cols="12"
          md="4"
          lg="4"
          xl="3"
        >
          <div class="card p-2" :class="i.color" style="height: 130px">
            <div class="card-statistic-3">
              <div class="card-icon card-icon-large" style="margin: -20px 10px">
                <i :class="i.icon"></i>
              </div>
              <div class="card-content">
                <h3 class="card-title text-capitalize">{{ i.title }}</h3>
                <h2 class="">{{ i.value }}</h2>
                <p class="mb-0 text-sm">
                  <span
                    style="cursor: pointer"
                    class="handcursor font-11"
                    @click="showDialogGeneralreport(i.link)"
                  >
                    <span class="mr-2"> </span>
                    <span class="text-nowrap regular-font"></span>
                  </span>
                </p>
                <p class="mb-0 text-sm">
                  <span
                    style="cursor: pointer"
                    class="handcursor font-11"
                    @click="showDialogGeneralreport(i.multi_in_out)"
                  >
                    <span class="mr-2"> </span>
                    <span class="text-nowrap regular-font"></span>
                  </span>
                </p>
              </div>
            </div>
          </div>
        </v-col>
      </v-row>
      <VisitorList />
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
import VisitorList from "../components/Visitor/List.vue";

export default {
  components: { VisitorList },

  data() {
    return {
      loading: true,
      dialogGeneralreport: false,
      iframeDisplay: false,
      iframeUrl: "",
      items: [],
    };
  },
  created() {
    this.initialize();
  },
  mounted() {},
  computed: {
    getCurrentDate() {
      // Get the current date
      const date = new Date();

      // Get the year, month, and day from the date object
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");

      return `${year}-${month}-${day}`;
    },
  },
  filters: {
    get_decimal_value: function (value) {
      if (!value) return "";
      return (Math.round(value * 100) / 100).toFixed(2);
    },
    get_comma_seperator: function (x) {
      if (!x) return "";
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
  },
  methods: {
    openDialog(announcement) {
      this.dialogData = announcement;
      this.dialog = true;
    },

    updateLink(url) {
      if (
        this.$axios.defaults.baseURL !=
        "https://stagingbackend.ideahrms.com/api"
      ) {
        url = url.replace(
          "https://stagingbackend.ideahrms.com/api",
          this.$axios.defaults.baseURL
        );
      }

      return url;
    },
    closeDialogGeneralreport() {
      this.iframeDisplay = false;
      this.dialogGeneralreport = false;
      //this.iframeUrl = "#";
    },
    showDialogGeneralreport(iframeUrl) {
      this.iframeDisplay = false;
      this.iframeUrl = this.updateLink(iframeUrl);
      this.dialogGeneralreport = true;

      this.iframeDisplay = true;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    initialize() {
      let options = {
        company_id: this.$auth.user.company_id,
      };
      this.$axios.get(`count`, { params: options }).then(({ data }) => {
        this.items = data;
        console.log(data);
        this.loading = false;

        // if (this.items.length > 0) {
        //   this.loading = false;
        // }
      });
    },
  },
};
</script>
<style scoped src="@/assets/dashboard.css"></style>
