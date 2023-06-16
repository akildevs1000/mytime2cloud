<template>
  <div v-if="can(`employee_access`)">
    <!-- <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      crossorigin="anonymous"
    />
    <link
      href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"
      rel="stylesheet"
    /> -->
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class="mt-5 mb-5">
      <v-col cols="8">
        <h3>{{ Model }}</h3>
        <div>Dashboard / {{ Model }}</div>
      </v-col>
      <v-col cols="4" class="text-right">

      </v-col>

      <v-col cols="12">
        <!-- <v-toolbar class="rounded-md" color="background" dense flat dark>
          <span> {{ Model }} List</span>
        </v-toolbar> -->

        <v-card class="mb-5 rounded-md mt-3" elevation="0">
          <v-toolbar class="rounded-md" color="background" dense flat dark>
            <span> {{ Model }} </span>
            <a style="padding-left:10px" title="Reload Page/Reset Form" @click="getDataFromApi()"><v-icon class="mx-1">mdi
                mdi-reload</v-icon></a>
            <v-spacer></v-spacer>
            <v-toolbar-items>
              <v-col class="toolbaritems-button-design">
                <v-btn @click="goToCreatePage()" small dark class="primary pt-4 pb-4">Create New +
                </v-btn>
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
            itemsPerPageOptions: [50, 100, 500, 1000],



          }" class="elevation-1">
            <template v-slot:item.sno="{ item, index }">

              <b>{{ ++index }}</b>
            </template>
            <template v-slot:item.timezone.timezone_name="{ item }">
              <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                :return-value.sync="item.employee_id" @save="getDataFromApi()" @open="datatable_open">
                {{ item.timezone.timezone_name }}
                <template v-slot:input>
                  <v-text-field @input="datatable_searchByTimezonename" v-model="datatable_search_textbox"
                    label="Type Timezone Name"></v-text-field>
                </template>
              </v-edit-dialog>

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
                {{ caps(subitem.display_name + " : " + subitem.employee_id) }}

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
                  <v-list-item @click="deleteItem(item.id, item.deleteItem)">
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
      color: "primary",
      pagination: {
        current: 1,
        total: 0,
        per_page: 10,
      },
      headers: [

        { text: "#", align: "left", sortable: false, value: "sno", align: "start", key: 'sno', value: "sno" },
        { text: "Timezone Name", align: "left", sortable: true, align: "start", key: 'timezoneName', value: "timezone.timezone_name" },

        {
          text: "Devices",
          align: "left",
          sortable: false,
          value: "devices",
        },
        {
          text: "Employees",
          align: "left",
          sortable: false,
          value: "employees",
        },


        { text: "Actions", value: "actions", sortable: false },
      ],

    };
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

    datatable_save() {
      // this.snack = true
      // this.snackColor = 'success'
      // this.snackText = 'Searching...'
    },
    datatable_cancel() {
      // this.loading = false;
      // this.snack = true
      // this.snackColor = 'error'
      // this.snackText = 'Search Canceled'
      this.datatable_search_textbox = '';
    },
    datatable_open() {
      // this.snack = true
      // this.snackColor = 'info'
      // this.snackText = 'Search Details'
      this.datatable_search_textbox = '';
    },
    datatable_close() {
      // console.log('Dialog closed')
      this.loading = false;
      //this.datatable_search_textbox = '';
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
        company_id: this.$auth.user.company.id,
      };

      confirm("Are you sure you want to delete this item?") &&
        this.$axios.post(`${url}`, options).then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
          }
        });

      this.getDataFromApi();
    },

    datatable_searchByTimezonename(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length >= 1) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`, 'searchByTimezoneName');
      }
    },
    getDataFromApi(url = this.endpoint, additional_params) {
      let page = this.pagination.current;
      let options = {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company.id,
          cols: ["id", "employee_id", "display_name"],
        },
      };
      if (additional_params == 'searchByTimezoneName') {
        options.params.searchByTimezoneName = 'searchByTimezoneName';
      }




      this.loading = true;
      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {



        if (additional_params != '' && data.data.length == 0) {

          this.snack = true;
          this.snackColor = 'error';
          this.snackText = 'No Results Found';
          this.loading = false;
          return false;
        }


        this.data = data.data;
        this.total = this.data.length;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
      });
    },
    // firstCall() {
    //   // Code that will run only after the
    //   // entire view has been rendered

    //   var options = {
    //     params: {
    //       per_page: 1000, //this.pagination.per_page,
    //       company_id: this.$auth.user.company.id,

    //       cols: ["id", "employee_id", "display_name"],
    //     },
    //   };
    //   let page = 1;

    //   nuxtThisobject = this;
    //   let url = this.$axios.defaults.baseURL + "/gettimezonesinfo";

    //   $(document).ready(() => {
    //     setTimeout(() => {
    //       datatableobject = $("#empDevice1").DataTable({
    //         responsive: true,
    //         colReorder: true,
    //         dom: "Bfrtip",
    //         buttons: ["excel", "pdf"],
    //         buttons: [
    //           {
    //             extend: "pdf",
    //             className: "btn btn-danger",
    //             text: "<i class='mdi mdi-file-pdf-box'></i>",
    //           },
    //           {
    //             extend: "excel",
    //             className: "btn btn-success",
    //             text: "<i class='mdi mdi-file-excel-box'></i>",
    //           },
    //         ],

    //         order: [
    //           [0, "asc"], // colonna index1
    //           [1, "asc"], // colonna index2
    //         ],
    //         stateSave: true,
    //         ajax: {
    //           url,
    //           data: options.params,
    //           dataSrc: "data",
    //           datatype: "json",
    //         },
    //         columns: [
    //           {
    //             data: null,
    //             render: function (data, type, row, meta) {
    //               return meta.row + 1;
    //             },
    //           },

    //           //{ data: "timezone_id" },
    //           {
    //             data: null,
    //             render: function (data, type, row) {
    //               return row.timezone.timezone_name;
    //             },
    //           },
    //           {
    //             data: null,
    //             render: function (data, type, row) {
    //               let htmlConent1 = '<span class="tooltiptext"   > ';
    //               let htmlConent = "";
    //               row.device_id.forEach((data1) => {
    //                 htmlConent =
    //                   htmlConent +
    //                   data1.location +
    //                   ": " +
    //                   data1.name +
    //                   " <br/>";
    //               });
    //               htmlConent1 = htmlConent + "</span>";
    //               return htmlConent;
    //             },
    //           },
    //           {
    //             data: null,
    //             render: function (data, type, row) {
    //               let htmlConent1 = '<span class="tooltiptext"   > ';
    //               let htmlConent = "";
    //               row.employee_id.forEach((data1) => {
    //                 htmlConent =
    //                   htmlConent +
    //                   data1.display_name +
    //                   ": " +
    //                   data1.employee_id +
    //                   " <br/>";
    //               });
    //               htmlConent1 = htmlConent + "</span>";
    //               return htmlConent;
    //             },
    //           },
    //           {
    //             data: null,
    //             render: (data, type, row) => {
    //               var link = "'/timezonemapping/view'";
    //               let viewHTML =
    //                 '<div class="text-right"><a class="btn btn-success text-white btnView"  data-id="' +
    //                 row.id +
    //                 '"  href="javascript:void(0)"   >   <i class="mdi mdi-view-headline"></i> View</a> &nbsp;' +
    //                 '<a class="btn btn-warning text-white btnEdit" data-id="' +
    //                 row.id +
    //                 '" href="javascript:void(0)"    > <i class="mdi mdi-table-edit"></i> Edit</a>  &nbsp;' +
    //                 '<a class="btn btn-danger text-white btnDelete" data-id="' +
    //                 row.id +
    //                 '" data-timezoneid="' +
    //                 row.timezone_id +
    //                 '" href="javascript:void(0)"   > <i class="mdi mdi-delete-forever"></i>  Delete</a></div>';
    //               return viewHTML;
    //             },
    //           },
    //         ],
    //       });
    //       setTimeout(() => {
    //         $(".btnView").on("click", function (e) {
    //           var rowId = $(this).data("id");
    //           nuxtThisobject.$router.push("/timezonemapping/" + rowId);
    //         });

    //         $(".btnEdit").on("click", function (e) {
    //           var rowId = $(this).data("id");
    //           //nuxtThisobject.$router.push("/timezonemapping/edit"+ rowId);
    //           nuxtThisobject.$router.push({
    //             path: "/timezonemapping/edit",
    //             query: { id: rowId },
    //           });
    //         });

    //         $(".btnDelete").on("click", function (e) {
    //           let rowId = $(this).data("id");
    //           let timezone_id = $(this).data("timezoneid");

    //           console.log("id", rowId);
    //           //nuxtThisobject.$router.push("/timezonemapping/delete");
    //           console.log("nuxtThisobject.$router", nuxtThisobject.$router);
    //           let url =
    //             nuxtThisobject.$axios.defaults.baseURL + "/deletetimezone";
    //           let options = {
    //             timezone_id: timezone_id,
    //             id: rowId,
    //           };

    //           // .delete(`${url}/${rowId}`, options)

    //           confirm("Are you sure you want to delete this item?") &&
    //             nuxtThisobject.$axios
    //               .post(`${url}`, options)
    //               .then(({ data }) => {
    //                 if (!data.status) {
    //                   this.errors = data.errors;
    //                 } else {
    //                   this.errors = [];
    //                   this.snackbar = true;
    //                   this.response = data.message;
    //                   datatableobject.destroy();
    //                   nuxtThisobject.firstCall();
    //                 }
    //               });
    //         });
    //       }, 2000);

    //       $("a.toggle-vis").on("click", function (e) {
    //         e.preventDefault();

    //         // Get the column API object
    //         var column = datatableobject.column($(this).attr("data-column"));

    //         // Toggle the visibility
    //         column.visible(!column.visible());
    //       });
    //     }, 1000 * 2);
    //   });
    // },
    goToCreatePage() {
      this.$router.push("/timezonemapping/new");
    },
    handleAction(actionName, data) {
      console.log(actionName, data);
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
