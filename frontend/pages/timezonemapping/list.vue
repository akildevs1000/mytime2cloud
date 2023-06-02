<template>
  <div v-if="can(`employee_access`)">
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      crossorigin="anonymous"
    />
    <link
      href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"
      rel="stylesheet"
    />
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
        <v-btn @click="goToCreatePage()" small dark class="primary pt-4 pb-4"
          >Create New +
        </v-btn>
      </v-col>
      <v-col cols="12">
        <!-- <v-toolbar class="rounded-md" color="background" dense flat dark>
          <span> {{ Model }} List</span>
        </v-toolbar> -->
        <table id="empDevice1" class="display nowrap" style="width: 100%">
          <thead>
            <tr>
              <th># sno</th>
              <th>Time zone Name</th>
              <th>Devices</th>
              <th>Employees</th>
              <th class="text-right">Options</th>
            </tr>
            <v-progress-linear
              v-if="loading"
              :active="loading"
              :indeterminate="loading"
              absolute
              color="primary"
            ></v-progress-linear>
          </thead>
          <tbody></tbody>
        </table>
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
      name: "fahath",
      endpointUpdatetimezonelist: "employee_timezone_mapping",
      Model: "Timezone Mapping List ",
      response: "",
      tableData: [],
      tableColumns: [],
      loading: false,
      snackbar: false,
      color: "primary",
    };
  },
  computed: {},

  created() {
    //this.getData();
    this.loading = true;
  },
  mounted: function () {
    this.$nextTick(function () {
      // this.snackbar = true;
      // this.response = "Data loading...Please wait ";
      this.firstCall();

      setTimeout(() => {
        this.loading = false;
      }, 1000 * 2);
    });
  },
  methods: {
    firstCall() {
      // Code that will run only after the
      // entire view has been rendered

      var options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company.id,

          cols: ["id", "employee_id", "display_name"],
        },
      };
      let page = 1;

      nuxtThisobject = this;
      let url = this.$axios.defaults.baseURL + "/gettimezonesinfo";

      $(document).ready(() => {
        setTimeout(() => {
          datatableobject = $("#empDevice1").DataTable({
            responsive: true,
            colReorder: true,
            dom: "Bfrtip",
            buttons: ["excel", "pdf"],
            buttons: [
              {
                extend: "pdf",
                className: "btn btn-danger",
                text: "<i class='mdi mdi-file-pdf-box'></i>",
              },
              {
                extend: "excel",
                className: "btn btn-success",
                text: "<i class='mdi mdi-file-excel-box'></i>",
              },
            ],

            order: [
              [0, "asc"], // colonna index1
              [1, "asc"], // colonna index2
            ],
            stateSave: true,
            ajax: {
              url,
              data: options.params,
              dataSrc: "data",
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
                  return row.timezone.timezone_name;
                },
              },
              {
                data: null,
                render: function (data, type, row) {
                  let htmlConent1 = '<span class="tooltiptext"   > ';
                  let htmlConent = "";
                  row.device_id.forEach((data1) => {
                    htmlConent =
                      htmlConent +
                      data1.location +
                      ": " +
                      data1.name +
                      " <br/>";
                  });
                  htmlConent1 = htmlConent + "</span>";
                  return htmlConent;
                },
              },
              {
                data: null,
                render: function (data, type, row) {
                  let htmlConent1 = '<span class="tooltiptext"   > ';
                  let htmlConent = "";
                  row.employee_id.forEach((data1) => {
                    htmlConent =
                      htmlConent +
                      data1.display_name +
                      ": " +
                      data1.employee_id +
                      " <br/>";
                  });
                  htmlConent1 = htmlConent + "</span>";
                  return htmlConent;
                },
              },
              {
                data: null,
                render: (data, type, row) => {
                  var link = "'/timezonemapping/view'";
                  let viewHTML =
                    '<div class="text-right"><a class="btn btn-success text-white btnView"  data-id="' +
                    row.id +
                    '"  href="javascript:void(0)"   >   <i class="mdi mdi-view-headline"></i> View</a> &nbsp;' +
                    '<a class="btn btn-warning text-white btnEdit" data-id="' +
                    row.id +
                    '" href="javascript:void(0)"    > <i class="mdi mdi-table-edit"></i> Edit</a>  &nbsp;' +
                    '<a class="btn btn-danger text-white btnDelete" data-id="' +
                    row.id +
                    '" data-timezoneid="' +
                    row.timezone_id +
                    '" href="javascript:void(0)"   > <i class="mdi mdi-delete-forever"></i>  Delete</a></div>';
                  return viewHTML;
                },
              },
            ],
          });
          setTimeout(() => {
            $(".btnView").on("click", function (e) {
              var rowId = $(this).data("id");
              nuxtThisobject.$router.push("/timezonemapping/" + rowId);
            });

            $(".btnEdit").on("click", function (e) {
              var rowId = $(this).data("id");
              //nuxtThisobject.$router.push("/timezonemapping/edit"+ rowId);
              nuxtThisobject.$router.push({
                path: "/timezonemapping/edit",
                query: { id: rowId },
              });
            });

            $(".btnDelete").on("click", function (e) {
              let rowId = $(this).data("id");
              let timezone_id = $(this).data("timezoneid");

              console.log("id", rowId);
              //nuxtThisobject.$router.push("/timezonemapping/delete");
              console.log("nuxtThisobject.$router", nuxtThisobject.$router);
              let url =
                nuxtThisobject.$axios.defaults.baseURL + "/deletetimezone";
              let options = {
                timezone_id: timezone_id,
                id: rowId,
              };

              // .delete(`${url}/${rowId}`, options)

              confirm("Are you sure you want to delete this item?") &&
                nuxtThisobject.$axios
                  .post(`${url}`, options)
                  .then(({ data }) => {
                    if (!data.status) {
                      this.errors = data.errors;
                    } else {
                      this.errors = [];
                      this.snackbar = true;
                      this.response = data.message;
                      datatableobject.destroy();
                      nuxtThisobject.firstCall();
                    }
                  });
            });
          }, 2000);

          $("a.toggle-vis").on("click", function (e) {
            e.preventDefault();

            // Get the column API object
            var column = datatableobject.column($(this).attr("data-column"));

            // Toggle the visibility
            column.visible(!column.visible());
          });
        }, 1000 * 2);
      });
    },
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
<style scoped>
div.dataTables_wrapper div.dataTables_filter {
  float: left !important;
}
.dt-buttons {
  float: right !important;
  text-align: right !important;
}
</style>
