<template>
  <v-card flat>
    <v-card-text>
      <v-row class="d-flex align-center">
        <v-col cols="12">
          <v-tabs v-model="tab">
            <v-tab>Settings</v-tab>
            <v-tab>Login</v-tab>
            <v-tab>RFID</v-tab>
          </v-tabs>
          <v-tabs-items v-model="tab">
            <v-tab-item>
              <v-card v-if="employeeId" flat class="d-flex flex-column">
                <div class="text-right">
                  <v-icon small color="primary" @click="editForm = !editForm"
                    >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
                  >
                </div>
                <v-simple-table dense flat class="my-simple-table">
                  <tbody>
                    <tr>
                      <td style="width: 50%">Leave Group</td>
                      <td>
                        <v-autocomplete
                          :append-icon="!editForm ? '' : 'mdi-menu-down'"
                          :items="leave_groups"
                          item-text="group_name"
                          item-value="id"
                          autofocus
                          :readonly="!editForm"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="setting.leave_group_id"
                          color="primary"
                          :hide-details="!errors.leave_group_id"
                          :error-messages="
                            errors && errors.leave_group_id
                              ? errors.leave_group_id[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 50%">Leave Manager/Reporting Manger</td>
                      <td>
                        <v-autocomplete
                          :append-icon="!editForm ? '' : 'mdi-menu-down'"
                          :items="leave_managers"
                          :item-text="getEmployeeName"
                          item-value="id"
                          :readonly="!editForm"
                          class="small-input-font"
                          style="border-bottom: 1px solid #eaeaea"
                          dense
                          v-model="setting.reporting_manager_id"
                          color="primary"
                          :hide-details="!errors.reporting_manager_id"
                          :error-messages="
                            errors && errors.reporting_manager_id
                              ? errors.reporting_manager_id[0]
                              : ''
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 50%">Employee Status</td>
                      <td style="width: 50%">
                        <v-switch
                          :disabled="!editForm"
                          dense
                          hide-details
                          color="success"
                          class="mt-0 ml-2"
                          v-model="setting.status"
                          @change="isStatusChanged = true"
                        ></v-switch>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 50%">Web Login Access</td>
                      <td style="width: 50%">
                        <v-switch
                          :disabled="!editForm"
                          dense
                          hide-details
                          color="success"
                          class="mt-0 ml-2"
                          v-model="setting.user.web_login_access"
                        ></v-switch>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 50%">Mobile App Login Access</td>
                      <td style="width: 50%">
                        <v-switch
                          :disabled="!editForm"
                          dense
                          hide-details
                          color="success"
                          class="mt-0 ml-2"
                          v-model="setting.user.mobile_app_login_access"
                        ></v-switch>
                      </td>
                    </tr>

                    <tr>
                      <td style="width: 50%">Over Time</td>
                      <td style="width: 50%">
                        <div class="text-overline mb-1">
                          <v-switch
                            :disabled="!editForm"
                            dense
                            hide-details
                            color="success"
                            class="mt-0 ml-2"
                            v-model="setting.overtime"
                          ></v-switch>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 50%">Whatsapp OTP</td>
                      <td style="width: 50%">
                        <div class="text-overline mb-1">
                          <v-switch
                            :disabled="!editForm"
                            dense
                            hide-details
                            color="success"
                            class="mt-0 ml-2"
                            v-model="setting.user.enable_whatsapp_otp"
                          ></v-switch>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 50%">
                        Location Tracking(Mobile Login)
                      </td>
                      <td style="width: 50%">
                        <div class="text-overline mb-1">
                          <v-switch
                            :disabled="!editForm"
                            dense
                            hide-details
                            color="success"
                            class="mt-0 ml-2"
                            v-model="setting.user.tracking_status"
                          ></v-switch>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </v-simple-table>
                <v-card-actions class="mt-auto">
                  <v-spacer></v-spacer>
                  <v-btn
                    :disabled="!editForm"
                    x-small
                    class="grey white--text"
                    @click="close"
                    >Cancel</v-btn
                  >
                  <v-btn
                    v-if="can('employee_edit')"
                    :disabled="!editForm"
                    x-small
                    class="primary"
                    :loading="loading"
                    @click="update_setting"
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-tab-item>

            <v-tab-item>
              <EmployeeViewLogin
                :employeeId="employeeId"
                :employeeObject="employeeObject"
                @eventFromchild="close"
              />
            </v-tab-item>
            <v-tab-item>
              <EmployeeViewRfid
                :employeeId="employeeId"
                :employeeObject="employeeObject"
                @eventFromchild="close"
              />
            </v-tab-item> </v-tabs-items
        ></v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  props: ["employeeId"],
  data() {
    return {
      tab: null,
      editForm: false,
      response: "",
      snackbar: false,
      setting: {
        user: {},
      },
      leave_managers: [],
      leave_groups: [],
      errors: [],
      loading: false,
      isStatusChanged: false,
    };
  },
  created() {
    this.payloadOptions = {
      params: {
        per_page: 10,
        company_id: this.$auth.user.company_id,
      },
    };

    this.getInfo(this.employeeId);

    this.getLeaveGroups();
    this.getLeaveManagers();
  },
  methods: {
    close() {
      this.$emit("close-popup");
    },
    can(permission) {
      return true;
    },
    getLeaveGroups() {
      this.payloadOptions.params.company_id = this.$auth.user.company_id;
      this.loading = true;
      this.$axios.get(`leave_groups`, this.payloadOptions).then(({ data }) => {
        this.leave_groups = data.data;
        this.loading = false;
      });
    },
    getLeaveManagers() {
      this.loading = true;
      this.payloadOptions.params.company_id = this.$auth.user.company_id;

      this.$axios.get(`employeesList`, this.payloadOptions).then(({ data }) => {
        this.leave_managers = data.data;
        this.loading = false;
      });
    },
    getEmployeeName(item) {
      return item.first_name ? item.first_name + " " + item.last_name : "---";
    },

    getInfo(id) {
      this.loading = true;
      this.$axios.get(`employee/${id}`).then(({ data }) => {
        this.employeeId = data.id;

        this.setting = {
          ...data,
        };
        this.loading = false;
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
        lockDevice: !this.setting.status ? 1 : 0,
        overtime: this.setting.overtime,
        leave_group_id: this.setting.leave_group_id,
        reporting_manager_id: this.setting.reporting_manager_id,
        user_id: this.setting.user_id,
        mobile_app_login_access: this.setting.user.mobile_app_login_access,
        web_login_access: this.setting.user.web_login_access,
        enable_whatsapp_otp: this.setting.user.enable_whatsapp_otp,
        tracking_status: this.setting.user.tracking_status,
      };

      // return;
      this.$axios
        .post(`employee/update/setting`, payload)
        .then(async ({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Settings has been successfully updated";
            await this.handleExpiry();
            setTimeout(() => this.$emit("close-popup"), 3000);
          }
        })
        .catch((e) => console.log(e));
    },

    async handleExpiry() {
      this.$axios
        .post(`setUserExpiry/${this.$auth?.user?.company?.id}`, {
          name: this.setting.first_name + " " + this.setting.last_name,
          userCode: this.setting.system_user_id,
          lockDevice: !this.setting.status ? 1 : 0,
        })
        .then(({ data }) => {});
    },
  },
};
</script>
