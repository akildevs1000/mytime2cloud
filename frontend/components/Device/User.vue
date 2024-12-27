<template>
  <v-dialog v-model="dialog" max-width="1100">
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon :color="iconColor" :size="iconSize">mdi-cellphone-text</v-icon>
        {{ iconText }}
      </span>
    </template>
    <v-card>
      <v-toolbar dense flat color="primary" dark>
        <v-card-title>{{ label }}</v-card-title>
        <v-spacer></v-spacer>
        <v-icon :loading="loading" @click="getDataFromApi">mdi-sync</v-icon>
        <v-icon @click="dialog = false">mdi-close</v-icon>
      </v-toolbar>
      <v-card-text>
        <v-container>
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">#</th>
                  <th class="text-center">User Id</th>
                  <th class="text-center">Device Name</th>
                  <th class="text-center">Employee Data</th>
                  <th class="text-center">Location</th>
                  <th class="text-center">Face</th>
                  <th class="text-center">RFID</th>
                  <th class="text-center">PIN</th>
                  <th class="text-center">Delete</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading" class="pt-5">
                  <td colspan="100" class="text-center pa-5">
                    <div class="text-center">
                      <v-progress-circular
                        color="primary"
                        indeterminate
                      ></v-progress-circular>
                    </div>
                  </td>
                </tr>
                <!-- {{
                  data
                }} -->
                <tr v-for="(d, index) in data" :key="index">
                  <td class="text-left">{{ index + 1 }}</td>
                  <td class="text-center">{{ d.system_user_id }}</td>
                  <td class="text-left">
                    {{ d.name }}
                  </td>
                  <td class="text-center">
                    <span class="red--text" v-if="d.noResponse"
                      >No Response From Device</span
                    >
                    <span class="green--text" v-else>Avaialbe on Device</span>
                  </td>
                  <td class="text-center">{{ d.location }}</td>
                  <td class="text-center">
                    <v-icon color="green" v-if="d.IsFace">mdi-check</v-icon>
                    <v-icon color="" v-else>mdi-minus</v-icon>
                  </td>
                  <td class="text-center">
                    <v-icon v-if="d.IsRFID == '' || d.IsRFID == '0'"
                      >mdi-minus</v-icon
                    >
                    <v-icon color="green" v-else>mdi-check</v-icon>
                  </td>
                  <td class="text-center">
                    <v-icon v-if="d.IsPIN == '' || d.IsPIN == 'FFFFFFFF'"
                      >mdi-minus</v-icon
                    >
                    <v-icon color="green" v-else>mdi-check</v-icon>
                  </td>
                  <td class="text-center">
                    <v-icon
                      color="red"
                      @click="
                        deleteUserFromDevice(d.device_id, d.system_user_id)
                      "
                      >mdi-close</v-icon
                    >
                  </td>
                </tr>

                <!-- <tr v-if="!loading && !data.length" class="pt-5">
                  <td colspan="5" class="text-center">No Data available</td>
                </tr> -->
              </tbody>
            </template>
          </v-simple-table>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  props: {
    label: {
      type: String,
      default: "Tenant",
    },
    iconText: {
      type: String,
      default: "Devices",
    },
    iconColor: {
      type: String,
      default: "secondary",
    },
    iconSize: {
      type: String,
      default: "small",
    },
    system_user_id: {
      required: true,
    },
  },

  data: () => ({
    options: {},
    endpoint: "device",
    dialog: false,
    devices: [],
    data: [],
    loading: false,
    total: 0,
    totalRowsCount: 0,
    headers: [
      {
        text: "Name",
        align: "left",
        sortable: false,
        value: "name",
        filterable: false,
      },
      {
        text: "Location",
        align: "left",
        sortable: false,
        value: "location",
        filterable: false,
      },
      {
        text: "Face",
        align: "left",
        sortable: false,
        value: "IsFace",
        filterable: false,
      },
      {
        text: "RFID",
        align: "left",
        sortable: false,
        value: "IsRFID",
        filterable: false,
      },
      {
        text: "PIN",
        align: "left",
        sortable: false,
        value: "IsPIN",
        filterable: false,
      },
      //   {
      //     text: "DateTime",
      //     align: "center",
      //     sortable: false,
      //     value: "uploaded_at",
      //     filterable: false,
      //     filterSpecial: false,
      //   },
    ],
    response: "",
    errors: [],
    apiCallGetEmployeeDevice: false,
  }),

  watch: {
    options: {
      handler() {
        if (this.dialog == true) {
          this.getDataFromApi();
        }
      },
      deep: true,
    },
  },
  async created() {
    this.loading = true;
    if (this.dialog == true) {
      this.getDataFromApi();
    }
  },

  methods: {
    async getDataFromApi() {
      this.data = [];
      this.loading = true;

      const response = await this.$axios.get("device-list", {
        params: {
          company_id: this.$auth.user.company_id,
        },
      });

      const data = response.data;
      this.data = [];

      if (data.length === 0) {
        this.loading = false;
        return;
      }

      for (const e of data) {
        try {
          this.loading = true;
          const deviceResponse = await this.$axios.get(
            `/SDK/get-device-person-details/${e.device_id}/${this.system_user_id}`
          );
          const deviceData = deviceResponse.data.data;

          if (deviceData === null) {
            this.data.push({
              device_id: e.device_id,
              name: e.name,
              location: "---",
              IsFace: false,
              IsRFID: false,
              IsPIN: false,
              noResponse: true,
              system_user_id: this.system_user_id,
            });
          } else {
            this.data.push({
              device_id: e.device_id,
              name: e.name,
              location: e.location,
              IsFace: deviceData?.face ? deviceData.face : "",
              IsRFID: deviceData?.cardData ? deviceData.cardData : "",
              IsPIN: deviceData?.password ? deviceData.password : "",
              system_user_id: this.system_user_id,
            });
          }
        } catch (error) {
          this.loading = false;
          this.data.push({
            device_id: e.device_id,
            name: e.name,
            location: "---",
            IsFace: false,
            IsRFID: false,
            IsPIN: false,
            noResponse: true,
            system_user_id: this.system_user_id,
          });

          console.log(
            `Error fetching device details for device ID ${e.device_id}:`,
            error
          );
        }
      }
      this.loading = false;
    },
    deleteUserFromDevice(device_id, system_user_id) {
      if (confirm("Are you sure you want to delete Employee From Device"))
        this.$axios
          .delete(`/SDK/delete-device-person-details/${device_id}`, {
            params: { userCodeArray: [system_user_id] },
          })
          .then(({ data }) => {
            this.loading = false;
            this.getDataFromApi();
            alert("Employee Record  deleted ");
          })
          .catch((e) => {
            alert("Record cannot delete");
            console.log(e);
            this.loading = false;
          });
    },
  },
};
</script>
