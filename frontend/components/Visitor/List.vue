<template>
  <div>
    <v-dialog v-model="viewDialog" width="800">
      <v-card>
        <v-card-title dense class="popup_background">
          Visitor Information
          <v-spacer></v-spacer>
          <v-icon @click="viewDialog = false" outlined dark>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-container>
            <span class="bold"> Visitor </span>

            <span style="float: right">
              <span :style="'color:' + getRelatedColor(item)">{{
                item.status
              }}</span></span
            >
            <v-row class="100%" style="margin: auto; line-height: 36px">
              <v-col cols="4" style="padding: 0px">
                <v-img
                  style="
                    border-radius: 2%;
                    width: 200px;
                    max-width: 95%;
                    min-height: 100px;
                    height: auto;
                    border: 1px solid #ddd;
                  "
                  :src="item.logo ? item.logo : '/no-profile-image.jpg'"
                >
                </v-img>
              </v-col>
              <v-col cols="4" style="padding-left: 5px; padding-top: 0px">
                <span cols="8">
                  <b>{{ item.full_name || "---" }} </b></span
                >

                <div>
                  <v-icon size="20" class="icon-blue" title="Date"
                    >mdi-calendar-range</v-icon
                  >
                  {{ item.from_date_display }}
                  <span v-if="item.to_date_display != item.from_date_display">
                    to {{ item.to_date_display }}</span
                  >
                </div>
                <div>
                  <v-icon size="20" class="icon-blue" title="Time"
                    >mdi-clock-outline</v-icon
                  >
                  {{ item.time_in }} - {{ item.time_out }}
                </div>
                <div>
                  <v-icon title="Purpose" size="20" class="icon-blue"
                    >mdi-briefcase-account-outline</v-icon
                  >
                  {{ item.purpose.name || "---" }}
                </div>
                <div>
                  <v-icon size="20" class="icon-blue" title="Contact Number"
                    >mdi-cellphone</v-icon
                  >
                  {{ item.phone_number || "---" }}
                </div>
                <divider></divider>
                <div class="bold">
                  <v-icon size="20" color="green" title="Entry In Time"
                    >mdi-bank-transfer-in</v-icon
                  >
                  {{ item.checked_in_datetime || "---" }}
                </div>

                <div v-if="item.over_stay" style="color: red">
                  Expected Out Time: {{ item.time_out }}
                </div>
              </v-col>
              <v-col cols="4" style="padding-left: 5px; padding-top: 0px">
                <div>&nbsp;</div>
                <div>
                  <v-icon title="Company" size="30" class="icon-blue"
                    >mdi-domain</v-icon
                  >
                  {{ item.visitor_company_name || "---" }}
                </div>
                <span cols="8">
                  <v-icon
                    size="20"
                    class="icon-blue"
                    v-if="item.gender == 'Male'"
                    >mdi-human-male</v-icon
                  >
                  <v-icon
                    size="20"
                    class="icon-blue"
                    v-if="item.gender == 'Female'"
                    >mdi-human-female</v-icon
                  >
                  {{ item.gender || "---" }}
                </span>

                <div>
                  <v-icon size="20" class="icon-blue" title="ID"
                    >mdi-identifier</v-icon
                  >

                  <span v-if="item.id_type == 1">Emirates ID</span>
                  <span v-else-if="item.id_type == 2">National ID</span>

                  : {{ item.id_number || "---" }}
                </div>

                <div>
                  <v-icon size="20" class="icon-blue" title="Email"
                    >mdi-email</v-icon
                  >
                  {{ item.email || "---" }}
                </div>
                <divider></divider>
                <div class="bold">
                  <v-icon size="30" color="red" title="Exit Out Time"
                    >mdi-bank-transfer-out</v-icon
                  >
                  {{ item.checked_out_datetime || "---" }}

                  <div v-if="item.over_stay" style="color: red">
                    Over stay: {{ item.over_stay }}
                  </div>
                </div>
              </v-col>
            </v-row>
            <v-divider class="mt-3"></v-divider>
            <h4 style="background: #ddd" class="mb-3">Host :</h4>

            <v-row>
              <v-col col="4">
                <v-img
                  style="
                    border-radius: 2%;
                    width: 200px;
                    max-width: 95%;
                    min-height: 100px;
                    height: auto;
                    border: 1px solid #ddd;
                  "
                  :src="
                    item.host
                      ? item.host.employee.profile_picture
                      : '/no-profile-image.jpg'
                  "
                >
                </v-img>
              </v-col>

              <v-col cols="8">
                <v-row>
                  <v-col col="3">Employee Name </v-col>
                  <v-col col="9"
                    >: {{ item.host?.employee.first_name }}
                    {{ item.host?.employee.last_name }}</v-col
                  >
                </v-row>
                <v-row>
                  <v-col col="3">Contact Number</v-col>
                  <v-col col="9"
                    >: {{ item.host?.employee.phone_number }}</v-col
                  >
                </v-row>
                <v-row>
                  <v-col col="3"> Email Id </v-col>
                  <v-col col="9">
                    {{ item.host?.employee.user?.email || "---" }}
                  </v-col>
                </v-row>
                <v-row>
                  <v-col col="3"> Branch </v-col>
                  <v-col col="9">
                    : {{ item.host?.employee.branch?.branch_name || "---" }}
                  </v-col>
                </v-row>
                <v-row>
                  <v-col col="3">Status </v-col>
                  <v-col col="9">
                    <span :style="'color:' + getRelatedColor(item)">{{
                      item.status
                    }}</span></v-col
                  >
                </v-row>
                <v-row>
                  <v-col col="3">Reason:</v-col>
                  <v-col col="9"> {{ item.reason || "---" }}</v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-card elevation="1" class="mt-2" style="min-height: 500px">
      <v-toolbar class="mb-2 popup_background" dense flat>
        <v-toolbar-title>
          <span style="color: black" class="page-title-display">
            Visitor Requests</span
          ></v-toolbar-title
        >
        <v-btn
          dense
          class="ma-0 px-0"
          x-small
          :ripple="false"
          text
          title="Filter"
        >
          <v-icon @click="toggleFilter" class="mx-1 ml-2"
            >mdi mdi-filter</v-icon
          >
        </v-btn>
        <!-- <v-tooltip top color="primary">
              <template v-slot:activator="{ on, attrs }"> -->
        <!-- <v-btn
              title="Reload"
              dense
              class="ma-0 px-0"
              x-small
              :ripple="false"
              @click="getData"
              text
            >
              <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
            </v-btn> -->
        <v-spacer></v-spacer>
        <!-- <CustomFilter
          style="margin-bottom: 5px"
          @filter-attr="filterAttr"
          :defaultFilterType="1"
          :height="'40px'"
          :default_date_from="from_date"
          :default_date_to="to_date"
        /> -->
      </v-toolbar>
      <v-data-table
        dense
        :headers="headers_table"
        :items="data"
        model-value="data.id"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [10, 50, 100, 500, 1000],
        }"
        class="elevation-0 alternate-rows"
        :server-items-length="totalRowsCount"
      >
        <template v-slot:header="{ props: { headers } }">
          <tr v-if="isFilter">
            <td v-for="header in headers" :key="header.text">
              <v-container>
                <v-text-field
                  clearable
                  :hide-details="true"
                  v-if="header.filterable && !header.filterSpecial"
                  v-model="filters[header.value]"
                  id="header.value"
                  @input="applyFilters(header.value, $event)"
                  outlined
                  dense
                  autocomplete="off"
                ></v-text-field>
                <v-select
                  clearable
                  :hide-details="true"
                  @change="applyFilters('status', $event)"
                  item-value="id"
                  item-text="name"
                  v-model="filters[header.value]"
                  outlined
                  dense
                  v-else-if="
                    header.filterable &&
                    header.filterSpecial &&
                    header.value == 'status_id'
                  "
                  :items="[{ id: '', name: 'All' }, ...visitor_status_list]"
                ></v-select>
                <v-select
                  clearable
                  :hide-details="true"
                  @change="applyFilters('status', $event)"
                  item-value="id"
                  item-text="name"
                  v-model="filters[header.value]"
                  outlined
                  dense
                  v-else-if="
                    header.filterable &&
                    header.filterSpecial &&
                    header.value == 'purpose_id'
                  "
                  :items="[{ id: '', name: 'All Purposes' }, ...purposeList]"
                ></v-select>
                <v-autocomplete
                  clearable
                  :hide-details="true"
                  @change="applyFilters('status', $event)"
                  item-value="host_id"
                  item-text="full_name"
                  v-model="filters[header.value]"
                  outlined
                  dense
                  v-else-if="
                    header.filterable &&
                    header.filterSpecial &&
                    header.value == 'host_company_id'
                  "
                  :items="[
                    { host_id: '', full_name: 'All Hosts' },
                    ...hostList,
                  ]"
                ></v-autocomplete>

                <div
                  v-else-if="
                    header.filterable &&
                    header.filterSpecial &&
                    header.value == 'visit_from'
                  "
                  style="margin-top: -17px"
                >
                  <Calender
                    @filter-attr="filterAttr"
                    :defaultFilterType="1"
                    :height="'40px'"
                    :default_date_from="from_date"
                    :default_date_to="to_date"
                  />
                </div>
              </v-container>
            </td>
          </tr>
        </template>
        <template v-slot:item.sno="{ item, index }">
          {{
            currentPage
              ? (currentPage - 1) * perPage +
                (cumulativeIndex + data.indexOf(item))
              : "-"
          }}
        </template>

        <template v-slot:item.pic="{ item }">
          <v-img
            style="
              border-radius: 2%;
              width: 100px;
              max-width: 95%;
              min-height: 100px;
              height: auto;
              border: 1px solid #ddd;
            "
            :src="item.logo ? item.logo : '/no-profile-image.jpg'"
          >
          </v-img>
        </template>
        <template v-slot:item.first_name="{ item }">
          {{ item.full_name }}
        </template>

        <template v-slot:item.purpose_id="{ item }">
          {{ item.purpose.name }}
        </template>
        <template v-slot:item.visit_from="{ item }">
          {{ item.from_date_display }}
          <span v-if="item.to_date_display != item.from_date_display">
            to {{ item.to_date_display }}</span
          >
        </template>
        <template v-slot:item.time_in="{ item }">
          {{ item.time_in }} - {{ item.time_out }}
        </template>

        <template v-slot:item.phone_number="{ item }">
          {{ item.phone_number }}
          <br />
          <span class="secondary-value"> {{ item.email }}</span>
        </template>
        <template v-slot:item.visitor_company_name="{ item }"
          >{{ item.visitor_company_name }}
        </template>
        <template v-slot:item.id="{ item }">
          <span v-if="item.id_type == 1">Emirates ID</span>
          <span v-else-if="item.id_type == 2">National ID</span> <br />

          <span class="secondary-value"> {{ item.id_number }}</span>
        </template>
        <template v-slot:item.host_company_id="{ item }">
          {{ item.host?.employee.first_name || "---" }}
          {{ item.host?.employee.last_name }}
        </template>
        <template v-slot:item.status_id="{ item }">
          <span :style="'color:' + getRelatedColor(item)"
            >{{ item.status }}
            <div v-if="item.over_stay" style="color: red">
              Over stay: {{ item.over_stay }}
            </div>
          </span>
        </template>
        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="150" dense>
              <v-list-item @click="viewInfo(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="green" small> mdi-eye </v-icon>
                  View
                </v-list-item-title>
              </v-list-item>
              <v-list-item @click="uploadUserToDeviceDialog = true">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="purple" small> mdi-cellphone-text </v-icon>
                  Upload Visitor
                </v-list-item-title>
              </v-list-item>
              <!-- <v-list-item @click="updateStatus(item.id, 3)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="red" small> mdi-cancel</v-icon>
                  Reject
                </v-list-item-title>
              </v-list-item> -->
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-card>

    <div class="text-center">
      <v-dialog v-model="uploadUserToDeviceDialog" max-width="500px">
        <v-card>
          <v-card-title class="headline">Upload Visitor</v-card-title>
          <v-card-text class="mt-2">
            <v-form ref="form" v-model="valid">
              <v-text-field
                v-model="payload.visitor_id"
                label="Visitor ID"
                required
                outlined
                dense
              ></v-text-field>

              <v-select
                v-model="payload.zone_id"
                :items="zoneList"
                label="Zone 1"
                item-text="name"
                item-value="id"
                outlined
                dense
                required
              ></v-select>

              <v-row>
                <v-col>
                  <v-menu
                    ref="fromTimePicker"
                    v-model="fromTimePicker"
                    :close-on-content-click="false"
                    transition="scale-transition"
                    offset-y
                    min-width="290px"
                  >
                    <template v-slot:activator="{ on }">
                      <v-text-field
                        v-model="payload.fromTime"
                        label="From Time"
                        outlined
                        dense
                        readonly
                        v-on="on"
                      ></v-text-field>
                    </template>
                    <v-time-picker
                      v-if="fromTimePicker"
                      v-model="payload.fromTime"
                    ></v-time-picker>
                  </v-menu>
                </v-col>
                <v-col>
                  <v-menu
                    ref="toTimePicker"
                    v-model="toTimePicker"
                    :close-on-content-click="false"
                    transition="scale-transition"
                    offset-y
                    min-width="290px"
                  >
                    <template v-slot:activator="{ on }">
                      <v-text-field
                        v-model="payload.toTime"
                        label="To Time"
                        outlined
                        dense
                        readonly
                        v-on="on"
                      ></v-text-field>
                    </template>
                    <v-time-picker
                      v-if="toTimePicker"
                      v-model="payload.toTime"
                    ></v-time-picker>
                  </v-menu>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn dark color="grey"  @click="cancel">Cancel</v-btn>
            <v-btn dark color="purple"  @click="save" :disabled="!valid"
              >Save</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog v-model="dialog" width="500">
        <v-card>
          <v-toolbar flat dense>
            <v-spacer></v-spacer>
            <v-icon @click="dialog = false">mdi-close</v-icon>
          </v-toolbar>

          <v-card-text>
            <p class="text-center">
              <v-img
                :src="response_image"
                alt="Avatar"
                height="50px"
                width="50px"
                style="display: inline-block"
              ></v-img>
              <!-- <v-icon v-if="status_id == 1" color="green">mdi-check</v-icon>
              <v-icon v-else-if="status_id == 2" color="red">mdi-cancel</v-icon> -->
            </p>
            <p class="text-center">
              {{ message }}
            </p>
          </v-card-text>
        </v-card>
      </v-dialog>
    </div>
  </div>
</template>

<script>
export default {
  props: ["filterValue"],
  data: () => ({
    visitor_status_list: [],
    uploadUserToDeviceDialog: false,
    valid: false,
    fromTimePicker: false,
    toTimePicker: false,

    payload: {
      visitor_id: null,
      zone_id: 1,
      fromTime: null,
      toTime: null,
    },
    zoneList: [
      { id: 1, name: "Zone 1" },
      { id: 2, name: "Zone 2" },
      // Add more zones as needed
    ],
    hostList: [],
    item: { purpose: {} },
    viewDialog: false,
    formAction: "Create",
    previewImage: null,

    upload: {
      name: "",
    },
    isFilter: false,
    filters: [],
    loading: false,
    cumulativeIndex: 1,
    perPage: 10,
    currentPage: 1,
    totalRowsCount: 0,
    options: { perPage: 10 },
    status_id: 0,
    response_image: "/sucess.png",
    dialog: false,
    message: "",
    branchesList: [],
    changeRequestDialog: false,
    Model: "Visitor Request",
    endpoint: "visitor",
    data: [],
    from_date: "",
    to_date: "",
    headers_table: [
      {
        text: "#",
        align: "left",
        sortable: false,
        value: "sno",
        filterable: false,
      },
      {
        text: "Picture",
        align: "left",
        sortable: true,
        value: "pic",
        filterable: false,
      },
      {
        text: "Name",
        align: "left",
        sortable: true,
        value: "first_name",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Purpose",
        align: "left",
        sortable: true,
        value: "purpose_id",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Date",
        align: "left",
        sortable: true,
        value: "visit_from",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Time",
        align: "left",
        sortable: true,
        value: "time_in",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Contact Number",
        align: "left",
        sortable: true,
        value: "phone_number",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "From Company",
        align: "left",
        sortable: true,
        value: "visitor_company_name",
        filterable: true,
        filterSpecial: false,
      },

      {
        text: "Host",
        align: "left",
        sortable: true,
        value: "host_company_id",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Status",
        align: "left",
        sortable: true,
        value: "status_id",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Options",
        align: "left",
        sortable: false,
        value: "options",
        filterable: false,
      },
    ],
    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    purposeList: [],
  }),
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },

    filterValue: {
      handler(val) {
        this.getDataFromApi(val);
      },
      deep: true,
    },
  },
  created() {
    const today = new Date();

    this.from_date = today.toISOString().slice(0, 10);
    this.to_date = today.toISOString().slice(0, 10);
    this.getDataFromApi();
    setTimeout(() => {
      this.getPurposeList();
      this.getHostsList();

      this.getVisitorStatusList();
    }, 1000);
  },
  methods: {
    cancel() {
      this.uploadUserToDeviceDialog = false;
    },
    save() {
      // if (this.$refs.form.validate()) {
      //   this.uploadUserToDeviceDialog = false;
      // }
    },
    viewInfo(item) {
      this.viewDialog = true;
      this.item = item;
    },
    filterAttr(data) {
      if (data != null) {
        this.from_date = data.from;
        this.to_date = data.to;
      } else {
        this.from_date = null;
        this.to_date = null;
      }

      this.applyFilters();
    },
    applyFilters() {
      this.getDataFromApi();
    },
    toggleFilter() {
      this.isFilter = !this.isFilter;
    },
    getPurposeList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`purpose_list`, options).then(({ data }) => {
        this.purposeList = data;
      });
    },
    getHostsList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`host_list`, options).then(({ data }) => {
        this.hostList = [];

        // Loop through the data and extract employee_id and full_name
        for (let i = 0; i < data.length; i++) {
          let item = data[i];
          let employee = item.employee;
          if (employee) {
            // Add the extracted information to the array
            this.hostList.push({
              host_id: item.id,
              full_name: employee.full_name,
            });
          }
        }
      });
    },
    getVisitorStatusList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`visitor_status_list`, options).then(({ data }) => {
        this.visitor_status_list = data;
      });
    },
    // filterAttr(data) {
    //   this.from_date = data.from;
    //   this.to_date = data.to;
    //   this.getDataFromApi();
    // },
    updateStatus(id, status_id) {
      this.status_id = status_id;
      this.$axios
        .post(`visitor-status-update/${id}`, {
          status_id: status_id,
        })
        .then(({ data }) => {
          if (!data.status) {
            this.message = data.message;
            this.response_image = "/fail.png";
            setTimeout(() => (this.dialog = false), 3000);
            return;
          }
          this.message = "Your clocking has been recorded successfully";
          if (status_id == 1) {
            this.response_image = "/success.png";
          } else {
            this.response_image = "/fail.png";
          }
          this.dialog = true;
          this.message = data.message;
          this.getDataFromApi();
        });
    },
    getRelatedColor(item) {
      let colors = {
        1: "purple",
        3: "red",
        2: "green",
        UNKNOWN: "purple",
      };
      return colors[item.status_id || "UNKNOWN"];
    },
    getDataFromApi(filterValue = null) {
      console.log("filterValue", filterValue);
      this.loading = true;

      // if (this.filterValue == "Total Visitor") {
      //   this.filters["status_id"] = null;
      // } else if (this.filterValue == "Approved") {
      //   this.filters["status_id"] = 2;
      // } else if (this.filterValue == "Rejected") {
      //   this.filters["status_id"] = 3;
      // } else if (this.filterValue == "Over Stayed") {
      //   this.filters["status_id"] = null;
      // }
      if (this.filterValue != "") {
        this.filters["status_id"] = null;
      }

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;
      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          pagination: true,
          company_id: this.$auth.user.company_id,

          from_date: this.from_date,
          to_date: this.to_date,
          ...this.filters,
          statsFilterValue: filterValue,

          status_id: 2,
        },
      };
      this.$axios.get(this.endpoint, options).then(({ data }) => {
        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
        this.totalRowsCount = data.total;
      });
    },
  },
};
</script>
