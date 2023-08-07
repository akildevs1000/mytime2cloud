<template>
  <div v-if="can('company_access')">
    <div v-if="!preloader">
      <div class="text-center ma-2">
        <v-snackbar
          v-model="snackbar"
          top="top"
          color="secondary"
          elevation="24"
        >
          {{ response }}
        </v-snackbar>
      </div>
      <v-card>
          <v-toolbar class="background" dark>
            Page Builder
          </v-toolbar>
        <v-container fluid>
          <v-row>
            <v-col cols="12">
              <v-autocomplete
                label="Select Page"
                v-model="page"
                :items="[`dashboard`]"
              >
              </v-autocomplete>
            </v-col>
            <v-col cols="12">
              <CardDesginer :page="page" />
            </v-col>
          </v-row>
        </v-container>
      </v-card>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>

<script>
import CardDesginer from "../../components/Theme/CardDesginer.vue";

export default {
  data: () => ({
    page: "dashboard",
    show_password_confirm: false,
    current_password_show: false,
    show_password: false,
    loading_password: false,
    menuIssueDate: false,
    menuExpiryDate: false,
    IssueDate: null,
    vertical: false,
    id: "",
    loading: false,
    preloader: true,
    upload: {
      name: "",
    },
    company_payload: {
      name: "",
      logo: "",
      member_from: "",
      expiry: "",
      max_branches: "",
      max_employee: "",
      max_devices: "",
      mol_id: "",
      p_o_box_no: "",
    },
    company_trade_license: {
      license_no: "",
      license_type: "",
      emirate: "",
      makeem_no: "",
      manager: "",
      issue_date: "",
      expiry_date: "",
    },
    contact_payload: {
      name: "",
      number: "",
      position: "",
      whatsapp: "",
    },
    user_payload: {
      password: "",
      password_confirmation: "",
    },
    payload: {
      password: "",
      current_password: "",
      password_confirmation: "",
    },
    geographic_payload: {
      lat: "",
      lon: "",
      location: "",
    },
    e1: 1,
    errors: [],
    previewImage: null,
    data: {},
    response: "",
    snackbar: false,
  }),
  async created() {
    this.getDataFromApi();
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    update_setting() {
      this.$axios
        .post(
          `/company/${this.$auth?.user?.company?.id}/update/user`,
          this.payload
        )
        .then(({ data }) => {
          this.loading = false;
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = "Setting updated successfully";
          }
        })
        .catch((e) => console.log(e));
    },
    getDataFromApi() {
      this.id = this.$route.params.id;
      this.$axios.get(`company/${this.id}`).then(({ data }) => {
        let r = data.record;
        this.company_payload = r;
        this.contact_payload = r.contact;
        this.user_payload = r.user;
        if (r.trade_license) {
          this.company_trade_license = r.trade_license;
        }
        let mf = this.formatted_date(r.member_from);
        let exp = this.formatted_date(r.expiry);
        this.company_payload.member_from = mf;
        this.company_payload.expiry = exp;
        this.geographic_payload = {
          lat: this.company_payload.lat,
          lon: this.company_payload.lon,
          location: this.company_payload.location,
        };
        this.preloader = false;
      });
    },
    formatted_date(v) {
      let [year, month, date] = v.split("/");
      return `${year}-${month}-${date}`;
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
    attachment(e) {
      this.upload.name = e.target.files[0] || "";
      let input = this.$refs.attachment_input;
      let file = input.files;
      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },
    update_company() {
      let payload = new FormData();
      payload.append("name", this.company_payload.name);
      if (this.upload.name) {
        payload.append("logo", this.upload.name);
      }
      payload.append("location", this.company_payload.location);
      payload.append("member_from", this.company_payload.member_from);
      payload.append("expiry", this.company_payload.expiry);
      payload.append("max_employee", this.company_payload.max_employee);
      payload.append("max_branches", this.company_payload.max_branches);
      payload.append("max_devices", this.company_payload.max_devices);
      payload.append("mol_id", this.company_payload.mol_id);
      payload.append("p_o_box_no", this.company_payload.p_o_box_no);
      this.start_process(`/company/${this.id}/update`, payload, `Company`);
    },
    update_contact() {
      this.start_process(
        `/company/${this.id}/update/contact`,
        this.contact_payload,
        `Contact`
      );
    },
    update_license() {
      this.start_process(
        `/company/${this.id}/trade-license`,
        this.company_trade_license,
        `Trade License`
      );
    },
    update_geographic() {
      this.start_process(
        `/company/${this.id}/update/geographic`,
        this.geographic_payload,
        `Geographic Info`
      );
    },
    update_user() {
      this.start_process(
        `/company/${this.id}/update/user`,
        this.user_payload,
        `User`
      );
    },
    start_process(url, payload, model) {
      this.loading = true;
      this.$axios
        .post(url, payload)
        .then(({ data }) => {
          this.loading = false;
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = model + " updated successfully";
          }
        })
        .catch((e) => console.log(e));
    },
  },
  components: { CardDesginer },
};
</script>
