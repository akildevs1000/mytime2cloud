<template>
  <div v-if="can('role_access')">
    <v-dialog persistent v-model="dialogNewRole" width="800">
      <WidgetsClose left="790" @click="closeDialog" />
      <v-card>
        <v-alert dense flat dark class="primary">
          {{ formTitle }} {{ Model }} and Permissions
          <v-spacer></v-spacer>
        </v-alert>
        <v-card-text>
          <v-row>
            <v-col cols="5">
              <v-text-field
                hide-details
                label="Role Name"
                outlined
                dense
                v-model="editedItem.name"
              ></v-text-field>
              <span v-if="errors && errors.name" class="error--text">
                {{ errors.name[0] }}</span
              >
            </v-col>
            <v-col cols="5">
              <v-text-field
                hide-details
                label="Role Description"
                outlined
                dense
                v-model="editedItem.description"
              ></v-text-field>
              <span v-if="errors && errors.description" class="error--text">
                {{ errors.description[0] }}</span
              >
            </v-col>
            <v-col cols="2" class="text-right">
              <v-btn color="primary" fill small @click="save">Save</v-btn>
            </v-col>

            <v-col cols="12">
              <div style="max-height: 450px; overflow-y: auto">
                <v-card
                  v-for="(menu, index) in topMenus"
                  :key="index"
                  class="mb-2"
                  flat
                >
                  <v-row no-gutters>
                    <v-col>
                      <div class="text-color mx-3 my-1">
                        {{ menu.label }}
                      </div>
                    </v-col>
                    <v-col class="text-right">
                      <div class="mx-3 my-1">
                        <v-icon
                          color="text-color "
                          small
                          text
                          @click="toggleExpand(index)"
                        >
                          mdi-chevron-down
                        </v-icon>
                      </div>
                    </v-col>
                  </v-row>
                  <v-expand-transition>
                    <v-card-text v-if="expandedIndex === index">
                      <style scoped>
                        table {
                          border-spacing: 0;
                          border-collapse: collapse; /* To ensure borders are collapsed like the effect of cellspacing="0" */
                          width: 100%;
                        }
                        td {
                          font-size: 12px;
                        }
                        .border-top {
                          border-top: 1px solid #e0e0e0;
                        }
                        .border-bottom {
                          border-bottom: 1px solid #e0e0e0;
                        }
                      </style>
                      <table>
                        <tr>
                          <td style="width: 30%" class="border-bottom">
                            <div class="text-color"></div>
                          </td>
                          <td class="border-bottom">
                            <div class="text-color text-center">Access</div>
                          </td>
                          <td class="border-bottom">
                            <div class="text-color text-center">View</div>
                          </td>
                          <td class="border-bottom">
                            <div class="text-color text-center">Create</div>
                          </td>
                          <td class="border-bottom">
                            <div class="text-color text-center">Edit</div>
                          </td>
                          <td class="border-bottom">
                            <div class="text-color text-center">Delete</div>
                          </td>
                        </tr>
                        <tr>
                          <td style="width: 30%" class="border-bottom">
                            <div class="text-color">
                              <b>Select All</b>
                            </div>
                          </td>
                          <td class="border-bottom">
                            <div
                              class="text-color text-center d-flex align-center justify-center"
                            >
                              <v-checkbox
                                class="py-1 pl-1 ma-0"
                                color="primary"
                                dense
                                hide-details
                                @change="
                                  (isChecked) =>
                                    selectAllByfilteredMenus(
                                      'access',
                                      menu.name,
                                      isChecked
                                    )
                                "
                              ></v-checkbox>
                            </div>
                          </td>
                          <td class="border-bottom">
                            <div
                              class="text-color text-center d-flex align-center justify-center"
                            >
                              <v-checkbox
                                class="py-1 pl-1 ma-0"
                                color="primary"
                                dense
                                hide-details
                                @change="
                                  (isChecked) =>
                                    selectAllByfilteredMenus(
                                      'view',
                                      menu.name,
                                      isChecked
                                    )
                                "
                              ></v-checkbox>
                            </div>
                          </td>
                          <td class="border-bottom">
                            <div
                              class="text-color text-center d-flex align-center justify-center"
                            >
                              <v-checkbox
                                class="py-1 pl-1 ma-0"
                                color="primary"
                                dense
                                hide-details
                                @change="
                                  (isChecked) =>
                                    selectAllByfilteredMenus(
                                      'create',
                                      menu.name,
                                      isChecked
                                    )
                                "
                              ></v-checkbox>
                            </div>
                          </td>
                          <td class="border-bottom">
                            <div
                              class="text-color text-center d-flex align-center justify-center"
                            >
                              <v-checkbox
                                class="py-1 pl-1 ma-0"
                                color="primary"
                                dense
                                hide-details
                                @change="
                                  (isChecked) =>
                                    selectAllByfilteredMenus(
                                      'edit',
                                      menu.name,
                                      isChecked
                                    )
                                "
                              ></v-checkbox>
                            </div>
                          </td>
                          <td class="border-bottom">
                            <div
                              class="text-color text-center d-flex align-center justify-center"
                            >
                              <v-checkbox
                                class="py-1 pl-1 ma-0"
                                color="primary"
                                dense
                                hide-details
                                @change="
                                  (isChecked) =>
                                    selectAllByfilteredMenus(
                                      'delete',
                                      menu.name,
                                      isChecked
                                    )
                                "
                              ></v-checkbox>
                            </div>
                          </td>
                        </tr>
                        <tr
                          v-for="(childMenu, idx) in filteredMenus(menu.name)"
                          :key="idx"
                        >
                          <td style="width: 30%" class="border-bottom">
                            <div class="text-color">
                              {{ childMenu.title }}
                            </div>
                          </td>
                          <td
                            class="border-bottom"
                            v-for="(perm, permIndex) in filteredPermissions(
                              childMenu.module
                            )"
                            :key="permIndex"
                          >
                            <div
                              class="text-center d-flex align-center justify-center"
                            >
                              <v-checkbox
                                class="py-1 pl-1 ma-0"
                                color="primary"
                                :value="perm.id"
                                v-model="permission_ids"
                                dense
                                hide-details
                                @change="
                                  $emit(`selectedPermissions`, permission_ids)
                                "
                              >
                              </v-checkbox>
                            </div>
                          </td>
                        </tr>
                      </table>
                    </v-card-text>
                  </v-expand-transition>
                  <v-divider></v-divider>
                </v-card>
              </div>
            </v-col>
            <v-col cols="6" class="text-right">
              <span v-if="errors && errors.permission_ids" class="red--text">
                {{ errors.permission_ids[0] }}
              </span>
              <span v-if="errors && errors.name" class="error--text">
                {{ errors.name[0] }}</span
              >
              <span v-if="errors && errors.description" class="error--text">
                {{ errors.description[0] }}</span
              >
            </v-col>
            <v-col class="text-right">
              <v-btn color="primary" fill small @click="save">Save</v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <v-col md="12">
        <v-data-table
          v-model="ids"
          item-key="id"
          :headers="headers"
          :items="data"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [50, 100, 500, 1000],
          }"
          class="elevation-1"
        >
          <template v-slot:top>
            <v-card class="mb-5 rounded-md mt-3" elevation="0">
              <v-toolbar class="rounded-md" dense flat>
                <span> Roles</span>
                <v-tooltip top color="primary">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn
                      dense
                      class="ma-0 px-0"
                      x-small
                      :ripple="false"
                      text
                      v-bind="attrs"
                      v-on="on"
                      @click="getDataFromApi()"
                    >
                      <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
                    </v-btn>
                  </template>
                  <span>Reload</span>
                </v-tooltip>
                <v-spacer></v-spacer>
                <v-toolbar-items>
                  <v-col>
                    <v-btn
                      v-if="can('role_create')"
                      dark
                      dense
                      color="primary"
                      small
                      @click="dispalyNewDialog()"
                    >
                      <v-icon color="white" small> mdi-plus </v-icon>
                      Role
                    </v-btn>
                  </v-col>
                </v-toolbar-items>
              </v-toolbar>
            </v-card>
          </template>

          <template v-slot:item.action="{ item }">
            <v-icon
              v-if="can('role_edit')"
              color="secondary"
              small
              class="mr-2"
              @click="editItem(item)"
            >
              mdi-pencil
            </v-icon>
            <v-icon
              v-if="can('role_delete')"
              color="error"
              small
              @click="deleteItem(item)"
            >
              {{ item.role === "customer" ? "" : "mdi-delete" }}
            </v-icon>
          </template>
          <template v-slot:no-data>
            <!-- <v-btn color="background" @click="initialize">Reset</v-btn> -->
          </template>
        </v-data-table></v-col
      >
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    expandedIndex: null, // Index of the currently expanded item
    topMenus: [
      {
        label: "Dashboard",
        name: "dashboard",
      },
      {
        label: "Employee",
        name: "employee",
      },
      {
        label: "Attendance",
        name: "attendance",
      },
      {
        label: "Payroll",
        name: "payroll",
      },
      {
        label: "Visitor",
        name: "visitor",
      },
      {
        label: "Reports",
        name: "reports",
      },
      {
        label: "Settings",
        name: "settings",
      },
    ],
    menus: [
      {
        topMenu: "dashboard",
        module: "dashboard",
        title: "Dashboard",
      },
      {
        topMenu: "employee",
        module: "employee",
        title: "Employee",
      },
      {
        topMenu: "employee",
        module: "employee_profile",
        title: "Employee Profile",
      },
      {
        topMenu: "employee",
        module: "employee_contact",
        title: "Employee Contact",
      },
      {
        topMenu: "employee",
        module: "employee_home_contact",
        title: "Employee Home Contact",
      },
      {
        topMenu: "employee",
        module: "employee_visa",
        title: "Employee Visa",
      },
      {
        topMenu: "employee",
        module: "employee_emirates",
        title: "Employee Emirates",
      },
      {
        topMenu: "employee",
        module: "employee_passport",
        title: "Employee Passport",
      },
      {
        topMenu: "employee",
        module: "employee_bank",
        title: "Employee Bank",
      },
      {
        topMenu: "employee",
        module: "employee_payroll",
        title: "Employee Payroll",
      },
      {
        topMenu: "employee",
        module: "employee_document",
        title: "Employee Document",
      },
      {
        topMenu: "employee",
        module: "employee_qualification",
        title: "Employee Qualification",
      },
      {
        topMenu: "employee",
        module: "employee_setting",
        title: "Employee Setting",
      },
      {
        topMenu: "employee",
        module: "employee_login",
        title: "Employee Login",
      },
      {
        topMenu: "employee",
        module: "employee_rfid",
        title: "Employee RFID",
      },
      {
        topMenu: "employee",
        module: "leave_application",
        title: "Leave Application",
      },
      {
        topMenu: "employee",
        module: "announcement",
        title: "Announcement",
      },
      {
        topMenu: "employee",
        module: "announcement_category",
        title: "Announcement Category",
      },
      {
        topMenu: "employee",
        module: "employee_upload",
        title: "Employee Photo Upload",
      },
      {
        topMenu: "attendance",
        module: "shift",
        title: "Shift",
      },
      {
        topMenu: "attendance",
        module: "employee_schedule",
        title: "Employee Schedule",
      },
      {
        topMenu: "attendance",
        module: "change_request",
        title: "Change Request",
      },
      {
        topMenu: "payroll",
        module: "payroll",
        title: "Payroll",
      },
      {
        topMenu: "access_control",
        module: "timezone",
        title: "Timezone",
      },
      {
        topMenu: "access_control",
        module: "timezone_mapping",
        title: "Timezone Mapping",
      },
      {
        topMenu: "access_control",
        module: "timezone_device_mapping",
        title: "Timezone Device Mapping",
      },
      {
        topMenu: "visitor",
        module: "visitor_dashboard",
        title: "Visitor Dashboard",
      },
      {
        topMenu: "visitor",
        module: "visitor_request",
        title: "Visitor Request",
      },
      {
        topMenu: "visitor",
        module: "visitor",
        title: "Visitor",
      },
      {
        topMenu: "visitor",
        module: "host",
        title: "Host",
      },
      {
        topMenu: "visitor",
        module: "purpose",
        title: "Purpose",
      },
      {
        topMenu: "visitor",
        module: "visitor_logs",
        title: "Visitor Logs",
      },
      {
        topMenu: "visitor",
        module: "zone",
        title: "Zone",
      },
      {
        topMenu: "visitor",
        module: "visitor_reports",
        title: "Visitor Reports",
      },
      {
        topMenu: "visitor",
        module: "unknown",
        title: "Unknown",
      },
      {
        topMenu: "reports",
        module: "attendance_report",
        title: "Attendance Report",
      },
      {
        topMenu: "reports",
        module: "performance_report",
        title: "Performance Report",
      },
      {
        topMenu: "reports",
        module: "access_control_report",
        title: "Access Control Report",
      },
      {
        topMenu: "reports",
        module: "device_logs",
        title: "Device Logs",
      },
      {
        topMenu: "reports",
        module: "leave",
        title: "Leave",
      },
      {
        topMenu: "reports",
        module: "visitor_reports",
        title: "Visitor Reports",
      },
      {
        topMenu: "reports",
        module: "web_login_logs",
        title: "Web Login",
      },
      {
        topMenu: "reports",
        module: "document_expiry",
        title: "Document Expiry",
      },
      {
        topMenu: "settings",
        module: "company_profile",
        title: "Company Profile",
      },
      {
        topMenu: "settings",
        module: "license",
        title: "License",
      },
      {
        topMenu: "settings",
        module: "document",
        title: "Document",
      },
      {
        topMenu: "settings",
        module: "password",
        title: "Password",
      },
      {
        topMenu: "settings",
        module: "admin",
        title: "Admin",
      },
      {
        topMenu: "settings",
        module: "attendance_rating",
        title: "Attendance Rating",
      },
      {
        topMenu: "settings",
        module: "branch",
        title: "Branch",
      },
      {
        topMenu: "settings",
        module: "department",
        title: "Department",
      },
      {
        topMenu: "settings",
        module: "sub_department",
        title: "Sub Department",
      },
      {
        topMenu: "settings",
        module: "designation",
        title: "Designation",
      },
      {
        topMenu: "settings",
        module: "automation_absent",
        title: "Automation Absent",
      },
      {
        topMenu: "settings",
        module: "automation_attendance",
        title: "Automation Attendance",
      },
      {
        topMenu: "settings",
        module: "automation_device",
        title: "Automation Device",
      },
      {
        topMenu: "settings",
        module: "automation_document",
        title: "Automation Document",
      },
      {
        topMenu: "settings",
        module: "automation_access_control",
        title: "Automation Access Control",
      },
      {
        topMenu: "settings",
        module: "role",
        title: "Role",
      },
      {
        topMenu: "settings",
        module: "device",
        title: "Device",
      },
      {
        topMenu: "settings",
        module: "holiday",
        title: "Holiday",
      },
      {
        topMenu: "settings",
        module: "leave_type",
        title: "Leave Type",
      },
      {
        topMenu: "settings",
        module: "leave_group",
        title: "Leave Group",
      },
      {
        topMenu: "settings",
        module: "payroll_formula",
        title: "Payroll Formula",
      },
      {
        topMenu: "settings",
        module: "payroll_generation",
        title: "Payroll Generation",
      },
      {
        topMenu: "settings",
        module: "automation_mail_content",
        title: "Automation Mail Content",
      },
    ],
    compKey: 1,
    dialogNewRole: false,
    options: {},
    Model: "Role",
    endpoint: "role",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: false,
    total: 0,
    headers: [
      {
        text: "Role",
        align: "left",
        sortable: true,
        key: "name",
        value: "name",
      },
      {
        text: "Description",
        align: "left",
        sortable: true,
        key: "description",
        value: "description",
      },
      {
        text: "Created",
        align: "left",
        sortable: true,
        key: "updated_at",
        value: "updated_at",
      },
      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    editedIndex: -1,
    editedItem: { name: "", description: "" },
    defaultItem: { name: "", description: "" },
    response: "",
    data: [],
    errors: [],

    permission_ids: [],
    permissions: [],
    formTitle: "New",
    editPermissionId: "",
  }),

  computed: {},

  watch: {
    editedIndex(val) {
      this.formTitle = val === -1 ? "New" : "Edit";
    },
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  created() {
    this.loading = true;

    //permissions
    this.getPermissions();
  },

  methods: {
    closeDialog() {
      this.dialogNewRole = false;
      ++this.compKey;
    },
    handleSelectedPermissions(e) {
      this.permission_ids = e;
    },
    dispalyNewDialog() {
      this.errors = [];
      this.editedItem = { name: "", description: "" };
      this.editedIndex = -1;
      this.formTitle = "New";
      this.dialogNewRole = true;
      this.permission_ids = [];
    },

    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
          role_type: "employee",
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.total = data.total;
        this.loading = false;
      });
    },
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },

    editItem(item) {
      console.log(item);
      this.errors = [];
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      //this.dialog = true;
      this.dialogNewRole = true;

      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios
        .get("assign-permission/role-id/" + item.id, options)
        .then(({ data }) => {
          //this.data = data;
          this.loading = false;
          if (data[0]) {
            this.permission_ids = data[0].permission_ids;
            this.editPermissionId = data[0].id;

            //alert(this.editPermissionId);
          }

          // else
          //   this.$router.push("/assign_permission/create");
        });
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
      this.dialogNewRole = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    save() {
      let payload = {
        name: this.editedItem.name,
        description: this.editedItem.description,

        company_id: this.$auth.user.company.id,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              if (this.editPermissionId == "") {
                this.savePermisions(this.editedItem.id);
              } else {
                this.updatePermission(this.editedItem.id);
              }

              this.getDataFromApi();
              // const index = this.data.findIndex(
              //   (item) => item.id == this.editedItem.id
              // );
              // this.data.splice(index, 1, {
              //   id: this.editedItem.id,
              //   name: this.editedItem.name,
              // });
              this.snackbar = data.status;
              this.response = data.message;
              this.dialogNewRole = false;
              this.close();
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.savePermisions(data.record.id);
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
    updatePermission(role_id) {
      //alert(this.editPermissionId);
      console.log(this.editPermissionId);
      let payload = {
        role_id: role_id,
        permission_ids: this.permission_ids,
      };
      this.$axios
        .put("assign-permission/" + this.editPermissionId, payload)
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
            return;
          }
          this.response = "Permissions has been assigned";
          this.snackbar = true;
          //setTimeout(() => this.$router.push("/assign_permission"), 2000);
        });
    },

    getPermissions(url = "dropDownList") {
      this.$axios
        .get(url)
        .then(({ data }) => {
          this.permissions = data.data;
        })
        .catch((err) => console.log(err));
    },
    capsTitle(val) {
      if (val == "gst") {
        val = val.toUpperCase();
        return val;
      }
      let res = val;
      let r = res.replace(/[^a-z]/g, " ");
      let title = r.replace(/\b\w/g, (c) => c.toUpperCase());
      return title;
    },

    savePermisions(role_id) {
      this.errors = [];
      let payload = {
        role_id: role_id, //this.role_id,
        permission_ids: this.permission_ids,
        company_id: this.$auth.user.company.id,
      };

      this.$axios.post("assign-permission", payload).then(({ data }) => {
        if (!data.status) {
          this.errors = data.errors;
          return;
        }
        this.msg = "Permissions has been assigned";
        this.snackbar = true;
        //setTimeout(() => this.$router.push("/assign_permission"), 1000);
      });
    },

    toggleExpand(index) {
      // If the index is already expanded, collapse it; otherwise, expand it
      this.expandedIndex = this.expandedIndex === index ? null : index;
    },
    filteredPermissions(module) {
      return this.permissions[module.toLocaleLowerCase()];
    },
    filteredMenus(topMenuName) {
      return this.menus.filter((menu) => menu.topMenu === topMenuName);
    },
    selectAllByfilteredMenus(action, topMenuName, isChecked) {
      const allPermissions = this.menus
        .filter((menu) => menu.topMenu === topMenuName)
        .flatMap((menu) =>
          this.filteredPermissions(menu.module)
            .filter(
              (permission) => permission.name === menu.module + "_" + action
            )
            .map((permission) => permission.id)
        );

      if (isChecked) {
        // If checked, add all permissions to permission_ids
        this.permission_ids = [
          ...new Set([...this.permission_ids, ...allPermissions]),
        ];
      } else {
        // If unchecked, remove all permissions for this action
        this.permission_ids = this.permission_ids.filter(
          (id) => !allPermissions.includes(id)
        );
      }
    },
  },
};
</script>
