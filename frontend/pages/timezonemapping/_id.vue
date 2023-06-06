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
    <!-- <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div> -->
    <!-- <div>
      Toggle column: <a class="toggle-vis" data-column="0">Name</a> -
      <a class="toggle-vis" data-column="1">First name</a> -
      <a class="toggle-vis" data-column="2">Last name</a> -
      <a class="toggle-vis" data-column="3">Position</a> -
      <a class="toggle-vis" data-column="4">Age</a> -
      <a class="toggle-vis" data-column="5">Start date</a>
    </div> -->
    <v-row class="mt-5 mb-5">
      <v-col cols="8">
        <h3>{{ Model }} : Timezone : {{ Timezone }}</h3>
        <div>Dashboard / {{ Model }}</div>
      </v-col>
      <v-col cols="4" class="text-right">
        <v-btn @click="goToBackpage()" small dark class="primary pt-4 pb-4"
          >Back
        </v-btn>
      </v-col>
      <v-col cols="6">
        <v-toolbar class="rounded-md" color="background" dense flat dark>
          <span> Devices List</span>
        </v-toolbar>
        <table id="devicesTables" class="display nowrap" style="width: 100%">
          <thead>
            <tr>
              <th># sno</th>
              <th>Device Name</th>
              <th>Device Location</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </v-col>

      <v-col cols="6">
        <v-toolbar class="rounded-md" color="background" dense flat dark>
          <span> Employees List</span>
        </v-toolbar>
        <table id="employeesTable" class="display nowrap" style="width: 100%">
          <thead>
            <tr>
              <th># sno</th>
              <th>Employee Name</th>
              <th>Emp Id</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </v-col>
    </v-row>
  </div>
</template>
<script>
import DataTable from "@andresouzaabreu/vue-data-table";

export default {
  components: {
    DataTable,
  },
  data() {
    return {
      name: "fahath",
      endpointUpdatetimezonelist: "employee_timezone_mapping",
      Model: "Timezone Mapping List  ",
      response: "",
      tableData: [],
      tableColumns: [],
      Timezone: "",
    };
  },
  computed: {},

  created() {
    //this.getData();
  },
  mounted: function () {
    this.Timezone = this.$route.params.id;
    this.$nextTick(function () {
      this.deviceTableContent();
      this.employeeTableContent();
    });
  },
  methods: {
    goToBackpage() {
      this.$router.push("/timezonemapping/list");
    },
    deviceTableContent() {
      // Code that will run only after the
      // entire view has been rendered
      var options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          id: this.$route.params.id,
          cols: ["id", "employee_id", "display_name"],
        },
      };
      let page = 1;
      // ${url}?page=${page}

      let url =
        this.$axios.defaults.baseURL +
        "/employee_timezone_mapping/" +
        this.$route.params.id;

      $(document).ready(() => {
        // setTimeout(() => {

        var table = $("#devicesTables").DataTable({
          responsive: true,
          colReorder: true,
          paging: false,
          info: false,
          searching: false,
          // dom: "Bfrtip",
          buttons: [],
          order: [
            [0, "asc"], // colonna index1
            [1, "asc"], // colonna index2
          ],
          stateSave: true,
          ajax: {
            url,
            data: options.params,
            dataSrc: "device_id",
            datatype: "json",
          },
          columns: [
            {
              data: null,
              render: function (data, type, row, meta) {
                return meta.row + 1;
              },
            },

            //{ data: "timezone_id" },

            {
              data: null,
              render: function (data, type, row) {
                return row.location;
              },
            },
            {
              data: null,
              render: function (data, type, row) {
                return row.device_id;
              },
            },
          ],
        });

        $("a.toggle-vis").on("click", function (e) {
          e.preventDefault();

          // Get the column API object
          var column = table.column($(this).attr("data-column"));

          // Toggle the visibility
          column.visible(!column.visible());
        });
        // }, 1000 * 1);
      });
    },
    employeeTableContent() {
      console.log("id", this.$route.params.id);
      // Code that will run only after the
      // entire view has been rendered
      var options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,
          id: this.$route.params.id,
          cols: ["id", "employee_id", "display_name"],
        },
      };
      let page = 1;
      // ${url}?page=${page}

      let url =
        this.$axios.defaults.baseURL +
        "/employee_timezone_mapping/" +
        this.$route.params.id;

      $(document).ready(() => {
        // setTimeout(() => {

        var table = $("#employeesTable").DataTable({
          responsive: true,
          colReorder: true,
          paging: false,
          searching: false,
          info: false,
          // dom: "Bfrtip",
          // buttons: [],

          order: [
            [0, "asc"], // colonna index1
            [1, "asc"], // colonna index2
          ],
          stateSave: true,
          ajax: {
            url,
            data: options.params,
            dataSrc: "employee_id",
            datatype: "json",
          },
          columns: [
            {
              data: null,
              render: function (data, type, row, meta) {
                return meta.row + 1;
              },
            },

            //{ data: "timezone_id" },
            {
              data: null,
              render: function (data, type, row) {
                return row.display_name;
              },
            },
            {
              data: null,
              render: function (data, type, row) {
                return row.employee_id;
              },
            },
          ],
        });

        $("a.toggle-vis").on("click", function (e) {
          e.preventDefault();

          // Get the column API object
          var column = table.column($(this).attr("data-column"));

          // Toggle the visibility
          column.visible(!column.visible());
        });
        // }, 1000 * 1);
      });
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
  },
};
</script>
<style scoped>
div.dataTables_wrapper div.dataTables_filter {
  float: left !important;
}
.dt-buttons {
  float: right !important;
}
</style>
