<template>
  <span>
    <style scoped>
      .v-date-picker-table .v-btn--active {
        background: white !important;
        color: black !important;
      }

      .v-date-picker-table .v-btn--active::before {
        opacity: 0 !important;
      }

      .v-date-picker-table {
        height: 192px !important;
      }

      .v-date-picker-table--date .v-btn {
        height: 25px !important;
        width: 25px !important;
        font-size: 10px !important;
      }
      .v-date-picker-table--date th {
        padding: 0 !important;
      }
    </style>
    <v-dialog :key="leaveDialogKey" persistent v-model="dialog" width="800px">
      <WidgetsClose left="790" @click="close" />
      <template v-slot:activator="{ on, attrs }">
        <span v-bind="attrs" v-on="on">
          <v-icon color="secondary" small>mdi-eye</v-icon> View
        </span>
      </template>

      <v-card v-if="editedItem && editedItem.id">
        <v-alert flat dense class="primary white--text">
          <span>
            Leave Information for {{ editedItem?.employee?.full_name }}
          </span>
        </v-alert>
        <v-card-text>
          <v-tabs color="deep-purple accent-4" right>
            <v-tab>Info</v-tab>
            <v-tab>Documents</v-tab>
            <v-tab>Leave Quotta</v-tab>
            <v-tab-item>
              <v-container>
                <v-row>
                  <v-col cols="7">
                    <v-row>
                      <v-col cols="8">
                        <v-row dense align="center">
                          <v-col cols="12">
                            <v-text-field
                              outlined
                              dense
                              hide-details
                              v-model="fromDateFormatted"
                              label="From Date"
                              readonly
                            ></v-text-field>
                          </v-col>
                          <v-col cols="12" class="text-right">
                            <v-text-field
                              outlined
                              dense
                              hide-details
                              v-model="toDateFormatted"
                              label="To Date"
                              readonly
                            ></v-text-field>
                          </v-col>
                        </v-row>
                      </v-col>
                      <v-col cols="4">
                        <v-card
                          outlined
                          style="
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            min-height: 88px;
                          "
                        >
                          <b>{{ dayDifference }} Days</b>
                        </v-card>
                      </v-col>
                      <v-col md="12" sm="12" cols="12">
                        <v-text-field
                          append-icon=""
                          label="Reporting Manager"
                          v-model="editedItem.reporting_manager"
                          hide-details
                          dense
                          outlined
                          readonly
                        ></v-text-field>
                      </v-col>

                      <v-col md="12" sm="12" cols="12">
                        <v-text-field
                          append-icon=""
                          label="Applied Date"
                          v-model="editedItem.applied_date"
                          hide-details
                          dense
                          outlined
                          readonly
                        ></v-text-field>
                      </v-col>
                      <v-col
                        v-if="editedItem.alternate_employee"
                        md="12"
                        sm="12"
                        cols="12"
                      >
                        <v-card outlined>
                          <v-alert dense flat class="grey lighten-3"
                            ><small>Alertnate Employee Info</small></v-alert
                          >
                          <v-row align="center" class="px-7">
                            <v-col cols="3">
                              <v-avatar size="80">
                                <!-- src="https://cdn.vuetifyjs.com/images/lists/1.jpg" -->
                                <img
                                  :src="
                                    editedItem?.alternate_employee
                                      ?.profile_picture
                                  "
                                  alt="Profile Picture"
                                />
                              </v-avatar>
                            </v-col>

                            <v-col>
                              <div class="d-flex justify-space-between pt-2">
                                <div style="font-size: 12px">Full Name:</div>
                                <div style="font-size: 12px">
                                  {{
                                    editedItem?.alternate_employee?.full_name
                                  }}
                                </div>
                              </div>
                              <v-divider></v-divider>
                              <div class="d-flex justify-space-between">
                                <div style="font-size: 12px">Employee Id:</div>
                                <div style="font-size: 12px">
                                  {{
                                    editedItem?.alternate_employee?.employee_id
                                  }}
                                </div>
                              </div>
                              <v-divider></v-divider>
                              <div class="d-flex justify-space-between">
                                <div style="font-size: 12px">Dept:</div>
                                <div style="font-size: 12px">
                                  {{
                                    editedItem?.alternate_employee?.department
                                      ?.name
                                  }}
                                </div>
                              </div>
                              <v-divider></v-divider>
                              <div class="d-flex justify-space-between">
                                <div style="font-size: 12px">Desg:</div>
                                <div style="font-size: 12px">
                                  {{
                                    editedItem?.alternate_employee?.designation
                                      ?.name
                                  }}
                                </div>
                              </div>
                            </v-col>
                          </v-row>
                        </v-card>
                      </v-col>
                      <v-col md="6" sm="6" cols="6">
                        <v-text-field
                          append-icon=""
                          label="Group Name"
                          v-model="editedItem.employee.leave_group.group_name"
                          hide-details
                          dense
                          outlined
                          readonly
                        ></v-text-field>
                      </v-col>
                      <v-col md="6" sm="6" cols="6">
                        <v-text-field
                          append-icon=""
                          label="Leave Type"
                          v-model="editedItem.leave_type.short_name"
                          hide-details
                          dense
                          outlined
                          readonly
                        ></v-text-field>
                      </v-col>

                      <v-col md="12" sm="12" cols="12">
                        <v-textarea
                          rows="2"
                          label="Note"
                          dense
                          outlined
                          v-model="editedItem.reason"
                          placeholder="Reason/Notes"
                          hide-details
                          readonly
                        ></v-textarea>
                      </v-col>
                      <v-col
                        v-if="editedItem.status == 1 || editedItem.status == 2"
                        md="12"
                        sm="12"
                        cols="12"
                      >
                        <v-text-field
                          append-icon=""
                          :label="`${
                            editedItem.status == 1
                              ? 'Approved Date'
                              : 'Rejected Date'
                          }`"
                          v-model="editedItem.approved_or_rejected_datetime"
                          hide-details
                          dense
                          outlined
                          readonly
                        ></v-text-field>
                      </v-col>

                      <v-col cols="12" v-if="editedItem.status > 0">
                        <v-text-field
                          :label="`${
                            editedItem.status == 1
                              ? 'Approved Notes'
                              : 'Rejected Notes'
                          }`"
                          v-model="editedItem.approve_reject_notes"
                          hide-details
                          dense
                          outlined
                        ></v-text-field>
                      </v-col>

                      <v-col
                        v-if="editedItem.status == 0"
                        md="12"
                        sm="12"
                        cols="12"
                      >
                        <v-textarea
                          rows="3"
                          append-icon=""
                          label="Approve/Reject Notes"
                          v-model="editedItem.approve_reject_notes"
                          hide-details
                          dense
                          outlined
                        ></v-textarea>
                      </v-col>
                      <v-col cols="6" class="text-right">
                        <v-btn
                          block
                          :disabled="!allowAction"
                          class="error align-right mr-5"
                          v-if="editedItem.status == 0"
                          small
                          @click="rejectLeave(editedItem.id)"
                        >
                          Reject
                        </v-btn>
                      </v-col>
                      <v-col>
                        <v-btn
                          block
                          :disabled="!allowAction"
                          class="primary"
                          v-if="editedItem.status == 0"
                          small
                          @click="approveLeave(editedItem.id)"
                          >Approve</v-btn
                        >
                      </v-col>
                    </v-row>
                  </v-col>
                  <v-col cols="5">
                    <LeavesCalendarView />
                    <LeavesOtherEmployees />
                  </v-col>
                </v-row>
              </v-container>
            </v-tab-item>
            <v-tab-item>
              <v-container>
                <v-row>
                  <v-col cols="12" v-if="Document.items.length">
                    <v-card outlined>
                      <v-simple-table dense class="w-100">
                        <thead>
                          <tr>
                            <th class="text-left">Title</th>
                            <th class="text-left">File</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(d, index) in Document.items" :key="index">
                            <td class="text-left">
                              {{ d.title }}
                            </td>
                            <td class="text-left">
                              <a :href="d.previewUrl" target="_blank">
                                View file
                                <v-icon small>mdi-open-in-new</v-icon>
                              </a>
                            </td>
                          </tr>
                        </tbody>
                      </v-simple-table>
                    </v-card>
                  </v-col>
                </v-row>
              </v-container>
            </v-tab-item>
            <v-tab-item>
              <v-container>
                <v-row>
                  <v-col cols="12" v-if="leaveStats.length">
                    <v-card outlined>
                      <v-simple-table dense class="w-100">
                        <thead>
                          <tr>
                            <th class="text-center" style="font-size: 12px">
                              Leave Group
                            </th>
                            <th class="text-center" style="font-size: 12px">
                              Total
                            </th>
                            <th class="text-center" style="font-size: 12px">
                              Used
                            </th>
                            <th class="text-center" style="font-size: 12px">
                              Available
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(d, index) in leaveStats" :key="index">
                            <td class="text-center" style="font-size: 12px">
                              {{ d.leave_group }}
                            </td>
                            <td class="text-center" style="font-size: 12px">
                              {{ d.total }}
                            </td>
                            <td class="text-center" style="font-size: 12px">
                              {{ d.used }}
                            </td>
                            <td class="text-center" style="font-size: 12px">
                              {{ d.available }}
                            </td>
                          </tr>
                        </tbody>
                      </v-simple-table>
                    </v-card>
                  </v-col>
                </v-row>
              </v-container>
            </v-tab-item>
          </v-tabs>
        </v-card-text>
      </v-card>
    </v-dialog>
  </span>
</template>
<script>
export default {
  props: ["editedItem"],
  data: () => ({
    leaveDialogKey: 1,
    fromMenu: false,
    toMenu: false,
    perPage: 10,
    currentPage: 1,
    totalRowsCount: 0,
    options: {
      current: 1,
      total: 0,
      itemsPerPage: 10,
    },

    dialogUploadDocuments: false,
    valid: true,
    documents: false,
    response: "",
    errors: [],
    Document: {
      items: {
        title: "",
        file: "",
      },
    },
    leaveStats: [],
    newLeaveApplication: true,
    filters: {},
    isFilter: false,
    DialogLeavesList: [],
    DialogLeaveGroupInfo: [],
    dialogLeaveGroup: false,
    attrs: {},
    leaveTypes: [],
    snack: false,
    snackText: "",
    title: "",
    des: "",
    desDate: "",
    dept: "",
    options: {
      current: 1,
      total: 0,
      itemsPerPage: 10,
    },

    Model: "leaves",
    endpoint: "employee_leaves",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,
    data: [],
    todayDate: "",
    login_user_employee_id: "",
    alternate_employee: null,
  }),

  computed: {
    fromDateFormatted() {
      if (!this.editedItem.start_date) return null;
      return this.formatDate(this.editedItem.start_date);
    },
    toDateFormatted() {
      if (!this.editedItem.end_date) return null;
      return this.formatDate(this.editedItem.end_date);
    },
    allowAction() {
      let item = this.editedItem;
      let user = this.$auth.user;
      console.log(
        item.employee.department_id,
        user?.department_id,
        item.status,
        item.order
      );

      if (user?.user_type == "department") {
        return (
          item?.employee?.department_id == user?.department_id &&
          item.status == 0 &&
          item.order == -1
        );
      } else if (item.status == 0 && item.order == -1) {
        return false;
      } else if (user?.order > 0) {
        return item.order >= user?.order;
      }

      return item.order == 0;
    },
    dayDifference() {
      const from = new Date(this.editedItem.start_date);
      const to = new Date(this.editedItem.end_date);
      return Math.max(1, (to - from) / (1000 * 60 * 60 * 24) + 1);
    },
  },
  async created() {
    this.loading = true;
    this.errors = [];

    let now = new Date();

    let year = now.getFullYear();
    let day = ("0" + now.getDate()).slice(-2);
    let month = ("0" + (now.getMonth() + 1)).slice(-2);

    let formattedDateTime = year + "-" + month + "-" + day;

    this.todayDate = formattedDateTime;

    let item = this.editedItem;

    this.alternate_employee = {
      ...item.alternate_employee,
      department: item?.alternate_employee?.department?.name,
      designation: item?.alternate_employee?.designation?.name,
    };

    this.editedItem.approved_or_rejected_datetime = item.updated_at
      ? this.getCurrentDateTime(item.updated_at)
      : "--";

    this.editedItem.applied_date = this.getCurrentDateTime(item.created_at);

    this.editedItem.reporting_manager = item.reporting
      ? item.reporting.first_name + " " + item.reporting.last_name
      : "--";

    this.getInfo(item.id);
    this.verifyAvailableCount(item.employee.leave_group_id);
  },

  methods: {
    
    approveLeave(leaveid) {
      if (this.editedItem.approve_reject_notes == "") {
        this.errors = {
          status: false,
          approve_reject_notes: ["Notes is required"],
        };
        this.errors.status = false;
      } else if (confirm("Are you sure to Approve Leave?")) {
        let options = {
          params: {
            approve_reject_notes: this.editedItem.approve_reject_notes,
            company_id: this.$auth.user.company_id,
            system_user_id: this.editedItem.system_user_id,
            shift_type_id: this.editedItem.shift_type_id,
            order: this.$auth.user.order,
            user_name: this.$auth.user.name,
            user_id: this.$auth.user.id,
          },
        };
        this.$axios
          .post(this.endpoint + "/approve/" + leaveid, options.params)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.snackbar = data.status;
              this.response = data.message;
              this.$emit("response",true);
              this.dialog = false;
            }
          });
      }
    },
    rejectLeave(leaveid) {
      if (this.editedItem.approve_reject_notes == "") {
        this.errors = {
          status: false,
          approve_reject_notes: ["Notes is required"],
        };
        this.errors.status = false;
      } else if (confirm("Are you sure to Reject Leave?")) {
        let options = {
          params: {
            approve_reject_notes: this.editedItem.approve_reject_notes,
            company_id: this.$auth.user.company_id,
            user_name: this.$auth.user.name,
            user_id: this.$auth.user.id,
          },
        };
        this.$axios
          .get(this.endpoint + "/reject/" + leaveid, options)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.snackbar = data.status;
              this.response = data.message;
              this.$emit("response",true);
              this.dialog = false;
            }
          });
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString("en-US", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      });
    },
    verifyAvailableCount(leaveGroupId) {
      if (leaveGroupId) {
        let options = {
          params: {
            per_page: 1000,
            company_id: this.$auth.user.company_id,
          },
        };
        this.$axios
          .get("leave_groups/" + leaveGroupId, options)
          .then(({ data }) => {
            this.leaveTypes = data[0].leave_count;
          });
      }

      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
          employee_id: this.editedItem.employee.id,
        },
      };
      this.$axios
        .get("leave_groups/" + leaveGroupId, options)
        .then(({ data }) => {
          this.leaveStats = data[0].leave_count.map((e) => ({
            leave_group: e.leave_type.short_name,
            used: e.employee_used,
            total: e.leave_type_count,
            available: e.leave_type_count - e.employee_used,
          }));
        });
    },

    close() {
      this.leaveDialogKey += 1;
      this.dialog = false;
      this.alternate_employee = null;
    },
    getInfo(leave_id) {
      this.$axios
        .get(`employee_document`, {
          params: {
            company_id: this.$auth?.user?.company?.id,
            employee_id: this.login_user_employee_id,
            leave_id: leave_id,
          },
        })
        .then(({ data }) => {
          // this.Document.items = data;

          this.Document.items = data.map((e) => ({
            title: e.key,
            previewUrl: e.value,
          }));
          this.loading = false;
        });
    },

    close_document_info() {
      this.documents = false;
      this.errors = [];
    },
    getCurrentDateTime(date) {
      let now = new Date(date);

      let year = now.getFullYear();
      let day = ("0" + now.getDate()).slice(-2);
      let month = ("0" + (now.getMonth() + 1)).slice(-2);

      let formattedDateTime = year + "-" + month + "-" + day; //+ " " + hours + ":" + minutes;

      return formattedDateTime;
    },
    showStatus(item, listView = false) {
      let user = this.$auth.user;

      if (item.status == 2) {
        return { label: "Rejected", color: "red white--text" };
      }

      if (user?.user_type == "department") {
        if (listView) {
          return item?.employee?.department_id == user?.department_id &&
            item.status == 0 &&
            item.order == -1
            ? { label: "Pending", color: "secondary" }
            : { label: "Approved", color: "primary" };
        } else {
          return item?.department_id == user?.department_id &&
            item.status == 0 &&
            item.order == -1
            ? { label: "Pending", color: "secondary" }
            : { label: "Approved", color: "primary" };
        }
      } else if (item.status == 0 && item.order == -1) {
        return { label: "Pending", color: "secondary" };
      } else if (user?.order > 0) {
        if (item.status == 0 && item.order == 0) {
          return {
            label: `Approved`,
            color: "primary",
          };
        }
        if (user?.order >= item.order) {
          return { label: "Pending", color: "secondary" };
        } else {
          return {
            label: `Approved ${item.order} - ${user?.order}`,
            color: "primary",
          };
        }
      }

      return item.status == 1 && item.order == 0
        ? { label: "Approved", color: "primary" }
        : { label: "Pending", color: "secondary" };
    },
  },
};
</script>
