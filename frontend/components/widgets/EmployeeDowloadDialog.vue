<template>
  <v-dialog v-model="dialog" width="800">
    <WidgetsClose left="790" @click="dialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <v-btn
        style="margin-top: -5px"
        small
        color="primary "
        dark
        v-bind="attrs"
        v-on="on"
      >
        Download Employees
      </v-btn>
    </template>

    <v-card>
      <v-alert class="text-h5 grey lighten-2" dense flat>
        <div style="font-size: 14px">Download Employee From Device</div>
      </v-alert>
      <v-card-text>
        <v-autocomplete
          outlined
          dense
          @change="getEmployeesIds"
          x-small
          item-value="device_id"
          item-text="name"
          :items="[{ name: `Select Device`, device_id: null }, ...devices]"
          placeholder="Device"
        ></v-autocomplete>

        <div style="overflow-y: scroll; max-height: 400px">
          <v-simple-table dense>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-center">
                    <v-checkbox v-model="selectAll"></v-checkbox>
                  </th>
                  <th class="text-center">User Id</th>
                  <th class="text-center">Name</th>
                  <th class="text-center">Face</th>
                  <th class="text-center">RFID</th>
                  <th class="text-center">PIN</th>
                  <th class="text-center">FP</th>
                  <th class="text-center">Palm</th>
                </tr>
              </thead>
              <tbody>
                <!-- {{
                  data
                }} -->
                <tr v-for="(d, index) in employees" :key="index">
                  <td class="text-center">
                    <v-checkbox v-model="d.isSelected"></v-checkbox>
                  </td>
                  <td class="text-center">{{ d.system_user_id }}</td>
                  <td class="text-center">
                    {{ d.full_name }}
                  </td>
                  <td class="text-center">
                    <v-icon color="green" v-if="d.isFace">mdi-check</v-icon>
                    <v-icon color="" v-else>mdi-minus</v-icon>
                  </td>
                  <td class="text-center">
                    <v-icon color="green" v-if="d.isRFID">mdi-check</v-icon>
                    <v-icon color="" v-else>mdi-minus</v-icon>
                  </td>
                  <td class="text-center">
                    <v-icon color="green" v-if="d.isPIN">mdi-check</v-icon>
                    <v-icon color="" v-else>mdi-minus</v-icon>
                  </td>
                  <td class="text-center">
                    <v-icon color="green" v-if="d.isFP">mdi-check</v-icon>
                    <v-icon color="" v-else>mdi-minus</v-icon>
                  </td>
                  <td class="text-center">
                    <v-icon color="green" v-if="d.isPalm">mdi-check</v-icon>
                    <v-icon color="" v-else>mdi-minus</v-icon>
                  </td>
                </tr>
                <tr v-if="loading">
                  <td colspan="8" class="text-center">{{ response }}</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </div>
        <br />
        <div flat dense class="red--text" v-if="errorResponse">
          {{ errorResponse }}
        </div>
        <div class="pt-5 mx-5">
          <v-btn block class="primary" @click="submit">Submit</v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  data: () => ({
    selectAll: false,
    dialog: false,
    devices: [],
    employees: [],
    loading: false,
    response: "Loading...",
    company_id: 1,
    errorResponse: null,
  }),
  async created() {
    this.company_id = this.$auth.user.company_id;

    //this.loading = true;
    // let page = this.pagination.current;
    await this.getDevices();
  },
  watch: {
    selectAll(value) {
      this.employees.forEach((employee) => {
        employee.isSelected = value; // Set to true if selectAll is true, otherwise false
      });
    },
  },
  methods: {
    async submit() {
      try {
        this.errorResponse = null;
        let payload = {
          employees: this.employees
            .filter((e) => e.isSelected == true)
            .map((e) => ({
              company_id: e.company_id,
              employee_id: e.employee_id,
              full_name: e.full_name,
              system_user_id: e.system_user_id,
              profile_picture: e.profile_picture,
              rfid_card_number: e.rfid_card_number,
              rfid_card_password: e.rfid_card_password,
              fp: e.fp ? e.fp : [],
              palm: e.palm ? e.palm : [],
            })),
        };
        await this.$axios.post(`/employee-store-from-device`, payload);
        this.dialog = false;
        this.employees = [];
        this.$emit("response");
      } catch (error) {
        console.log((this.errorResponse = error?.response?.data?.message));
      }
    },
    async getDevices() {
      let options = {
        params: {
          per_page: 1000,
          company_id: this.company_id,
          sortBy: "name",
          cols: ["name", "device_id", "status:id"],
        },
      };
      let { data } = await this.$axios.get(`/device`, options);
      this.devices = data.data;
    },
    async getEmployeesIds(device_id) {
      this.employees = [];
      if (!device_id) return;
      let { data } = await this.$axios.get(
        `/SDK/get-person-all-v1/${device_id}`
      );
      this.getEmployees(
        device_id,
        data.data.map((e) => e.userCode)
      );
    },

    async getEmployees(device_id, employeeIds) {
      employeeIds.forEach(async (employeeId) => {
        try {
          this.loading = true;
          const { data } = await this.$axios.get(
            `/SDK/get-person-details-v1/${device_id}/${employeeId}`
          );

          const deviceData = data.data;

          if (!deviceData) {
            alert(`Data not found`);
            return;
          }

          let payload = {
            company_id: this.company_id,
            full_name: deviceData.name,
            employee_id: deviceData.userCode,
            system_user_id: deviceData.userCode,
            profile_picture: deviceData.faceImage,
            rfid_card_number: deviceData.cardData,
            rfid_card_password: deviceData.password,
            fp: deviceData.fp,
            palm: deviceData.palm,
            face: deviceData.face,
            fpCount: deviceData.fpCount,
            isFace: deviceData.face == 1 ? true : false,
            isRFID:
              deviceData.cardData == "" || deviceData.cardData == "0"
                ? false
                : true,
            isPIN:
              deviceData.password == "" || deviceData.password == "FFFFFFFF"
                ? false
                : true,
            isFP: deviceData.fp ? true : false,
            isPalm: deviceData.palm ? true : false,
            isSelected: true,
          };
          this.employees.push(payload);
          this.loading = false;
          this.response = "loading....";
        } catch (error) {
          this.employees = [];
          this.response = error;
        }
      });
    },
  },
};
</script>
