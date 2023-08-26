<template>
  <div v-if="can(`employee_access`)">

    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class=" ">


      <v-col cols="12">

        <v-card class="mb-5 rounded-md " elevation="0">
          <v-toolbar class="rounded-md" color="background" dense flat dark>

            <v-toolbar-title><span> {{ Model }} </span></v-toolbar-title>

            <v-tooltip top color="primary">
              <template v-slot:activator="{ on, attrs }">
                <v-btn dense class="ma-0 px-0" x-small :ripple="false" text v-bind="attrs" v-on="on">
                  <v-icon color="white" class="ml-2" @click="getDataFromApi()" dark>mdi mdi-reload</v-icon>
                </v-btn>
              </template>
              <span>Reload</span>
            </v-tooltip>

            <v-tooltip top color="primary">
              <template v-slot:activator="{ on, attrs }">
                <v-btn x-small :ripple="false" text v-bind="attrs" v-on="on" @click="attendancFilters = true">
                  <v-icon dark white @click="toggleFilter">mdi-filter</v-icon>
                </v-btn>
              </template>
              <span>Filter</span>
            </v-tooltip>

            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-col class="toolbaritems-button-design">

                <v-tooltip top color="primary">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dense class="ma-0 px-0" x-small :ripple="false" text v-bind="attrs" v-on="on">
                      <v-icon color="white" class="ml-2" @click="goToCreatePage()" dark>mdi mdi-plus-circle</v-icon>
                    </v-btn>
                  </template>
                  <span>Add New Timezone</span>
                </v-tooltip>


              </v-col>


            </v-toolbar-items>
          </v-toolbar>
          <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
            {{ snackText }}

            <template v-slot:action="{ attrs }">
              <v-btn v-bind="attrs" text @click="snack = false">
                Close
              </v-btn>
            </template>
          </v-snackbar>


          <v-data-table dense :headers="headers" :items="data" :loading="loading" :options.sync="options" :footer-props="{
            itemsPerPageOptions: [100, 500, 1000],



          }" class="elevation-1" :server-items-length="totalRowsCount">

            <template v-slot:header="{ props: { headers } }">
              <tr v-if="isFilter">
                <td v-for="header in headers" :key="header.text">
                  <v-text-field clearable :hide-details="true" v-if="header.filterable" v-model="filters[header.key]"
                    :id="header.key" @input="applyFilters(header.key, $event)" outlined dense autocomplete="off"
                    :placeholder="header.placeHolder"></v-text-field>


                </td>
              </tr>


            </template>
            <template v-slot:item.sno="{ item, index }">

              <b>{{ ++index }}</b>
            </template>
            <template v-slot:item.timezone.timezone_name="{ item }">
              {{ item.timezone.timezone_name }}
            </template>
            <template v-slot:item.devices="{ item }">
              <v-chip small class="primary ma-1" v-for="(subitem, index) in item.device_id.slice(0, 3)" :key="index">
                {{ caps(subitem.location + " : " + subitem.name) }}

              </v-chip>
              <v-btn small warning @click="displayView(item.id)" v-if="item.device_id.length > 3">
                All Devices
              </v-btn>
            </template>
            <template v-slot:item.employees="{ item }">

              <v-chip small class="primary ma-1" v-for="(subitem, index) in item.employee_id.slice(0, 3)" :key="index">
                {{ caps(subitem.first_name + " " + subitem.last_name + " : " + subitem.employee_id) }}

              </v-chip>
              <v-btn small warning @click="displayView(item.id)" v-if="item.employee_id.length > 3">
                All Employees
              </v-btn>
            </template>
            <template v-slot:item.actions="{ item }">

              <v-menu bottom left>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn dark-2 icon v-bind="attrs" v-on="on">
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list width="120" dense>
                  <v-list-item @click="displayView(item.id)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="primary" small> mdi-view-list </v-icon>
                      View
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item @click="displayEdit(item.id)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-pencil </v-icon>
                      Edit
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="deleteItem(item.id, item.timezone_id)">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="error" small> mdi-delete </v-icon>
                      Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>

          </v-data-table>

        </v-card>

      </v-col>
    </v-row>
  </div>
</template>
<script>
//var nuxtThisobject.$router;
//var nuxtThisobject.$axios;
var nuxtThisobject;
// import DataTable from "@andresouzaabreu/vue-data-table";
var datatableobject;
export default {
  components: {
    //  DataTable,
  },
  data(vm) {
    return {
      showFilters: false,
      filters: {},
      isFilter: false,
      totalRowsCount: 10,
      filter_employeeid: '',
      snack: false,
      snackColor: '',
      snackText: '',
      datatable_search_textbox: '',
      total: 0,
      options: {},
      data: [],
      name: "",
      endpointUpdatetimezonelist: "employee_timezone_mapping",
      endpoint: "gettimezonesinfo",
      Model: "Timezone Mapping List ",
      response: "",
      tableData: [],
      tableColumns: [],
      loading: false,
      snackbar: false,
      color: "black",
      pagination: {
        current: 1,
        total: 0,
        per_page: 10,
      },
      headers: [

        { text: "#", align: "left", sortable: false, value: "sno", align: "start", key: 'sno', value: "sno", },
        { text: "Timezone Name", align: "left", sortable: true, align: "start", key: 'timezoneName', filterable: true, value: "timezone.timezone_name" },

        {
          text: "Devices",
          align: "left",
          sortable: false,
          value: "devices",
          filterable: false,
          key: 'device',
          placeHolder: 'Type Device Name'
        },
        {
          text: "Employees",
          align: "left",
          sortable: false,
          value: "employees",
          filterable: false,
          key: 'employees',
          placeHolder: 'Type First Name'
        },


        { text: "Actions", value: "actions", sortable: false },
      ],

    };
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  // computed: {
  //   data: {
  //     get() {
  //       return this.data
  //     },
  //     set(val) {
  //       this.$emit('update:usersProp', val)
  //     }
  //   }
  // },
  created() {
    //this.getData();
    this.loading = true;
  },
  mounted: function () {
    this.getDataFromApi();
    // this.$nextTick(function () {
    //   // this.snackbar = true;
    //   // this.response = "Data loading...Please wait ";
    //   this.firstCall();

    //   setTimeout(() => {
    //     this.loading = false;
    //   }, 1000 * 2);
    // });
  },
  methods: {
    applyFilters() {
      this.getDataFromApi();
    },
    toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;
    },
    clearFilters() {
      this.filters = {};

      this.isFilter = false;
      this.getDataFromApi();
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
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    getDeviceslist(devicesList) {
      let deviceNames = "";
      devicesList.forEach((data1) => {
        deviceNames =
          deviceNames + data1.location + ": " + data1.name + " <br />";
      });

      return deviceNames;
    },
    getEmployeelist(employeesList) {
      let employeeNames = "";
      employeesList.forEach((data1) => {
        employeeNames =
          employeeNames +
          data1.display_name +
          ": " +
          data1.employee_id +
          "<br />";
      });

      return employeeNames;
    },
    displayView(rowId) {
      this.$router.push("/timezonemapping/" + rowId);
    },
    displayEdit(rowId) {
      this.$router.push("/timezonemapping/edit?id=" + rowId);
    },
    deleteItem(rowId, timezone_id) {
      let url = this.$axios.defaults.baseURL + "/deletetimezone";
      let options = {
        timezone_id: timezone_id,
        id: rowId,
        company_id: this.$auth.user.company_id,
      };

      confirm("Are you sure you want to delete this item?") &&
        this.$axios.post(`${url}`, options).then(({ data }) => {

          this.getDataFromApi();
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
          }
        });


    },

    datatable_searchByTimezonename(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`, 'searchByTimezoneName');
      }
    },
    getDataFromApi(url = this.endpoint, additional_params) {

      this.data = [];
      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          cols: ["id", "employee_id", "display_name"],
          ...this.filters,
        },
      };
      if (additional_params != '')
        options.params['additional_params'] = additional_params;
      this.loading = true;
      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        // if (additional_params != '' && data.data.length == 0) {
        //   this.snack = true;
        //   this.snackColor = 'error';
        //   this.snackText = 'No Results Found';
        //   this.loading = false;
        //   return false;
        // }
        this.data = data.data;
        this.itemsPerPage = this.data.length;
        // this.pagination.current = data.current_page;
        // this.pagination.total = data.last_page;
        this.loading = false;
      });
    },

    goToCreatePage() {
      this.$router.push("/timezonemapping/new");
    },
    handleAction(actionName, data) {

      //window.alert("check out the console to see the logs");
    },
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e.name == per || per == "/")) ||
        u.is_master
      );
    },
    // goToViewPage() {
    //   this.$router.push("/timezonemapping/view");
    // },
    // goToEditPage() {
    //   this.$router.push("/timezonemapping/edit");
    // },

    // goToDeletePage() {
    //   this.$router.push("/timezonemapping/delete");
    // },
  },
};
</script>
