<template>
  <div v-if="can(`leave_application_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="dialogLeaveGroup" width="500px">
      <v-card>
        <v-toolbar flat small dense dark class="background">
          <span class="headline">Employee - {{ viewEmployeeName }} </span>
        </v-toolbar>
        <v-card-text style="padding:5px">

          <v-data-table v-model="ids" item-key="id" :headers="headersGroupInfo" :items="DialogLeaveGroupData"
            :loading="loading" :hide-default-footer="true" class="elevation-1">

            <template v-slot:item.leave_type="{ item }" center>
              {{ item.leave_type.name }} ({{ item.leave_type.short_name }})
            </template>
            <template v-slot:item.total="{ item }">
              <v-chip color="black" text-color="white"> {{ item.leave_type_count }}</v-chip>
            </template>
            <template v-slot:item.approved="{ item }">
              <v-chip color="primary"> {{ item.employee_used }}</v-chip>
            </template>
            <template v-slot:item.available="{ item }">
              <v-chip class="ma-2" color="green" text-color="white"> {{
                item.leave_type_count - item.employee_used }}</v-chip>
            </template>

          </v-data-table>








        </v-card-text>


      </v-card>
    </v-dialog>
    <v-dialog v-model="dialog" width="500px">
      <v-card>

        <v-toolbar flat small dense dark class="background">
          <span class="headline"> Leave Application </span>
        </v-toolbar>
        <v-card-text>
          <v-container>

            <v-row>
              <v-col cols="12">
                <label for="" style="padding-bottom:5px">Leave Type</label>
                <v-autocomplete :items="leaveTypes" item-text="name" item-value="id" placeholder="Select Leave Type"
                  v-model="editedItem.leave_type_id" :hide-details="!errors.leave_type_id" :error="errors.leave_type_id"
                  :error-messages="errors && errors.leave_type_id
                    ? errors.leave_type_id[0]
                    : ''
                    " dense outlined></v-autocomplete>
              </v-col>
              <v-col cols="12">
                <v-menu ref="from_menu" v-model="start_menu" :close-on-content-click="false"
                  :return-value.sync="editedItem.start_date" transition="scale-transition" offset-y min-width="auto">
                  <template v-slot:activator="{ on, attrs }">
                    <div class="mb-1">From Date</div>
                    <v-text-field style="height:45px" outlined dense v-model="editedItem.start_date" readonly
                      v-bind="attrs" v-on="on" :error-messages="errors && errors.start_date ? errors.start_date[0] : ''
                        ">
                    </v-text-field>
                  </template>
                  <v-date-picker v-model="editedItem.start_date" small no-title scrollable :min="todayDate"
                    @change="update_EdititemStart">

                  </v-date-picker>
                </v-menu>
              </v-col>

              <v-col cols="12">
                <v-menu ref="end_menu" v-model="end_menu" :close-on-content-click="false"
                  :return-value.sync="editedItem.end_date" transition="scale-transition" offset-y min-width="auto">
                  <template v-slot:activator="{ on, attrs }">
                    <div class="mb-1">End Date</div>
                    <v-text-field style="height:45px" outlined dense v-model="editedItem.end_date" readonly v-bind="attrs"
                      v-on="on" :error-messages="errors && errors.end_date ? errors.end_date[0] : ''
                        "></v-text-field>
                  </template>
                  <v-date-picker small v-model="editedItem.end_date" @change="update_EdititemEnd"
                    :min="editedItem.start_date" no-title scrollable>
                  </v-date-picker>
                </v-menu>
              </v-col>
              <v-col cols="12">
                <label for="" style="padding-bottom:5px">Reason</label>
                <v-text-field dense outlined v-model="editedItem.reason" placeholder="Reason/Notes" :error-messages="errors && errors.reason ? errors.reason[0] : ''
                  "></v-text-field>
              </v-col>


            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn class="error" small @click="close"> Cancel </v-btn>
          <v-btn class="primary" small @click="save">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogView" width="1000px">
      <v-card>
        <v-toolbar flat small dense dark class="background">
          <span class="headline">Leave Information </span>
        </v-toolbar>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="6">

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
                    <label for="">: {{ dialogViewObject.leave_group_name }}</label>
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
              <v-col col="6">


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
                    <label for="">: {{ dialogViewObject.reporting_manager }}</label>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4">
                    <strong>Status </strong>
                  </v-col>
                  <v-col cols="8">
                    <label for="">: <v-chip v-if="dialogViewObject.status == 1" small class="p-2 mx-1" color="primary">
                        Approved
                      </v-chip>
                      <v-chip v-if="dialogViewObject.status == 2" small class="p-2 mx-1" color="error">
                        Rejected
                      </v-chip>
                      <v-chip v-if="dialogViewObject.status == 0" small class="p-2 mx-1" color="secondary">
                        Pending
                      </v-chip></label>
                  </v-col>
                </v-row>
                <v-row v-if="dialogViewObject.status == 1">
                  <v-col cols="4">
                    <strong>Approved Date </strong>
                  </v-col>
                  <v-col cols="8">
                    <label for="">: {{ dialogViewObject.approved_datetime }}</label>
                  </v-col>
                </v-row>
                <v-row v-else-if="dialogViewObject.status == 2">
                  <v-col cols="4">
                    <strong>Rejected Date </strong>
                  </v-col>
                  <v-col cols="8">
                    <label for="">: {{ dialogViewObject.approved_datetime }}</label>
                  </v-col>
                </v-row>

              </v-col>
            </v-row>
            <v-row>

              <v-col cols="2">
                <strong><u>Leave Note</u> </strong>
              </v-col>
              <v-col cols="8">
                <label for="">: {{ dialogViewObject.reason }}</label>
              </v-col>

            </v-row>
            <v-card-actions class="mt-4">
              <v-btn class="error" small @click="close"> Close </v-btn>
              <v-spacer></v-spacer>
              <v-btn class="warning" v-if="dialogViewObject.status == 0" small @click="rejectLeave(dialogViewObject.id)">
                Reject </v-btn>
              <v-spacer></v-spacer>
              <v-btn class="primary" v-if="dialogViewObject.status == 0" small
                @click="approveLeave(dialogViewObject.id)">Approve</v-btn>
            </v-card-actions>
          </v-container>
        </v-card-text>


      </v-card>
    </v-dialog>
    <v-row>
      <v-col md="12">

        <v-card class="mb-5 rounded-md" elevation="0">
          <v-toolbar class="rounded-md" color="background" dense flat dark>
            <v-toolbar-title><span> Dashboard / Applications List</span></v-toolbar-title>
            <a style="padding-left:10px" title="Reload Page/Reset Form" @click="clearFilters()"><v-icon class="mx-1">mdi
                mdi-reload</v-icon></a>

            <a style="padding-left:10px" @click="toggleFilter"><v-icon class="mx-1">mdi
                mdi-filter</v-icon></a>
            <v-spacer></v-spacer>
            <!-- <v-toolbar-items>
              <v-col class="toolbaritems-button-design1">
                <v-btn v-if="can(`leave_application_create`)" small color="primary" @click="dialog = true" class="mb-2">{{
                  Model }}
                  +</v-btn>
              </v-col>
            </v-toolbar-items> -->
          </v-toolbar>

          <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
            {{ snackText }}

            <template v-slot:action="{ attrs }">
              <v-btn v-bind="attrs" text @click="snack = false">
                Close
              </v-btn>
            </template>
          </v-snackbar>
          <v-data-table v-if="can(`leave_application_view`)" v-model="ids" item-key="id" :headers="headers" :items="data"
            :loading="loading" :footer-props="{
              itemsPerPageOptions: [10, 50, 100, 500, 1000],
            }" class="elevation-1">
            <template v-slot:header="{ props: { headers } }">
              <tr v-if="isFilter">
                <td v-for="header in      headers     " :key="header.text" class="table-search-header">
                  <v-text-field style="padding-left: 10px;" v-if="header.filterable && header.text != 'Status'"
                    v-model="filters[header.value]" id="header.value" @input="applyFilters(header.value, $event)" outlined
                    height="10px" clearable></v-text-field>

                  <v-select class="filter-select-hidden-text" v-else-if="header.filterable && header.text == 'Status'"
                    height="10px;width:5px" style="padding: 0px;" small density="compact"
                    @change="applyFilters('status', $event)" clearable item-value="value" item-text="title" :items="[{ value: '', title: 'All' }, { value: 'approved', title: 'Approved' }, {
                      value: 'rejected',
                      title: 'Rejected'
                    }, { value: 'pending', title: 'Pending' }]"></v-select>

                  <template v-else>
                    <!-- {{ header.text }} -->
                  </template>
                </td>
              </tr>
            </template>
            <template v-slot:item.name="{ item }">
              <v-row no-gutters>
                <v-col style="
                        padding: 5px;
                        padding-left: 0px;
                        width: 50px;
                        max-width: 50px;
                      ">
                  <v-img style="
                          border-radius: 50%;
                          height: auto;
                          width: 50px;
                          max-width: 50px;
                        " :src="item.employee.profile_picture
                          ? item.employee.profile_picture
                          : '/no-profile-image.jpg'
                          ">
                  </v-img>
                </v-col>
                <v-col style="padding: 10px">
                  <strong>
                    {{ item.employee.first_name ? item.employee.first_name : "---" }}
                    {{ item.employee.last_name ? item.employee.last_name : "---" }}</strong>
                  <div>
                    {{
                      item.employee.designation ? (item.employee.designation.name) : "---"
                    }}
                  </div>
                </v-col>
              </v-row>
            </template>
            <template v-slot:item.group_name="{ item }">
              <v-chip @click="gotoGroupDetails(item.employee.leave_group_id, item.employee.id, item.employee.full_name)">
                {{
                  item.employee.leave_group &&
                  item.employee.leave_group.group_name }} </v-chip>
            </template>


            <template v-slot:item.leave_type_name="{ item }">
              {{ (item.leave_type.name) }}
            </template>
            <template v-slot:item.start_date="{ item }">
              {{ (item.start_date) }}
            </template>
            <template v-slot:item.end_date="{ item }">
              {{ (item.end_date) }}
            </template>
            <template v-slot:item.reason="{ item }">
              {{ (item.reason.substr(0, 30) + '...') }}
            </template>
            <template v-slot:item.reporting="{ item }">
              {{ (item.reporting.first_name) }} {{ (item.reporting.last_name) }}
            </template>
            <template v-slot:item.created_at="{ item }">
              {{ getCurrentDateTime(item.created_at) }}
            </template>
            <template v-slot:item.status="{ item }">
              <v-chip v-if="item.status == 1" small class="p-2 mx-1" color="primary">
                Approved
              </v-chip>
              <v-chip v-if="item.status == 2" small class="p-2 mx-1" color="error">
                Rejected
              </v-chip>
              <v-chip v-if="item.status == 0" small class="p-2 mx-1" color="secondary">
                Pending
              </v-chip>
            </template>

            <template v-slot:item.action="{ item }">
              <v-menu bottom left>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn dark-2 icon v-bind="attrs" v-on="on">
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list width="120" dense>

                  <!-- <v-list-item @click="editItem(item)" v-if="item.status == 0">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon v-if="can(`leave_application_edit`)" color="secondary" small @click="editItem(item)">
                        mdi-pencil
                      </v-icon> Edit
                    </v-list-item-title>
                  </v-list-item> -->


                  <!-- <v-list-item @click="deleteItem(item)" v-if="item.status == 0">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon v-if="can(`leave_application_delete`)" color="error" small @click="deleteItem(item)">
                        {{ item.announcement === "customer" ? "" : "mdi-delete" }}
                      </v-icon> Delete
                    </v-list-item-title>
                  </v-list-item> -->
                  <v-list-item
                    @click="gotoGroupDetails(item.employee.leave_group_id, item.employee.id, item.employee.full_name)">
                    <v-list-item-title style=" cursor: pointer">
                      <v-icon v-if="can(`leave_application_view`)" color="primary" small>
                        mdi-calendar
                      </v-icon> Statistics
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="view(item)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon v-if="can(`leave_application_view`)" color="primary" small @click="view(item)">
                        mdi-information
                      </v-icon>View Application
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
      </v-col>
    </v-row>
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
    formTitle: 'New Leave Application',
    dialogEmployees: false,
    idsEmployeeList: [],
    //editor
    datatable_search_textbox: '',
    filter_employeeid: '',
    snack: false,
    snackColor: '',
    snackText: '',
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
      { text: "Leave Type", align: "left", key: "name", value: "leave_type" },
      { text: "Total", align: "center", key: "name", value: "total" },
      { text: "Approved", align: "center", key: "name", value: "approved" },
      { text: "Available", align: "center", key: "name", value: "available" },

    ],
    headers: [
      { text: "Employee Name", align: "left", sortable: true, filterable: true, key: "name", value: "name" },
      {
        text: "Group Type",
        align: "left", filterable: true,
        sortable: true,
        value: "group_name",
      },
      {
        text: "Leave Type",
        align: "left", filterable: true,
        sortable: true,
        value: "leave_type_name",
      },
      {
        text: "Star Date",
        align: "left", filterable: true,
        sortable: true,
        value: "start_date",
      },
      {
        text: "End Date",
        align: "left", filterable: true,
        sortable: true,
        value: "end_date",
      },
      {
        text: "Leave Note",
        align: "left", filterable: true,
        sortable: true,
        value: "reason",
      },
      {
        text: "Reporting Manager Name",
        align: "left",
        sortable: true, filterable: true,
        value: "reporting",
      },
      {
        text: "Applied On ",
        align: "left",
        sortable: true, filterable: true,
        value: "created_at",
      },
      {
        text: "Status",
        align: "left", filterable: true,
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

    },
    defaultItem: {
      leave_type_id: "",
      reason: "",

      start_date: null,
      end_date: null,
    },
    response: "",
    data: [],
    errors: [],
    options_dialog: {},
    employees_dialog: [],
    selectAllDepartment: false,
    selectAllEmployee: false,
    DialogEmployeesData: {},
    todayDate: "",
    login_user_employee_id: "",
  }),

  computed: {

  },

  watch: {

  },
  created() {
    this.loading = true;


    this.getDataFromApi();
    this.getLeaveTypes();
    let now = new Date();

    let year = now.getFullYear();
    let day = ("0" + now.getDate()).slice(-2);
    let month = ("0" + (now.getMonth() + 1)).slice(-2);


    let formattedDateTime = year + "-" + month + "-" + day;

    this.todayDate = formattedDateTime;

    setInterval(() => {
      this.getDataFromApi();
    }, 1000 * 30);
  },

  methods: {
    applyFilters(filter_column = '', filter_value = '') {

      this.getDataFromApi('', filter_column, filter_value);
    },
    toggleFilter() {
      this.isFilter = !this.isFilter;
    },
    view(item) {
      this.dialogViewObject.id = item.id;
      this.dialogViewObject.employee_name = item.employee.first_name + " " + item.employee.last_name;
      this.dialogViewObject.leave_type = item.leave_type.name;
      this.dialogViewObject.from_date = item.start_date;
      this.dialogViewObject.to_date = item.end_date;
      this.dialogViewObject.approved_manager = item.reporting.first_name + " " + item.reporting.last_name;
      this.dialogViewObject.status = item.status;
      this.dialogViewObject.reason = item.reason;
      this.dialogViewObject.applied_date = this.getCurrentDateTime(item.created_at);
      this.dialogViewObject.leave_group_name = item.employee.leave_group ? item.employee.leave_group.group_name : '--';
      this.dialogViewObject.approved_datetime = item.updated_at ? this.getCurrentDateTime(item.updated_at) : '--';
      this.dialogViewObject.reporting_manager = item.reporting ? item.reporting.first_name + ' ' + item.reporting.last_name : '--';



      this.dialogView = true;

    },
    getCurrentDateTime(date) {
      let now = new Date(date);

      let year = now.getFullYear();
      let day = ("0" + now.getDate()).slice(-2);
      let month = ("0" + (now.getMonth() + 1)).slice(-2);
      let hours = ("0" + now.getHours()).slice(-2);
      let minutes = ("0" + now.getMinutes()).slice(-2);
      let seconds = ("0" + now.getSeconds()).slice(-2);

      let formattedDateTime = year + "-" + month + "-" + day;//+ " " + hours + ":" + minutes;

      return formattedDateTime;
    },
    update_EdititemStart() {

      this.$refs.from_menu.save(this.editedItem.start_date)
      this.from_menu = false;

    },
    update_EdititemEnd() {

      this.$refs.end_menu.save(this.editedItem.end_date)
      this.end_menu = false;



    },
    gotoGroupDetails(leaveGroupId, employee_id, employee_name) {

      this.viewEmployeeName = employee_name;
      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company.id,
          employee_id: employee_id
        },
      };
      this.$axios.get('leave_groups/' + leaveGroupId, options).then(({ data }) => {
        this.dialogLeaveGroup = true;
        this.DialogLeaveGroupData = data[0].leave_count;

      });

    },
    gotoDialogPage(item) {
      // console.log('item', item);
      this.DialogEmployeesData = item.employees;
      this.dialogEmployees = true;
    },
    datatable_save() {
    },
    datatable_cancel() {
      this.datatable_search_textbox = '';
    },
    datatable_open() {
      this.datatable_search_textbox = '';
    },
    datatable_close() {
      this.loading = false;
    },
    toggleDepartmentSelection() {
      this.selectAllDepartment = !this.selectAllDepartment;
    },
    toggleEmployeeSelection() {
      this.selectAllEmployee = !this.selectAllEmployee;
    },
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    onScroll() {
      this.scrollInvoked++;
    },
    getLeaveTypes() {


      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios.get(`leave_type`, options).then(({ data }) => {
        this.leaveTypes = data.data;
      });
    },

    clearFilters() {
      this.isFilter = false;
      this.getDataFromApi();
    },

    getDataFromApi(url = this.endpoint, filter_column = '', filter_value = '') {

      if (url == '') {
        url = this.endpoint;
        //
      }
      this.loading = true;

      let endDate = new Date();



      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
          year: endDate.getFullYear(),
        },
      };
      if (filter_column != '') {

        options.params[filter_column] = filter_value;

      }

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {

        if (filter_column != '' && data.data.length == 0) {
          this.snack = true;
          this.snackColor = 'error';
          this.snackText = 'No Results Found';
          this.loading = false;
          //return false;
        }
        this.data = data.data;
        this.total = data.total;
        this.loading = false;


        if (this.$auth)
          if (this.$auth.user)
            this.login_user_employee_id = this.$auth.user.employee.id;
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

    getEmployees(url = "employee") {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.employees_dialog = data.data;
      });
    },
    rejectLeave(leaveid) {
      let options = {
        params: {

          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios.get(this.endpoint + "/reject/" + leaveid, options).then(({ data }) => {

        if (!data.status) {
          this.errors = data.errors;
        } else {
          this.snackbar = data.status;
          this.response = data.message;
          this.getDataFromApi();
          this.dialogView = false;
        }



      });
    },
    approveLeave(leaveid) {
      let options = {
        params: {

          company_id: this.$auth.user.company.id,
        },
      };
      this.$axios.get(this.endpoint + "/approve/" + leaveid, options).then(({ data }) => {
        if (!data.status) {
          this.errors = data.errors;
        } else {
          this.snackbar = data.status;
          this.response = data.message;
          this.getDataFromApi();
          this.dialogView = false;
        }

      });
    },
    save() {


      console.log(this.$auth);
      this.editedItem.company_id = this.$auth.user.company.id;
      this.editedItem.employee_id = this.login_user_employee_id;
      this.editedItem.reporting_manager_id = this.$auth.user.reporting_manager_id;
      ;

      let options = {
        params: {
          company_id: this.$auth.user.company.id,
          employee_id: this.login_user_employee_id,
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


