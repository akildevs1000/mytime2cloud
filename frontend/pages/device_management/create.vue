<template>
  <div v-if="can('device_management')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="!preloader">
      <v-row class="mt-5 mb-5">
        <v-col cols="10">
          <h3>{{ model }}</h3>
          <div>Dashboard / {{ model }}</div>
        </v-col>
      </v-row>
      <v-card elevation="0" class="pa-3">
        <v-card-title>
          <label class="col-form-label"
            ><b>Upload User List to devices </b></label
          >
          <v-spacer></v-spacer>
          <v-btn small fab color="background" dark to="/report_notifications">
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
        </v-card-title>
        <v-container>
          <v-row>
            <v-col cols="3">
              <v-autocomplete
                @change="setUsers"
                :hide-details="!payload.users"
                multiple
                outlined
                dense
                item-text="display_name"
                item-value="system_user_id"
                placeholder="Users"
                v-model="payload.users"
                :items="employees"
              >
              </v-autocomplete>

              <span v-if="errors && errors.users" class="error--text">{{
                errors.users[0]
              }}</span>
            </v-col>

            <v-col cols="3">
              <v-autocomplete
                @change="setDevices"
                :hide-details="!payload.devices"
                v-model="payload.devices"
                multiple
                outlined
                dense
                item-text="name"
                item-value="device_id"
                placeholder="Devices"
                :items="devices"
              >
              </v-autocomplete>
              <span v-if="errors && errors.devices" class="error--text">{{
                errors.devices[0]
              }}</span>
            </v-col>

            <v-col cols="12">
              <v-btn small color="primary" @click="store">
                Submit
              </v-btn>
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
import {
  TiptapVuetify,
  Heading,
  Bold,
  Italic,
  Strike,
  Underline,
  Paragraph,
  BulletList,
  OrderedList,
  ListItem,
  Blockquote,
  History
} from "tiptap-vuetify";

export default {
  components: { TiptapVuetify },

  data: () => ({
    model: "Device Management",
    menu: false,
    color: "primary",

    preloader: false,
    loading: false,
    response: false,
    snackbar: false,

    payload: {
      users: [],
      devices: [],
      company_id: 0
    },
    employees: [],
    devices: [],
    errors: []
  }),

  created() {
    let options = {
      params: {
        per_page: 1000,
        company_id: this.$auth?.user?.company?.id
      }
    };
    this.$axios.get(`employee`, options).then(({ data: { data } }) => {
      this.employees = data;
      this.employees.unshift({ display_name: "All", system_user_id: "-1" });
    });

    this.$axios.get(`device`, options).then(({ data: { data } }) => {
      this.devices = data.filter(e => e.status_id == 1);
      this.devices.unshift({ name: "All", device_id: "-1" });
    });

    this.preloader = false;
    this.payload.company_id = this.$auth?.user?.company?.id;
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e.name == per || per == "/")) ||
        u.is_master
      );
    },
    setUsers() {
      this.payload.users = this.payload.users.includes("-1")
        ? this.employees
        : this.employees.filter(e =>
            this.payload.users.includes(e.system_user_id)
          );
    },
    setDevices() {
      this.payload.devices = this.payload.devices.includes("-1")
        ? this.devices
        : this.devices.filter(e => this.payload.devices.includes(e.device_id));
    },
    store() {
      this.$axios
        .post("/report_notification", this.payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
            return;
          }

          this.snackbar = data.status;
          this.response = data.message;
        })
        .catch(e => console.log(e));
    }
  }
};
</script>
<style scoped>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
<style>
.tiptap-vuetify-editor__content {
  min-height: 400px !important;
}

.ProseMirror .ProseMirror-focused {
  height: 400px !important;
}

.tiptap-icon .v-icon {
  color: white !important;
}
.tiptap-icon .v-btn--icon {
  color: white !important;
}
</style>
