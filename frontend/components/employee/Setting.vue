<template>
  <div class="mt-15">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" color="background">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-card class="mb-5 rounded-md" elevation="0">
        <v-toolbar class=" " color="background" dense flat dark>
          <span> Settings</span>
          <v-spacer></v-spacer>
        </v-toolbar>
        <v-col cols="12">

          <v-col col="6">
            <v-col md="6" sm="12" cols="12">
              <label class="col-form-label">Leave Group Name</label>
              <v-autocomplete :items="leave_groups" item-text="group_name" item-value="id" placeholder="Select"
                v-model="setting.leave_group_id" :hide-details="!errors.leave_group_id" :error="errors.leave_group_id"
                :error-messages="errors && errors.leave_group_id
                  ? errors.leave_group_id[0]
                  : ''
                  " dense outlined></v-autocomplete>
            </v-col>
            <v-col md="6" sm="12" cols="12">
              <label class="col-form-label">Leave Manager/Reporting Manger </label>
              <v-autocomplete :items="leave_managers" :item-text="getEmployeeName" item-value="id" placeholder="Select"
                v-model="setting.reporting_manager_id" :hide-details="!errors.reporting_manager_id"
                :error="errors.reporting_manager_id" :error-messages="errors && errors.reporting_manager_id
                  ? errors.reporting_manager_id[0]
                  : ''
                  " dense outlined></v-autocomplete>
            </v-col>



          </v-col>

          <v-col col="6">

            <table style="width:100%">
              <tr>
                <th>Employee Status</th>
                <td>
                  <v-switch color="success" class="mt-0 ml-2" v-model="setting.status"></v-switch>
                </td>
              </tr>

              <tr>
                <th>Mobile App Login</th>
                <td>
                  <v-switch color="success" class="mt-0 ml-2" v-model="setting.mobile_application"></v-switch>
                </td>
              </tr>

              <tr>
                <th>Over Time</th>
                <td>
                  <div class="text-overline mb-1">
                    <v-switch color="success" class="mt-0 ml-2" v-model="setting.overtime"></v-switch>
                  </div>
                </td>
              </tr>
              <tr>
                <th> </th>
                <td>
                  <div class="w-100 text-right">
                    <v-btn small class="primary mt-1 w-25" @click="update_setting">Save</v-btn>
                  </div>
                </td>
              </tr>

            </table>
          </v-col>
        </v-col>
      </v-card>
    </v-row>

  </div>
</template>

<script>
export default {
  props: ["employeeId"],
  data() {
    return {
      response: "",
      snackbar: false,
      setting: {},
      leave_managers: [],
      leave_groups: [],
      errors: [],
    };
  },
  created() {
    this.payloadOptions = {
      params: {
        per_page: 10,
        company_id: this.$auth.user.company.id,
      },
    };
    this.getInfo(this.employeeId);

    this.getLeaveGroups();
    this.getLeaveManagers();
  },
  methods: {
    getLeaveGroups() {
      this.payloadOptions.params.company_id = this.$auth.user.company.id;

      this.$axios.get(`leave_groups`, this.payloadOptions).then(({ data }) => {
        this.leave_groups = data.data;

      });
    },
    getLeaveManagers() {

      this.payloadOptions.params.company_id = this.$auth.user.company.id;

      this.$axios.get(`employeesList`, this.payloadOptions).then(({ data }) => {
        this.leave_managers = data.data;

      });
    },
    getEmployeeName(item) {

      return item.first_name ? item.first_name + ' ' + item.last_name : '---';
    },
    getInfo(id) {
      this.$axios.get(`employee/${id}`).then(({ data }) => {
        this.employeeId = data.id;
        this.setting = {
          ...data,
        };
      });
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    update_setting() {
      let payload = {
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.setting.employee_id,
        status: this.setting.status,
        overtime: this.setting.overtime,
        mobile_application: this.setting.mobile_application,
        leave_group_id: this.setting.leave_group_id,
        reporting_manager_id: this.setting.reporting_manager_id,
      };

      // return;
      this.$axios
        .post(`employee/update/setting`, payload)
        .then(({ data }) => {
          this.loading = false;


          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Setting has been successfully updated";

          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
