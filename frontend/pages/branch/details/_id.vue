<template>
  <div v-if="can(`branch_details_access`)">
    <v-row class="mt-10 mb-10">
      <v-col cols="10">
        <h3>Branch</h3>
        <div>Dashboard / Branch / Details</div>
      </v-col>
    </v-row>

    <v-card elevation="2">
      <v-row>
        <v-col cols="6" style="border-right: 1px dashed #808080">
          <v-list-item>
            <v-list-item-avatar tile size="120">
              <v-img
                :src="
                  company_payload.logo ? company_payload.logo : '/no-image.PNG'
                "
              ></v-img>
            </v-list-item-avatar>
            <v-list-item-content>
              <div class="text-overline mb-1">
                Branch Code : {{ company_payload.id }}
              </div>
              <v-list-item-title class="text-h5 mb-1">
                {{ company_payload.name }}
              </v-list-item-title>
              <v-list-item-subtitle>{{
                company_payload.location
              }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>

          <v-list-item>
            <v-list-item-content>
              <v-row class="mt-2">
                <v-col cols="3">
                  <v-list-item-title class="text-h7 mb-1">
                    Member From
                  </v-list-item-title>
                </v-col>
                <v-col cols="8">
                  {{ company_payload.show_member_from }}
                </v-col>

                <v-col cols="3">
                  <v-list-item-title class="text-h7 mb-1">
                    Expiry Date
                  </v-list-item-title>
                </v-col>
                <v-col cols="8">
                  {{ company_payload.show_expiry }}
                </v-col>

                <v-col cols="3">
                  <v-list-item-title class="text-h7 mb-1">
                    Max Employees
                  </v-list-item-title>
                </v-col>
                <v-col cols="8">
                  {{ company_payload.max_employee }}
                </v-col>

                <v-col cols="3">
                  <v-list-item-title class="text-h7 mb-1">
                    Max Devices
                  </v-list-item-title>
                </v-col>
                <v-col cols="8">
                  {{ company_payload.max_devices }}
                </v-col>
              </v-row>
            </v-list-item-content>
          </v-list-item>
        </v-col>
        <v-col cols="6">
          <v-row>
            <v-col cols="4">
              <v-list-item-title class="text-h7 mb-1">
                Contact Name
              </v-list-item-title>
            </v-col>
            <v-col cols="8">
              {{ contact_payload.contact_name }}
            </v-col>

            <v-col cols="4">
              <v-list-item-title class="text-h7 mb-1">
                Contact Number
              </v-list-item-title>
            </v-col>
            <v-col cols="8">
              {{ contact_payload.contact_no }}
            </v-col>

            <v-col cols="4">
              <v-list-item-title class="text-h7 mb-1">
                Contact Position
              </v-list-item-title>
            </v-col>
            <v-col cols="8">
              {{ contact_payload.contact_position }}
            </v-col>

            <v-col cols="4">
              <v-list-item-title class="text-h7 mb-1">
                Contact Whatsapp
              </v-list-item-title>
            </v-col>
            <v-col cols="8">
              {{ contact_payload.contact_whatsapp }}
            </v-col>

            <v-col cols="4">
              <v-list-item-title class="text-h7 mb-1">
                User Name
              </v-list-item-title>
            </v-col>
            <v-col cols="8">
              {{ login_payload.user_name }}
            </v-col>

            <v-col cols="4">
              <v-list-item-title class="text-h7 mb-1">
                User Email
              </v-list-item-title>
            </v-col>
            <v-col cols="8">
              {{ login_payload.email }}
            </v-col>

            <v-col cols="4">
              <v-list-item-title class="text-h7 mb-1">
                Company Created At
              </v-list-item-title>
            </v-col>
            <v-col cols="8">
              {{ company_payload.created_at }}
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-card>

    <v-tabs class="mt-5 mb-5">
      <v-tab>
        <v-icon left> mdi-laptop </v-icon>
        Devices
      </v-tab>

      <v-tab-item>
        <v-row class="mt-5 mb-5">
          <v-col cols="6">
            <h3>Device</h3>
            <div>Dashboard / Branch / Device</div>
          </v-col>
          <v-col cols="6">
            <div class="text-right">
              <v-btn
                v-if="can(`device_create_access`)"
                small
                color="primary"
                class="mb-2"
                :to="`/device/create/${$route.params.id}`"
                >+ Add Device</v-btn
              >
            </div>
          </v-col>
        </v-row>

        <v-row v-if="can(`device_view_access`)" class="mt-5 mb-5">
          <v-col cols="3" v-for="(item, index) in devices" :key="index">
            <v-card>
              <v-toolbar flat dense small class="primary" dark>{{
                item.device_id
              }}</v-toolbar>
              <v-card-title>
                <span
                  ><v-chip
                    small
                    :class="item.status.name == 'active' ? 'success' : 'error'"
                    >{{ item.status.name }}</v-chip
                  ></span
                >
                <v-spacer></v-spacer>
                <v-icon @click="editItem(item)" color="secondary" small
                  >mdi-pencil</v-icon
                >

                <v-icon @click="deleteItem(item)" color="red" small
                  >mdi-delete</v-icon
                >
              </v-card-title>

              <v-card-text class="text-center">
                <div>
                  <v-avatar color="secondary">
                    <v-icon dark> mdi-laptop </v-icon>
                  </v-avatar>
                </div>

                <div>
                  <b>{{ item.name }}</b>
                </div>
                <div>
                  {{ item.location }}
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-tab-item>
    </v-tabs>
  </div>
</template>

<script>
export default {
  data: () => ({
    loading: false,
    company_payload: {
      name: "",
      logo: "",
      location: "",
      member_from: "",
      expiry: "",
      max_employee: "",
      max_devices: "",
    },
    contact_payload: {
      contact_name: "",
      contact_no: "",
      contact_position: "",
      contact_whatsapp: "",
    },
    login_payload: {
      user_name: "",
      email: "",
      password: "",
      password_confirmation: "",
    },
    e1: 1,
    errors: [],
    data: [],
    devices: [],
  }),
  async created() {
    this.getDataFromApi();
    this.getCompanyDetails();
    this.getDevicesByCompanyId();
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
    },

    getDataFromApi() {
      this.$axios
        .get(`company/${this.$route.params.id}/branches`)
        .then(({ data }) => {
          this.data = data.data;
        });
    },
    getDevicesByCompanyId() {
      this.$axios
        .get(`company/${this.$route.params.id}/devices`)
        .then(({ data }) => {
          this.devices = data.data;
        });
    },
    getCompanyDetails() {
      this.$axios.get(`branch/${this.$route.params.id}`).then(({ data }) => {
        let r = data.record;
        this.company_payload = r;

        this.contact_payload.contact_name = r.contact.name;
        this.contact_payload.contact_no = r.contact.number;
        this.contact_payload.contact_position = r.contact.position;
        this.contact_payload.contact_whatsapp = r.contact.whatsapp;

        this.login_payload.user_name = r.user.name;
        this.login_payload.email = r.user.email;

        let mf = this.formatted_date(r.member_from);
        let exp = this.formatted_date(r.expiry);
        this.company_payload.member_from = mf;
        this.company_payload.expiry = exp;
        //   this.company_payload = this.can("companies_read") ? data : {};
      });
    },

    formatted_date(v) {
      let [year, month, date] = v.split("/");
      return `${year}-${month}-${date}`;
    },

    attachment(e) {
      this.company_payload.logo = e.target.files[0] || "";
    },
    editItem(item) {
      this.$router.push(`/branch/edit/${item.id}`);
    },
    deleteItem(item) {
      confirm("Are you sure you want to delete this item?") &&
        this.$axios.delete("branch/" + item.id).then((res) => {
          const index = this.data.indexOf(item);
          this.data.splice(index, 1);
        });
    },
  },
};
</script>
