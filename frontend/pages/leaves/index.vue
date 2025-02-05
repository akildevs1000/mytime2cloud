<template>
  <div v-if="can(`leave_application_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog persistent v-model="dialogLeaveGroup" width="500px">
      <v-card>
        <v-card-title dense class="popup_background popup_title">
          Employee - {{ viewEmployeeName }}
          <v-spacer></v-spacer>
          <v-icon @click="dialogLeaveGroup = false" outlined dark>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-data-table
            v-model="ids"
            item-key="id"
            :headers="headersGroupInfo"
            :items="DialogLeaveGroupData"
            :loading="loading"
            :hide-default-footer="true"
            class="elevation-1"
          >
            <template v-slot:item.leave_type="{ item }" center>
              {{ item.leave_type.name }} ({{ item.leave_type.short_name }})
            </template>
            <template v-slot:item.total="{ item }">
              <v-chip color="black" text-color="white">
                {{ item.leave_type_count }}</v-chip
              >
            </template>
            <template v-slot:item.approved="{ item }">
              <v-chip color="primary"> {{ item.employee_used }}</v-chip>
            </template>
            <template v-slot:item.available="{ item }">
              <v-chip class="ma-2" color="green" text-color="white">
                {{ item.leave_type_count - item.employee_used }}</v-chip
              >
            </template>
          </v-data-table>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog persistent v-model="dialogView" width="1000px">
      <WidgetsClose left="990" @click="dialogView = false" />
      <v-card v-if="dialogViewObject && dialogViewObject.id">
        <v-alert dense flat class="grey lighten-3"> Leave Information </v-alert>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="5">
                <v-row>
                  <v-col cols="4">
                    <label for="">
                      <strong>Employee Name</strong>
                    </label>
                  </v-col>
                  <v-col cols="8">
                    <label for="">: {{ dialogViewObject.employee_name }}</label>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4">
                    <strong>Group Name</strong>
                  </v-col>
                  <v-col cols="8">
                    <label for=""
                      >: {{ dialogViewObject.leave_group_name }}</label
                    >
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4">
                    <strong>Application Type</strong>
                  </v-col>
                  <v-col cols="8">
                    <label for="">: {{ dialogViewObject.leave_type }}</label>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4">
                    <strong>From Date</strong>
                  </v-col>
                  <v-col cols="8">
                    <label for="">: {{ dialogViewObject.from_date }}</label>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4">
                    <strong>To Date</strong>
                  </v-col>
                  <v-col cols="8">
                    <label for="">: {{ dialogViewObject.to_date }}</label>
                  </v-col>
                </v-row>
              </v-col>
              <v-col col="7">
                <v-row>
                  <v-col cols="4">
                    <strong>Applied Date </strong>
                  </v-col>
                  <v-col cols="8">
                    <label for="">: {{ dialogViewObject.applied_date }}</label>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4">
                    <strong>Reporting Manager </strong>
                  </v-col>
                  <v-col cols="8">
                    <label for=""
                      >: {{ dialogViewObject.reporting_manager }}</label
                    >
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4">
                    <strong>Status </strong>
                  </v-col>
                  <v-col cols="8">
                    :
                    <v-chip
                      small
                      :color="showStatus(dialogViewObject)?.color"
                      >{{ showStatus(dialogViewObject)?.label }}</v-chip
                    >
                  </v-col>
                </v-row>
                <v-row v-if="dialogViewObject.status == 1">
                  <v-col cols="4">
                    <strong>Approved Date </strong>
                  </v-col>
                  <v-col cols="8">
                    <label for=""
                      >: {{ dialogViewObject.approved_datetime }}</label
                    >
                  </v-col>
                </v-row>
                <v-row v-else-if="dialogViewObject.status == 2">
                  <v-col cols="4">
                    <strong>Rejected Date </strong>
                  </v-col>
                  <v-col cols="8">
                    <label for=""
                      >: {{ dialogViewObject.approved_datetime }}</label
                    >
                  </v-col>
                </v-row>
                <v-row v-if="dialogViewObject.status == 0">
                  <v-col cols="4">
                    <strong>Approve/Reject Notes </strong>
                  </v-col>
                  <v-col cols="8">
                    <v-textarea
                      rows="3"
                      dense
                      outlined
                      v-model="editedItem.approve_reject_notes"
                      placeholder="Notes"
                      :error-messages="
                        errors && errors.approve_reject_notes
                          ? errors.approve_reject_notes[0]
                          : ''
                      "
                    ></v-textarea>
                  </v-col>
                </v-row>
                <v-row v-else>
                  <v-col cols="4">
                    <strong
                      >{{
                        dialogViewObject.status == 1 ? "Approved" : "Rejected"
                      }}
                      Notes
                    </strong>
                  </v-col>
                  <v-col cols="8">
                    : {{ dialogViewObject.approve_reject_notes }}
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <strong> Leave Notes </strong>

                <label for="">: {{ dialogViewObject.reason }}</label>
              </v-col>
            </v-row>
            <v-row>
              <label
                ><strong>Uploaded Documents</strong> ({{
                  document_list.length
                }})</label
              >
              <v-col cols="12" v-if="document_list.length">
                <table style="border-collapse: collapse; width: 100%">
                  <thead>
                    <tr>
                      <th
                        style="
                          border: 1px solid #dddddd;
                          text-align: left;
                          padding: 8px;
                        "
                      >
                        Title
                      </th>
                      <th
                        style="
                          border: 1px solid #dddddd;
                          text-align: center;
                          padding: 8px;
                        "
                      >
                        File
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(d, index) in document_list" :key="index">
                      <td
                        style="
                          border: 1px solid #dddddd;
                          text-align: left;
                          padding: 8px;
                        "
                      >
                        {{ d.key }}
                      </td>
                      <td
                        style="
                          border: 1px solid #dddddd;
                          text-align: center;
                          padding: 8px;
                        "
                      >
                        <a :href="d.value" target="_blank">
                          <v-btn small class="primary">
                            View Uploaded File <v-icon>mdi-open-window</v-icon>
                          </v-btn>
                        </a>
                      </td>
                    </tr>
                    <!-- Add more rows as needed -->
                  </tbody>
                </table>
              </v-col>
            </v-row>

            <v-card-actions class="mt-4" v-if="dialogViewObject.shift_type_id">
              <v-spacer></v-spacer>
              <v-btn
                :disabled="!allowAction(dialogViewObject)"
                class="error align-right mr-5"
                v-if="dialogViewObject.status == 0"
                small
                @click="rejectLeave(dialogViewObject.id)"
              >
                Reject
              </v-btn>

              <v-btn
                :disabled="!allowAction(dialogViewObject)"
                class="primary"
                v-if="dialogViewObject.status == 0"
                small
                @click="approveLeave(dialogViewObject.id)"
                >Approve</v-btn
              >
            </v-card-actions>
            <v-card-actions v-else class="mt-4">
              <v-spacer></v-spacer>
              <v-chip color="red" variant="text">
                Note: Employee Schedule is not available
              </v-chip>
            </v-card-actions>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-card dense flat>
      <v-row>
        <!-- Left Section -->
        <v-col cols="5">
          <v-toolbar dense flat>
            <v-toolbar-title class="font-weight-bold text-subtitle-1">
              Leaves List
            </v-toolbar-title>
            <v-btn dense icon class="ml-2" title="Reload" @click="clearFilters">
              <v-icon>mdi-reload</v-icon>
            </v-btn>
          </v-toolbar>
        </v-col>

        <!-- Right Section -->
        <v-col cols="7">
          <v-row justify="end" align="center">
            <v-col>
              <v-select
                class="employee-schedule-cropdown"
                v-model="filters.branch_id"
                :items="[
                  { branch_name: 'All Branches', id: '' },
                  ...branchesList,
                ]"
                item-text="branch_name"
                item-value="id"
                outlined
                dense
                hide-details
                placeholder="All Branches"
                @change="getDataFromApi"
              ></v-select>
            </v-col>

            <v-col>
              <v-select
                v-model="filters.leave_type_id"
                :items="[{ name: 'All Leave Types', id: '' }, ...leaveTypes]"
                item-text="name"
                item-value="id"
                outlined
                dense
                hide-details
                placeholder="Leave Types"
                @change="applyFilters('leave_type_id', $event)"
              ></v-select>
            </v-col>

            <v-col>
              <v-select
                v-model="filters.status"
                :items="[
                  { value: '', title: 'All' },
                  { value: 'approved', title: 'Approved' },
                  { value: 'rejected', title: 'Rejected' },
                  { value: 'pending', title: 'Pending' },
                ]"
                item-value="value"
                item-text="title"
                outlined
                dense
                hide-details
                placeholder="Status"
                @change="applyFilters('status', $event)"
              ></v-select>
            </v-col>

            <v-col>
              <CustomDateFilter
                @filter-attr="filterAttr"
                :defaultFilterType="1"
                height="40px"
              />
            </v-col>

            <v-col cols="1">
              <v-menu offset-y>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon>
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list dense>
                  <v-list-item @click="export_submit">
                    <v-list-item-title class="d-flex align-center">
                      <v-icon class="mr-2" color="primary">mdi-download</v-icon>
                      <span class="text-caption">Export Leaves</span>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-card>

    <v-card class="mb-5 mt-2 pt-2 rounded-md" elevation="0">
      <v-data-table
        v-if="can(`leave_application_view`)"
        v-model="ids"
        item-key="id"
        :headers="headers"
        :items="data"
        :loading="loading"
        :footer-props="{
          itemsPerPageOptions: [10, 50, 100, 500, 1000],
        }"
        class="elevation-1"
        :options.sync="options"
        :server-items-length="totalRowsCount"
      >
        <template v-slot:item.employee="{ item }">
          <v-row no-gutters>
            <v-col
              style="
                padding: 5px;
                padding-left: 0px;
                width: 50px;
                max-width: 50px;
              "
            >
              <v-img
                style="
                  border-radius: 50%;
                  height: auto;
                  width: 50px;
                  max-width: 50px;
                "
                :src="
                  item.employee.profile_picture
                    ? item.employee.profile_picture
                    : '/no-profile-image.jpg'
                "
              >
              </v-img>
            </v-col>
            <v-col style="padding: 10px">
              <div style="font-size: 13px">
                {{ item.employee ? item.employee.first_name : "" }}
                {{ item.employee ? item.employee.last_name : "" }}
              </div>
              <small style="font-size: 12px; color: #6c7184"
                >{{ item?.employee?.designation?.name || "---" }}

                1111111</small
              >
            </v-col>
          </v-row>
        </template>

        <template v-slot:item.branch.name="{ item }">
          <div style="font-size: 13px">
            {{
              item.employee.department &&
              item.employee.department.branch &&
              item.employee.department.branch.branch_name
            }}
          </div>
        </template>

        <template v-slot:item.group.name="{ item }">
          <div style="font-size: 13px">
            {{
              item.employee.leave_group && item.employee.leave_group.group_name
            }}
          </div>
        </template>
        <template v-slot:item.leave_type.name="{ item }">
          <div style="font-size: 13px">
            {{ item.leave_type.name }}
          </div>
        </template>
        <template v-slot:item.start_date="{ item }">
          <div style="font-size: 13px">
            {{ item.start_date }}
          </div>
        </template>
        <template v-slot:item.end_date="{ item }">
          <div style="font-size: 13px">
            {{ item.end_date }}
          </div>
        </template>
        <template v-slot:item.reason="{ item }">
          <div style="font-size: 13px">
            {{ item.reason.substr(0, 30) + "..." }}
          </div>
        </template>
        <template v-slot:item.reporting="{ item }">
          <div style="font-size: 13px">
            {{ item.reporting.first_name }} {{ item.reporting.last_name }}
          </div>
        </template>
        <template v-slot:item.created_at="{ item }">
          <div style="font-size: 13px">
            {{ getCurrentDateTime(item.created_at) }}
          </div>
        </template>

        <template v-slot:item.employee_leave_timelines="{ item }">
          <LeavesTimeline
            :key="item.id"
            :items="item.employee_leave_timelines"
          />
        </template>

        <template v-slot:item.status="{ item }">
          <v-chip
            small
            class="p-2 mx-1"
            :color="showStatus(item, true)?.color"
            >{{ showStatus(item, true)?.label }}</v-chip
          >
        </template>

        <template v-slot:item.action="{ item }">
          <v-menu bottom left :nudge-width="100">
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="120" dense>
              <v-list-item @click="view(item)">
                <v-list-item-title style="cursor: pointer; font-size: 13px">
                  <v-icon
                    v-if="can(`leave_application_view`)"
                    color="primary"
                    small
                    @click="view(item)"
                  >
                    mdi-information
                  </v-icon>
                  View Application
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                @click="
                  gotoGroupDetails(
                    item.employee.leave_group_id,
                    item.employee.id,
                    item.employee.full_name
                  )
                "
              >
                <v-list-item-title style="cursor: pointer; font-size: 13px">
                  <v-icon
                    v-if="can(`leave_application_view`)"
                    color="primary"
                    small
                  >
                    mdi-calendar
                  </v-icon>
                  Statistics
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
        <template v-slot:no-data>
          <!-- <v-btn color="primary" @click="initialize">Reset</v-btn> -->
        </template>
      </v-data-table>
    </v-card>
  </div>

  <NoAccess v-else />
</template>
<script>
import {
  TiptapVuetify,
  Image,
  Heading,
  Bold,
  Italic,
  Strike,
  Underline,
  Code,
  Paragraph,
  BulletList,
  OrderedList,
  ListItem,
  Link,
  Blockquote,
  HardBreak,
  HorizontalRule,
  History,
} from "tiptap-vuetify";

export default {
  components: {
    TiptapVuetify,
  },
  data: () => ({
    from_menu_filter: "",
    from_date_filter: "",
    to_date_filter: "",
    created_at_filter: "",
    to_menu_filter: "",
    created_at_menu_filter: "",
    leaveGroups: [],
    document_list: [],
    totalRowsCount: 0,
    options: {},
    filters_select_all: "",
    viewEmployeeName: "",
    filters: {},
    isFilter: false,
    DialogLeaveGroupData: [],
    dialogLeaveGroup: false,
    attrs: {},
    dialogView: false,
    dialogViewObject: {
      id: "",
      employee_name: "",
      leave_type: "",
      from_date: "",
      to_date: "",
      approved_manager: "",
      status: "",
      reason: "",
      applied_date: "",
      leave_group_name: "",
      reporting_manager: "",
      approved_datetime: "",
    },
    leaveTypes: [],
    formTitle: "New Leave Application",
    dialogEmployees: false,
    idsEmployeeList: [],
    //editor
    datatable_search_textbox: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    extensions: [
      History,
      Blockquote,
      Link,
      Image,
      Underline,
      Strike,
      Italic,
      ListItem,
      BulletList,
      OrderedList,
      [
        Heading,
        {
          options: {
            levels: [1, 2, 3],
          },
        },
      ],
      Bold,
      Link,
      Code,
      HorizontalRule,
      Paragraph,
      HardBreak,
    ],
    // starting editor's content
    content: `
      <h1>Yay Headlines!</h1>
      <p>All these <strong>cool tags</strong> are working now.</p>
        `,

    //end editor
    scrollInvoked: 0,
    start_menu: false,
    end_menu: false,
    title: "",
    des: "",
    desDate: "",
    dept: "",
    options: {},
    Model: "leaves",
    endpoint: "employee_leaves",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    departments: [],
    loading: false,
    total: 0,
    headersGroupInfo: [
      {
        text: "Leave Type",
        align: "left",
        sortable: false,
        key: "name",
        value: "leave_type",
      },
      {
        text: "Total",
        align: "center",
        sortable: false,
        key: "name",
        value: "total",
      },
      {
        text: "Approved",
        align: "center",
        sortable: false,
        key: "name",
        value: "approved",
      },
      {
        text: "Available",
        align: "center",
        sortable: false,
        key: "name",
        value: "available",
      },
    ],
    headers: [
      {
        text: "Employee",
        align: "left",
        sortable: true,
        filterable: false,
        key: "employee_name",
        value: "employee",
      },
      {
        text: "Branch",
        align: "left",
        sortable: true,
        filterable: true,
        key: "branch_id",
        value: "employee.branch.branch_name",
        filterSpecial: true,
      },
      {
        text: "Group Type",
        align: "left",
        filterable: true,
        sortable: false,
        value: "group.name",
        key: "group_name_id",
        filterSpecial: true,
      },
      {
        text: "Leave Type",
        align: "left",
        filterable: true,
        sortable: true,
        value: "leave_type.name",
        key: "leave_type_id",
        filterSpecial: true,
      },
      {
        text: "Star Date",
        align: "left",
        filterable: true,
        sortable: true,
        value: "start_date",
        key: "start_date",
        filterSpecial: true,
      },
      {
        text: "End Date",
        align: "left",
        filterable: true,
        sortable: true,
        value: "end_date",
        key: "end_date",
        filterSpecial: true,
      },
      {
        text: "Leave Note",
        align: "left",
        filterable: false,
        sortable: true,
        value: "reason",
        key: "leave_note",
      },
      {
        text: "Reporting ",
        align: "left",
        sortable: false,
        filterable: false,
        value: "reporting",
        key: "reporting_manager",
      },
      {
        text: "Applied On ",
        align: "left",
        sortable: true,
        filterable: true,
        value: "created_at",
        key: "created_at",
        filterSpecial: true,
      },
      {
        text: "Timeline",
        align: "left",
        sortable: false,
        filterable: false,
        value: "employee_leave_timelines",
        key: "employee_leave_timelines",
        filterSpecial: false,
      },
      {
        text: "Status",
        align: "left",
        filterable: true,
        sortable: true,
        value: "status",
      },

      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    editedIndex: -1,
    editedItem: {
      leave_type_id: "",
      reason: "",
      start_date: null,
      end_date: null,
      approve_reject_notes: "",
    },
    defaultItem: {
      leave_type_id: "",
      reason: "",
      approve_reject_notes: "",
      start_date: null,
      end_date: null,
    },
    response: "",
    data: [],
    errors: {},
    options_dialog: {},
    employees_dialog: [],
    selectAllDepartment: false,
    selectAllEmployee: false,
    DialogEmployeesData: {},
    branchesList: [],
    //login_user_employee_id: "",
  }),

  computed: {},

  // watch: {
  //   options: {
  //     handler() {
  //       this.getDataFromApi();
  //     },
  //     deep: true,
  //   },
  // },
  created() {
    this.loading = true;
    this.getLeaveTypes();
    this.getLeaveGroups();
    this.getbranchesList();
    this.getDataFromApi();
  },

  methods: {
    allowAction(item) {
      let user = this.$auth.user;
      if (user?.user_type == "department") {
        return (
          item.department_id == user?.department_id &&
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
    filterAttr(data) {
      this.filters[`start_date`] = data.from;
      this.filters[`end_date`] = data.to;

      this.getDataFromApi();
    },
    json_to_csv(json) {
      let data = json.map((e) => ({
        Employee:
          `${e?.employee?.first_name} ${e?.employee?.last_name}` || "---",
        "Emp Id/Device Id":
          `${e?.employee?.employee_id}/${e?.employee?.system_user_id}` || "---",
        Branch: e?.employee?.branch?.branch_name || "---",
        "Group Type": e?.group?.name || "---",
        "Leave Type": e?.leave_type?.name || "---",
        "Start Date": e.start_date,
        "End Date": e.end_date,
        "Leave Note": e.reason,
        Reporting: e.reporting_manager || "---",
        "Applied On": e.created_at,
        Status: e.status,
      }));
      let header = Object.keys(data[0]).join(",") + "\n";
      let rows = "";
      data.forEach((e) => {
        rows += Object.values(e).join(",").trim() + "\n";
      });
      return header + rows;
    },
    export_submit() {
      if (this.data.length == 0) {
        this.snackbar = true;
        this.response = "No record to download";
        return;
      }

      let csvData = this.json_to_csv(this.data);
      let element = document.createElement("a");
      element.setAttribute(
        "href",
        "data:text/csv;charset=utf-8, " + encodeURIComponent(csvData)
      );
      element.setAttribute("download", "download.csv");
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
    },
    getbranchesList() {
      this.payloadOptions = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(`branches_list`, this.payloadOptions).then(({ data }) => {
        this.branchesList = data;
        this.branch_id = this.$auth.user.branch_id || "";
      });
    },
    applyFilters(filter_column = "", filter_value = "") {
      this.from_menu_filter = false;
      this.to_menu_filter = false;
      this.created_at_menu_filter = false;
      this.getDataFromApi("", filter_column, filter_value);
    },
    toggleFilter() {
      this.isFilter = !this.isFilter;
    },
    view(item) {
      this.dialogViewObject.order = item.order;
      this.dialogViewObject.id = item.id;
      this.dialogViewObject.employee_name =
        item.employee.first_name + " " + item.employee.last_name;
      this.dialogViewObject.system_user_id = item.employee.system_user_id;
      this.dialogViewObject.shift_type_id =
        item.employee.schedule.shift_type_id;

      this.dialogViewObject.leave_type = item.leave_type.name;
      this.dialogViewObject.from_date = item.start_date;
      this.dialogViewObject.to_date = item.end_date;
      this.dialogViewObject.approved_manager =
        item.reporting.first_name + " " + item.reporting.last_name;
      this.dialogViewObject.status = item.status;
      this.dialogViewObject.reason = item.reason;
      this.dialogViewObject.applied_date = this.getCurrentDateTime(
        item.created_at
      );
      this.dialogViewObject.leave_group_name = item.employee.leave_group
        ? item.employee.leave_group.group_name
        : "--";
      this.dialogViewObject.approved_datetime = item.updated_at
        ? this.getCurrentDateTime(item.updated_at)
        : "--";
      this.dialogViewObject.reporting_manager = item.reporting
        ? item.reporting.first_name + " " + item.reporting.last_name
        : "--";
      this.dialogViewObject.approve_reject_notes = item.approve_reject_notes;

      this.dialogViewObject.department_id = item.employee.department_id;

      this.dialogView = true;

      this.document_list = [];

      this.getInfo(item.id);
    },
    getInfo(leave_id) {
      this.$axios
        .get(`employee_document`, {
          params: {
            company_id: this.$auth?.user?.company?.id,
            //employee_id: this.login_user_employee_id,
            leave_id: leave_id,
          },
        })
        .then(({ data }) => {
          this.document_list = data;
          this.loading = false;
        });
    },
    getCurrentDateTime(date) {
      let now = new Date(date);

      let year = now.getFullYear();
      let day = ("0" + now.getDate()).slice(-2);
      let month = ("0" + (now.getMonth() + 1)).slice(-2);

      let formattedDateTime = year + "-" + month + "-" + day; //+ " " + hours + ":" + minutes;

      return formattedDateTime;
    },
    update_EdititemStart() {
      this.$refs.from_menu.save(this.editedItem.start_date);
      this.from_menu = false;
    },
    update_EdititemEnd() {
      this.$refs.end_menu.save(this.editedItem.end_date);
      this.end_menu = false;
    },
    gotoGroupDetails(leaveGroupId, employee_id, employee_name) {
      this.dialogLeaveGroup = true;
      this.viewEmployeeName = employee_name;
      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
          employee_id: employee_id,
        },
      };
      this.$axios
        .get("leave_groups/" + leaveGroupId, options)
        .then(({ data }) => {
          this.DialogLeaveGroupData = data[0].leave_count;
        });
    },
    toggleDepartmentSelection() {
      this.selectAllDepartment = !this.selectAllDepartment;
    },
    toggleEmployeeSelection() {
      this.selectAllEmployee = !this.selectAllEmployee;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    onScroll() {
      this.scrollInvoked++;
    },
    getLeaveTypes() {
      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`leave_type`, options).then(({ data }) => {
        this.leaveTypes = data.data;
      });
    },

    getLeaveGroups() {
      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`leave_groups`, options).then(({ data }) => {
        this.leaveGroups = data.data;
      });
    },

    clearFilters() {
      this.filters = {};
      this.isFilter = false;
      this.getDataFromApi();
    },

    getDataFromApi() {
      this.loading = true;

      let endDate = new Date();

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      if (this.filters) {
        page = 1;
      }

      let user = this.$auth.user;

      let department_id =
        user?.user_type == "department" ? user?.department_id : null;

      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          ...this.filters,
          department_id,
          company_id: this.$auth.user.company_id,
          year: endDate.getFullYear(),
          order: this.$auth.user.order,
        },
      };

      this.$axios.get(`employee_leaves`, options).then(({ data }) => {
        this.data = data.data;
        this.total = data.total;
        this.loading = false;
        this.totalRowsCount = data.total;
      });
    },

    editItem(item) {
      this.formTitle = "Edit leaves Information";
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    delteteSelectedRecords() {
      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`${this.endpoint} / delete /selected`, {
            ids: this.ids.map((e) => e.id),
          })
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.snackbar = data.status;
              this.ids = [];
              this.response = "Selected records has been deleted";
            }
            this.getDataFromApi();
          })
          .catch((err) => console.log(err));
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(this.endpoint + "/" + item.id)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
    },

    close() {
      this.dialog = false;
      this.dialogView = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    getEmployees() {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          page: page,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(`employee`, options).then(({ data }) => {
        this.employees_dialog = data.data;
      });
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
              this.getDataFromApi();
              this.dialogView = false;
            }
          });
      }
    },
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
            system_user_id: this.dialogViewObject.system_user_id,
            shift_type_id: this.dialogViewObject.shift_type_id,
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
              this.getDataFromApi();
              this.dialogView = false;
            }
          });
      }
    },
    save() {
      this.editedItem.company_id = this.$auth.user.company_id;
      this.editedItem.employee_id = this.login_user_employee_id;
      this.editedItem.reporting_manager_id =
        this.$auth.user.reporting_manager_id;
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          //employee_id: this.login_user_employee_id,
          reporting_manager_id: this.$auth.user.reporting_manager_id,
          leave_type_id: this.editedItem.leave_type_id,
          start_date: this.editedItem.start_date,
          end_date: this.editedItem.end_date,
          reason: this.editedItem.reason,
        },
      };

      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, options.params)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.errors = [];
              this.search = "";
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, this.editedItem)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.errors = [];
              this.search = "";
            }
          })
          .catch((res) => console.log(res));
      }
    },
  },
};
</script>
