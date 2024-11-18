<template>
  <v-dialog persistent v-model="dialog" width="800">
    <WidgetsClose left="790" @click="close" />
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
        <div class="d-flex justify-space-between align-center">
          <div style="font-size: 14px">Download Employee From Device</div>
          <v-icon
            color="black"
            class="cursor-pointer"
            @click="export_submit"
            title="Download"
          >
            mdi-download
          </v-icon>
        </div>
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
          hide-details
        ></v-autocomplete>

        <div class="my-2">
          <WidgetsProgress :total="totalEmployees" :engaged="engaged" />
        </div>

        <span v-if="employees.length && !loading">
          <div style="overflow-y: auto; max-height: 400px" class="px-2">
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
                  <tr v-if="response">
                    <td colspan="8" class="text-center">{{ response }}</td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </div>
          <div class="pt-5">
            <v-btn block class="primary" small @click="submit">Submit</v-btn>
          </div>
        </span>
        <div class="text-center" v-if="loading">
          <v-progress-linear
            color="deep-purple accent-4"
            indeterminate
            rounded
            height="6"
          ></v-progress-linear>
          <div class="mt-3">Loading, please wait...</div>
        </div>

        <div class="pt-5 red--text" v-if="response">
          {{ response }}
        </div>
        <span v-if="responses.length">
          <div style="overflow-y: auto; max-height: 400px" class="px-2">
            <v-simple-table dense>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-center">User Id</th>
                    <th class="text-center">Name</th>
                    <th class="text-center">Message</th>
                    <th class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- {{
                  data
                }} -->
                  <tr v-for="(r, index) in responses" :key="index">
                    <td class="text-center">{{ r.system_user_id }}</td>
                    <td class="text-center">{{ r.full_name }}</td>
                    <td class="text-center">{{ r.message }}</td>
                    <td class="text-center">
                      <v-icon color="green" v-if="r.status">mdi-check</v-icon>
                      <v-icon color="red" v-else>mdi-close</v-icon>
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </div>
        </span>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
import j2c from "../../utils/json-to-csv-downloader";
export default {
  data: () => ({
    totalEmployees: 0,
    engaged: 0,
    selectAll: false,
    dialog: false,
    devices: [],
    employees: [],
    loading: false,
    response: null,
    company_id: 1,
    responses: [],
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
    close() {
      this.dialog = false;
      this.responses = [];
      this.employee = [];
    },
    async submit() {
      try {
        this.responses = []; // Initialize an array to store responses
        this.loading = true; // Set loading to true at the start

        const selectedEmployees = this.employees.filter((e) => e.isSelected);

        for (const e of selectedEmployees) {
          const payload = {
            company_id: e.company_id,
            employee_id: e.employee_id,
            full_name: e.full_name,
            system_user_id: e.system_user_id,
            profile_picture: e.profile_picture,
            rfid_card_number: e.rfid_card_number,
            rfid_card_password: e.rfid_card_password,
            fp: e.fp ? e.fp : [],
            palm: e.palm ? e.palm : [],
          };

          try {
            // Wait for each request to complete before proceeding
            await this.$axios.post(`/employee-store-from-device`, payload);

            // Push the success response to the responses array
            this.responses.push({
              status: true,
              message: "User Uploaded",
              system_user_id: payload.system_user_id,
              full_name: payload.full_name,
            });
          } catch (error) {
            const firstMessageKey = Object.keys(
              error?.response?.data?.errors || {}
            )[0]; // Get the first key in the 'errors' object
            const firstMessage =
              error?.response?.data?.errors[firstMessageKey]?.[0] ||
              "Unknown error occurred"; // Fallback message

            // Push the error response to the responses array
            this.responses.push({
              status: false,
              message: firstMessage,
              system_user_id: payload.system_user_id,
              full_name: payload.full_name,
            });
          }
        }

        this.loading = false; // Set loading to false after all operations are complete

        // Check if all responses have been processed
        if (selectedEmployees.length === this.responses.length) {
          this.$emit("response", this.responses);
          this.employees = [];
        }
      } catch (error) {
        console.error(error);
        this.loading = false; // Ensure loading is false even in case of errors
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
      this.totalEmployees = 0;
      this.engaged = 0;
      this.employees = [];
      this.responses = [];
      this.response = null;

      if (!device_id) return;

      this.loading = true;

      try {
        let { data } = await this.$axios.get(
          `/SDK/get-person-all-v1/${device_id}`
        );

        let result = data.data;

        if (data.status === 200) {
          this.totalEmployees = result.length || 0;

          this.getEmployees(
            device_id,
            result.map((e) => e.userCode)
          );
          return;
        }
        this.response = data.message;
        this.loading = false;
      } catch (error) {
        console.error("Error fetching employee IDs:", error);
        this.response = "An error occurred while fetching employee data.";
        this.loading = false;
      }
    },
    json_to_csv(data) {
      let rows = "";
      data.forEach((e) => {
        rows += Object.values(e).join(",").trim() + "\n";
      });
      return header + rows;
    },

    export_submit() {
      if (this.responses.length == 0) {
        alert("No result found to download");
        return;
      }

      try {
        j2c(this.responses, "my-csv.csv");
      } catch (error) {
        console.error("Failed to export CSV:", error.message);
        alert("Error: " + error.message);
      }
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
          this.engaged++;
          this.loading = false;
        } catch (error) {
          this.response = error;
        }
      });
    },
  },
};
</script>
