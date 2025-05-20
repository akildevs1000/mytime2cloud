<template>
  <div v-if="can(`leave_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

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
        v-if="can(`leave_view`)"
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
              <v-list-item>
                <v-list-item-title style="cursor: pointer; font-size: 13px">
                  <LeavesView :editedItem="item" @response="reloadPage" />
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
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
    snack: false,
    title: "",
    des: "",
    options: {},
    Model: "leaves",
    endpoint: "employee_leaves",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,
    headers: require("../../headers/employee_leaves.json"),
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
    branchesList: [],
  }),
  created() {
    this.loading = true;
    this.getLeaveTypes();
    this.getLeaveGroups();
    this.getbranchesList();
    this.getDataFromApi();
  },

  methods: {
    reloadPage() {
      window.location.reload();
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
          return item?.employee?.department_id == user?.department_id &&
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
        if (item.order == 0) {
          return { label: "Pending", color: "secondary" };
        } else {
          return {
            label: `Approved`,
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
    getCurrentDateTime(date) {
      let now = new Date(date);

      let year = now.getFullYear();
      let day = ("0" + now.getDate()).slice(-2);
      let month = ("0" + (now.getMonth() + 1)).slice(-2);

      let formattedDateTime = year + "-" + month + "-" + day; //+ " " + hours + ":" + minutes;

      return formattedDateTime;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
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
  },
};
</script>
