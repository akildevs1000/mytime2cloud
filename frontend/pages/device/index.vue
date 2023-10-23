<template>
  <div>
    <v-dialog
      v-model="dialogAccessSettings"
      width="90%"
      style="background-color: #fff !important"
    >
      <v-card>
        <v-card-title dense class="popup_background">
          <span>Time Selection</span>
          <v-spacer></v-spacer>
          <v-icon @click="dialogAccessSettings = false" outlined>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <v-container>
            <DeviceAccessSettings
              :key="popup_device_id"
              :device_id="popup_device_id"
              @closepopup="closepopup"
            />
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    <div class="text-center ma-5">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-row>
      <!-- <v-col xs="12" sm="12" md="3" cols="12">
        <v-select class="form-control" @change="getDataFromApi(`device`)" v-model="pagination.per_page"
          :items="[10, 25, 50, 100]" placeholder="Per Page Records" solo hide-details flat></v-select>
      </v-col> -->
      <!-- <v-col xs="12" sm="12" md="3" cols="12">
        <v-text-field class="form-control py-0 custom-text-box floating shadow-none" placeholder="Search..." solo flat
          @input="searchIt" v-model="search" hide-details></v-text-field>
      </v-col> -->
    </v-row>
    <!-- <Back color="primary" /> -->
    <v-navigation-drawer v-model="editDialog" bottom temporary right fixed>
      <v-toolbar class="popup_background" dense>
        {{ this.editedIndex == -1 ? "New " : "Edit " }} Device
        <v-spacer></v-spacer>

        <v-icon @click="editDialog = false" outlined dark>
          mdi mdi-close-circle
        </v-icon>
      </v-toolbar>

      <v-row class="ma-1">
        <v-col md="12">
          <v-text-field
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.name"
            v-model="payload.name"
            placeholder="Device Name"
            outlined
            dense
            label="Device Name *"
          ></v-text-field>
          <span v-if="errors && errors.name" class="error--text pa-0 ma-0"
            >{{ errors.name[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-text-field
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.short_name"
            v-model="payload.short_name"
            placeholder="Short Name"
            outlined
            dense
            label="Short Name *"
          ></v-text-field>
          <span v-if="errors && errors.short_name" class="error--text pa-0 ma-0"
            >{{ errors.short_name[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-autocomplete
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.branch_id"
            v-model="payload.branch_id"
            placeholder="Branch Name"
            outlined
            dense
            label="Branch Name *"
            :items="branches"
            item-value="id"
            item-text="branch_name"
          ></v-autocomplete>
          <span v-if="errors && errors.branch_id" class="error--text pa-0 ma-0"
            >{{ errors.branch_id[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-text-field
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.location"
            v-model="payload.location"
            placeholder="Device location"
            outlined
            dense
            label="Device location *"
          ></v-text-field>
          <span v-if="errors && errors.location" class="error--text"
            >{{ errors.location[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-autocomplete
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.utc_time_zone"
            v-model="payload.utc_time_zone"
            placeholder="Time Zone"
            outlined
            dense
            label="Time Zone(Ex:UTC+) *"
            :items="getTimezones()"
            item-value="key"
            item-text="text"
          ></v-autocomplete>
          <span v-if="errors && errors.utc_time_zone" class="error--text"
            >{{ errors.utc_time_zone[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-text-field
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.model_number"
            v-model="payload.model_number"
            placeholder="Model Number"
            outlined
            dense
            label="Model Number *"
          ></v-text-field>
          <span v-if="errors && errors.model_number" class="error--text"
            >{{ errors.model_number[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-text-field
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.device_id"
            v-model="payload.device_id"
            placeholder="Serial Number"
            outlined
            dense
            label="Serial Number *"
          ></v-text-field>
          <span v-if="errors && errors.device_id" class="error--text"
            >{{ errors.device_id[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-autocomplete
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.function"
            v-model="payload.function"
            placeholder="Function"
            outlined
            dense
            label="Function *"
            :items="[
              { id: 'auto', name: 'Auto' },
              { id: 'In', name: 'In' },
              { id: 'Out', name: 'Out' },
            ]"
            item-value="id"
            item-text="name"
          ></v-autocomplete>
          <span v-if="errors && errors.function" class="error--text"
            >{{ errors.function[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-autocomplete
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.device_type"
            v-model="payload.device_type"
            placeholder="Device Type"
            outlined
            dense
            label="Device Type *"
            :items="[
              { id: 'all', name: 'All(Attendance and Access)' },
              { id: 'Attendance', name: 'Attendance' },
              { id: 'Access Control', name: 'Access Control' },
            ]"
            item-value="id"
            item-text="name"
          ></v-autocomplete>
          <span v-if="errors && errors.device_type" class="error--text"
            >{{ errors.device_type[0] }}
          </span>
        </v-col>
        <v-col md="12">
          <v-autocomplete
            style="height: 50px"
            class="pb-0"
            :hide-details="!payload.status_id"
            v-model="payload.status_id"
            placeholder="Time Zone"
            outlined
            dense
            label="Device Status *"
            :items="device_statusses"
            item-value="id"
            item-text="name"
          ></v-autocomplete>
          <span v-if="errors && errors.status_id" class="error--text"
            >{{ errors.status_id[0] }}
          </span>
        </v-col>
      </v-row>

      <!-- <div class="row pa-3">
        <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label">Device Name </label>
            <span class="text-danger">*</span>
            <input v-model="payload.name" class="form-control" type="text" />
            <span v-if="errors && errors.name" class="text-danger mt-2">{{
              errors.name[0]
            }}</span>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label">Device Short Name </label>
            <span class="text-danger">*</span>
            <input
              v-model="payload.short_name"
              class="form-control"
              type="text"
            />
            <span v-if="errors && errors.short_name" class="text-danger mt-2">{{
              errors.short_name[0]
            }}</span>
          </div>
        </div>
        <div v-if="isCompany" class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label">Branch</label>
            <span class="text-danger">*</span>
            <select
              v-model="payload.branch_id"
              class="form-select form-control"
              aria-label="Branch"
            >
              <option value="">Select Branch</option>
              <option
                v-for="(branch, idx) in branches"
                :key="idx"
                :value="branch.id"
              >
                {{ branch.branch_name }}
              </option>
            </select>
            <span v-if="errors && errors.branch_id" class="text-danger mt-2">{{
              errors.branch_id[0]
            }}</span>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label">Device Location </label>
            <span class="text-danger">*</span>
            <input v-model="payload.location" class="form-control" />
            <span v-if="errors && errors.location" class="text-danger mt-2">{{
              errors.location[0]
            }}</span>
          </div>
        </div>

        <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label">Time Zone</label>
            <span class="text-danger">*</span>
            <select
              v-model="payload.utc_time_zone"
              class="form-select form-control"
              aria-label="Branch"
            >
              <option value="">Select Branch</option>
              <option
                v-for="(timezone, idx, index) in timeZones"
                :key="index"
                :value="idx"
              >
                {{ timezone.offset }}- {{ idx }}
              </option>
            </select>
            <span
              v-if="errors && errors.utc_time_zone"
              class="text-danger mt-2"
              >{{ errors.utc_time_zone[0] }}</span
            >
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label"> Model Number</label>
            <span class="text-danger">*</span>
            <input
              v-model="payload.model_number"
              class="form-control"
              type="text"
            />
            <span
              v-if="errors && errors.model_number"
              class="text-danger mt-2"
              >{{ errors.model_number[0] }}</span
            >
          </div>
        </div>

        <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label"> Device Serial Number</label>
            <span class="text-danger">*</span>
            <input
              v-model="payload.device_id"
              class="form-control"
              type="text"
            />
            <span v-if="errors && errors.device_id" class="text-danger mt-2">{{
              errors.device_id[0]
            }}</span>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label"> Device Function</label>
            <span class="text-danger">*</span>
            <select
              v-model="payload.function"
              class="form-select pt-1 pb-1"
              aria-label="Default select"
            >
              <option value="">Select Device Function</option>
              <option value="auto">Auto</option>
              <option value="In">In</option>
              <option value="Out">Out</option>
            </select>
            <span v-if="errors && errors.function" class="text-danger mt-2">{{
              errors.function[0]
            }}</span>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label">Device Type </label>
            <span class="text-danger">*</span>
            <select
              v-model="payload.device_type"
              class="form-select pt-1 pb-1"
              aria-label="Default select"
            >
              <option value="">Select Device Type</option>
              <option value="all">All(Attendance and Access)</option>
              <option value="Attendance">Attendance</option>
              <option value="Access Control">Access Control</option>
            </select>
            <span
              v-if="errors && errors.device_type"
              class="text-danger mt-2"
              >{{ errors.device_type[0] }}</span
            >
          </div>
        </div>

          <div class="col-sm-12">
          <div class="form-group">
            <label class="col-form-label">Device Status </label>
            <span class="text-danger">*</span>
            <select
              v-model="payload.status_id"
              class="form-select"
              aria-label="Default select"
            >
              <option value="">Select Device Status</option>
              <option
                v-for="(device_status, idx) in device_statusses"
                :key="idx"
                :value="device_status.id"
              >
                {{ device_status.name }}
              </option>
            </select>
            <span v-if="errors && errors.status_id" class="text-danger mt-2">{{
              errors.status_id[0]
            }}</span>
          </div>
        </div>  
      </div> -->
      <v-row>
        <v-col cols="12">
          <div class="text-right">
            <v-btn
              small
              :loading="loading"
              color="primary"
              @click="store_device"
            >
              Submit
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-navigation-drawer>

    <v-card class="mb-5 mt-2" elevation="0">
      <v-toolbar class="rounded-md" dense flat>
        <v-toolbar-title><span> Devices List</span></v-toolbar-title>

        <!-- <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }"> -->
        <v-btn
          dense
          class="ma-0 px-0"
          x-small
          :ripple="false"
          text
          title="Reload"
        >
          <v-icon class="ml-2" @click="getDataFromApi()" dark
            >mdi mdi-reload</v-icon
          >
        </v-btn>
        <div v-if="isCompany" style="width: 250px">
          <v-select
            @change="getDataFromApi()"
            class="pt-10 px-2"
            v-model="branch_id"
            :items="[{ id: ``, branch_name: `Select All` }, ...branchesList]"
            dense
            placeholder="Select Branch"
            outlined
            item-value="id"
            item-text="branch_name"
          >
          </v-select>
        </div>
        <!-- </template>
          <span>Reload</span>
        </v-tooltip> -->

        <!-- <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }"> -->
        <!-- <v-btn
          x-small
          :ripple="false"
          text
          title="Filter"
          @click="toggleFilter()"
        >
          <v-icon dark>mdi-filter</v-icon>
        </v-btn> -->
        <!-- </template>
          <span>Filter</span>
        </v-tooltip> -->

        <v-spacer></v-spacer>

        <!-- <v-tooltip top color="primary">
          <template v-slot:activator="{ on, attrs }"> -->
        <v-btn
          x-small
          :ripple="false"
          text
          title="Sync Devices"
          @click="updateDevicesHealth"
        >
          <v-icon dark white>mdi-cached</v-icon>
        </v-btn>
        <!-- </template>
          <span>Sync Devices</span>
        </v-tooltip> -->
        <v-btn
          x-small
          :ripple="false"
          text
          title="Add Device"
          @click="addItem()"
        >
          <v-icon dark white>mdi-plus-circle</v-icon>
        </v-btn>
      </v-toolbar>

      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <v-data-table
        dense
        :headers="headers"
        :items="data"
        model-value="data.id"
        :loading="loading"
        :footer-props="{
          itemsPerPageOptions: [50, 100, 500, 1000],
        }"
        class="elevation-1 pt-5"
        :options.sync="options"
        :server-items-length="totalRowsCount"
      >
        <!-- <template v-slot:header="{ props: { headers } }">
          <tr v-if="isFilter">
            <td
              style="width: 50px"
              v-for="header in headers"
              :key="header.text"
            >
              <v-container>
                <v-text-field
                  clearable
                  :hide-details="true"
                  v-if="header.filterable && !header.filterSpecial"
                  v-model="filters[header.value]"
                  :id="header.value"
                  @input="applyFilters(header.key, $event)"
                  outlined
                  dense
                  autocomplete="off"
                ></v-text-field>
                <v-select
                  :hide-details="true"
                  @change="applyFilters('status', $event)"
                  item-value="value"
                  item-text="title"
                  v-model="filters[header.text]"
                  outlined
                  dense
                  v-else-if="header.filterSpecial && header.text == 'Status'"
                  :items="[
                    { value: '', title: 'All' },
                    { value: '1', title: 'Online' },
                    {
                      value: '2',
                      title: 'Offline',
                    },
                  ]"
                ></v-select>
              </v-container>
            </td>
          </tr>
        </template> -->
        <template v-slot:item.sno="{ item, index }">
          {{ ++index }}
        </template>
        <template v-slot:item.name="{ item }">
          {{ caps(item.name) }}
        </template>
        <template v-slot:item.short_name="{ item }">
          {{ caps(item.short_name) }}
        </template>
        <template v-slot:item.location="{ item }">
          {{ caps(item.location) }}
        </template>
        <template v-slot:item.device_id="{ item }">
          {{ item.device_id }}
        </template>
        <template v-slot:item.function="{ item }">
          <img
            title="Auto (In and Out)"
            v-if="item.function == 'auto'"
            src="/icons/function_in_out.png"
            style="width: 30px"
          />
          <img
            title="Only In"
            v-else-if="item.function == 'In'"
            src="/icons/function_in.png"
            style="width: 30px"
          />
          <img
            title="Only Out"
            v-else-if="item.function == 'Out'"
            src="/icons/function_out.png"
            style="width: 30px"
          />
        </template>

        <template v-slot:item.device_type="{ item }">
          <img
            title="All (Attendance and Access Control )"
            v-if="item.device_type == 'all'"
            src="/icons/device_type_all.png"
            style="width: 30px"
          />
          <img
            title="Only Access Control"
            v-else-if="item.device_type == 'Access Control'"
            src="/icons/device_type_access_control.png"
            style="width: 30px"
          />
          <img
            title="Only Attendance"
            v-else-if="item.device_type == 'Attendance'"
            src="/icons/device_type_attendance.png"
            style="width: 30px"
          />
        </template>
        <template v-slot:item.door_open="{ item }">
          <img
            style="cursor: pointer"
            title="Click to Open Door"
            @click="open_door(item.device_id)"
            src="/icons/door_open.png"
            class="iconsize30"
          />
        </template>
        <template v-slot:item.door_close="{ item }">
          <img
            style="cursor: pointer"
            title="Click to Close Door"
            @click="close_door(item.device_id)"
            src="/icons/door_close.png"
            class="iconsize30"
          />
        </template>
        <template v-slot:item.always_open="{ item }">
          <img
            style="cursor: pointer"
            title="Click to Always Open settings"
            @click="open_door_always(item.id)"
            src="/icons/always_open.png"
            class="iconsize30"
          />
        </template>

        <template v-slot:item.sync_date_time="{ item }">
          <img
            style="cursor: pointer"
            title="Click Sync UTC Time"
            @click="sync_date_time(item)"
            src="/icons/sync_date_time.png"
            class="iconsize30"
          />
        </template>

        <template v-slot:item.open_always="{ item }"> </template>
        <template v-slot:item.status_id="{ item }">
          <img
            title="Online"
            v-if="item.status.name == 'active'"
            src="/icons/device_status_open.png"
            style="width: 30px"
          />
          <img
            title="Offline"
            v-else
            src="/icons/device_status_close.png"
            style="width: 30px"
          />

          <!-- <img
            @click="sync_date_time(item)"
            :src="getDeviceStatusIcon(item)"
            class="iconsize30"
          /> -->
          <!-- <v-chip
            small
            class="p-2 mx-1"
            :color="item.status.name == 'active' ? 'primary' : 'error'"
          >
            {{ item.status.name == "active" ? "online" : "offline" }}
          </v-chip> -->
        </template>
        <template v-slot:item.status="{ item }">
          <!-- <v-chip
            small
            class="p-2"
            color="primary"
            @click="open_door(item.device_id)"
          >
            Open
          </v-chip>
          <v-chip
            small
            class="p-2 mx-1"
            color="primary"
            @click="open_door_always(item.device_id)"
          >
            Open Always
          </v-chip>

          <v-chip
            small
            class="p-2"
            color="error"
            @click="open_door_always(item.device_id)"
          >
            Close
          </v-chip> -->
        </template>
        <!-- <template v-slot:item.sync_date_time="{ item }">
          <v-chip
            small
            class="p-2 mx-1"
            @click="sync_date_time(item)"
            :color="'primary'"
          >
            {{
              item.sync_date_time == "---"
                ? "click to sync"
                : item.sync_date_time
            }}
          </v-chip>
        </template> -->
        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <div class="text-right">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </div>
            </template>
            <v-list width="120" dense>
              <v-list-item @click="editItem(item)">
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-pencil </v-icon>
                  Edit
                </v-list-item-title>
              </v-list-item>
              <v-list-item @click="deleteItem(item)">
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
  </div>
</template>
<script>
// import Back from "../../components/Snippets/Back.vue";
import timeZones from "../../defaults/utc_time_zones.json";
import DeviceAccessSettings from "../../components/DeviceAccessSettings.vue";
export default {
  components: { DeviceAccessSettings },

  data: () => ({
    dialogAccessSettings: false,
    popup_device_id: "",
    editDialog: false,
    showFilters: false,
    filters: {},
    isFilter: false,
    totalRowsCount: 0,
    datatable_search_textbox: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    timeZones: timeZones,
    payload: {
      name: "",
      device_type: "",
      device_id: "",
      model_number: "",
      status_id: "",
      company_id: "",
      location: "",
      short_name: "",
      ip: "",
      port: "",
    },
    Model: "Device",
    pagination: {
      current: 1,
      total: 0,
      per_page: 100,
    },
    options: {},
    endpoint: "device",
    search: "",
    snackbar: false,
    dialog: false,
    data: [],
    loading: false,
    total: 0,
    headers: [
      {
        text: "Sno",
        align: "left",
        sortable: false,
        value: "sno",
        filterable: false,
      },
      {
        text: "Name",
        align: "left",
        sortable: false,
        value: "name",
        filterable: false,
      },
      {
        text: "Short Name",
        align: "left",
        sortable: false,
        value: "short_name",
        filterable: false,
      },
      // {
      //   text: "Branch",
      //   align: "left",
      //   sortable: false,
      //   value: "branch",
      //   filterable: false,
      // },

      {
        text: "Location",
        align: "left",
        sortable: false,
        value: "location",
        filterable: false,
      },
      {
        text: "Time zone",
        align: "left",
        sortable: false,
        value: "utc_time_zone",
        filterable: false,
      },
      {
        text: "Model Number",
        align: "left",
        sortable: false,
        value: "model_number",
        filterable: false,
      },

      {
        text: "Device Serial Number",
        align: "left",
        sortable: false,
        value: "device_id",
        filterable: false,
      },
      {
        text: "Function",
        align: "left",
        sortable: false,
        value: "function",
        filterable: false,
      },
      {
        text: "Type",
        align: "left",
        sortable: false,
        value: "device_type",
        filterable: false,
      },
      {
        text: "Door Open",
        align: "left",
        sortable: false,
        value: "door_open",
        filterable: false,
      },
      {
        text: "Door Close",
        align: "left",
        sortable: false,
        value: "door_close",
        filterable: false,
      },
      {
        text: "Always Open",
        align: "left",
        sortable: false,
        value: "always_open",
        filterable: false,
      },
      {
        text: "Time Sync",
        align: "left",
        sortable: false,
        value: "sync_date_time",
        filterable: false,
      },
      {
        text: "Status",
        align: "center",
        sortable: false,
        value: "status_id",
        filterable: false,
        filterSpecial: false,
      },
      {
        text: "Options",
        align: "center",
        sortable: false,
        value: "options",
        filterable: false,
        filterSpecial: false,
      },
    ],
    editedIndex: -1,
    response: "",
    errors: [],

    device_statusses: [],
    branches: [],
    branchesList: [],
    branch_id: "",
    isCompany: true,
    timeZoneOptions: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New device" : "Edit device";
    },
  },

  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
  },
  async created() {
    this.loading = true;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      return;
    }

    let branch_header = [
      {
        text: "Branch",
        align: "left",
        sortable: true,
        key: "branch_id", //sorting
        value: "company_branch.branch_name", //edit purpose

        filterable: true,
        filterSpecial: true,
      },
    ];
    this.headers.splice(1, 0, ...branch_header);

    try {
      const { data } = await this.$axios.get(`branches_list`, {
        params: {
          per_page: 100,
          company_id: this.$auth.user.company_id,
        },
      });
      this.branchesList = data;
    } catch (error) {
      // Handle the error
      console.error("Error fetching branch list", error);
    }

    this.getDataFromApi();
    this.getBranches();
    this.getDeviceStatus();
  },

  methods: {
    getTimezones() {
      return Object.keys(this.timeZones).map((key) => ({
        offset: this.timeZones[key].offset,
        time_zone: this.timeZones[key].time_zone,
        key: key,
        text: key + " - " + this.timeZones[key].offset,
      }));
    },
    getBranches() {
      this.$axios
        .get(`branch`, { company_id: this.$auth.user.company_id })
        .then(({ data }) => {
          this.branches = data.data;
        });
    },
    getDeviceStatus() {
      this.$axios.get(`device_status`).then(({ data }) => {
        this.device_statusses = data.data;
      });
    },
    datatable_save() {},
    datatable_cancel() {
      this.datatable_search_textbox = "";
    },
    datatable_open() {
      this.datatable_search_textbox = "";
    },
    datatable_close() {
      this.loading = false;
    },
    sync_date_time(item) {
      console.log(item.sync_date_time);
      let sync_able_date_time = this.getUTC_CurentTime(item.utc_time_zone);
      console.log(item.sync_date_time);
      console.log(sync_able_date_time);

      let options = {
        params: {
          sync_able_date_time: sync_able_date_time,
        },
      };
      confirm("Are you sure want to Sync UTC time to Device?") &&
        this.$axios
          .get(`sync_device_date_time/${item.device_id}`, options)
          .then(({ data }) => {
            // if (data.status) {
            //   const index = this.data.findIndex((row) => row.id == item.id);
            //   this.data.splice(index, 1, data.record);
            // }

            this.snackbar = true;
            this.response = data.message;
          });
    },
    closepopup() {
      this.snackbar = true;
      this.response = "Device Time details are updated successfully";
      this.dialogAccessSettings = false;
    },
    getUTC_CurentTime(targetTimezone) {
      // Define the target time zone
      //const targetTimezone = "America/New_York";

      // Create a Date object for the current local time
      const localDate = new Date();

      // Get the date and time components in the target time zone
      const options = {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        timeZone: targetTimezone,
        hour12: false,
      };
      const formattedDateTime = localDate.toLocaleDateString("en-US", options);

      // Display the formatted date and time
      console.log(formattedDateTime);

      let dt = new Date(formattedDateTime);

      let year = dt.getFullYear();
      let month = dt.getMonth() + 1;
      let day = dt.getDate();

      let hours = dt.getHours();
      hours = hours < 10 ? "0" + hours : hours;

      let minutes = dt.getMinutes();
      minutes = minutes < 10 ? "0" + minutes : minutes;

      let seconds = dt.getSeconds();
      seconds = seconds < 10 ? "0" + seconds : seconds;

      let sync_able_date_time = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
      console.log(sync_able_date_time);

      return sync_able_date_time;
    },
    open_door(device_id) {
      let options = {
        params: { device_id },
      };
      confirm("Are you sure want to open the Door?") &&
        this.$axios.get(`open_door`, options).then(({ data }) => {
          this.snackbar = true;
          this.response = data;
          // this.getDataFromApi();
        });
    },
    open_door_always(device_id) {
      this.popup_device_id = device_id;
      this.dialogAccessSettings = true;

      /////////// this.$router.push(`/device/time_settings/${device_id}`);
      // let options = {
      //   params: { device_id },
      // };
      // this.$axios.get(`open_door_always`, options).then(({ data }) => {
      //   this.snackbar = true;
      //   this.response = data.message;
      //   this.getDataFromApi();
      // });
    },
    close_door(device_id) {
      let options = {
        params: { device_id },
      };
      confirm("Are you sure want to close the Door?") &&
        this.$axios.get(`close_door`, options).then(({ data }) => {
          this.snackbar = true;
          this.response = data.message;
          this.getDataFromApi();
        });
    },
    can(permission) {
      let user = this.$auth;
      return;
      return (
        (user && user.permissions.some((e) => e.permission == permission)) ||
        user.master
      );
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    onPageChange() {
      this.getDataFromApi();
    },
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
    async getDataFromApi(
      url = this.endpoint,
      filter_column = "",
      filter_value = ""
    ) {
      if (url == "") url = this.endpoint;
      this.loading = true;
      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          branch_id: this.branch_id,
          company_id: this.$auth.user.company_id,
          ...this.filters,
        },
      };
      if (filter_column != "") {
        if (filter_column == "serach_status_name") {
          options.params[filter_column] =
            filter_value.toLowerCase() == "online"
              ? "active"
              : filter_value.toLowerCase() == "offline"
              ? "inactive"
              : "";
        } else options.params[filter_column] = filter_value;
      }
      await this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        if (filter_column != "" && data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }
        this.totalRowsCount = data.total;
        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
      });
    },
    async updateDevicesHealth() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      await this.$axios
        .get("/check_device_health", options)
        .then(({ data }) => {
          this.snackbar = true;
          this.response = data;
          this.getDataFromApi();
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
      this.errors = [];
      this.payload = {};
      this.editedIndex = item.id;

      this.payload = Object.assign({}, item);

      this.editDialog = true;
    },
    addItem() {
      this.payload = {};
      this.errors = [];
      if (!this.isCompany) {
        this.payload.branch_id = this.branch_id;
      }

      this.editedIndex = -1;
      this.editDialog = true;
    },
    store_device() {
      let id = this.editedIndex;
      let company_id = console.log(this.payload);
      let payload = this.payload;

      this.payload.company_id = this.$auth.user.company_id;
      if (this.editedIndex == -1) this.payload.status_id = 2;
      this.payload.ip = "0.0.0.0";
      this.payload.serial_number = this.payload.device_id;
      this.payload.port = "0000";

      delete this.payload.status;
      delete this.payload.company;
      delete this.payload.company_branch;

      this.loading = true;
      if (this.editedIndex == -1) {
        this.$axios
          .post(`/device`, payload)
          .then(({ data }) => {
            this.loading = false;
            if (!data.status) {
              this.errors = data.errors;
            } else if (data.status == "device_api_error") {
              this.errors = [];
              this.snackbar = true;
              this.response = "Check the Device information. There are errors.";
            } else {
              this.snackbar = true;
              this.response = "Device details are  Created successfully";
              this.getDataFromApi();
              this.editDialog = false;
            }
          })
          .catch((e) => console.log(e));
      } else {
        this.$axios
          .put(`/device/${id}`, payload)
          .then(({ data }) => {
            this.loading = false;
            if (!data.status) {
              this.errors = data.errors;
            } else if (data.status == "device_api_error") {
              this.errors = [];
              this.snackbar = true;
              this.response = "Check the Device information. There are errors.";
            } else {
              this.snackbar = true;
              this.response = "Device details are  updated successfully";
              this.getDataFromApi();
              this.editDialog = false;
            }
          })
          .catch((e) => console.log(e));
      }
    },

    getFunctionIcon(item) {
      if (item.function == "auto") {
        return "/icons/function_in_out.png?t=2";
      } else if (item.function == "In") {
        return "/icons/function_in.png";
      } else if (item.function == "Out") {
        return "/icons/function_out.png";
      }
    },
    getDeviceIcon(item) {
      if (item.device_type == "all") {
        return "/icons/device_type_all.png";
      } else if (item.device_type == "Access Control") {
        return "/icons/device_type_attendance.png";
      } else if (item.device_type == "Attendance") {
        return "/icons/device_type_access_control.png";
      }
    },
    getDeviceStatusIcon(item) {
      if (item.status.name == "active") {
        return "/icons/device_status_open.png";
      } else {
        return "/icons/device_status_close.png";
      }
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
              this.snackbar = data.status;
              this.response = data.message;
              this.getDataFromApi();
            }
          })
          .catch((err) => console.log(err));
    },
  },
};
</script>
<style scoped>
.v-text-field.v-text-field--enclosed .v-text-field__details,
.v-text-field.v-text-field--enclosed .v-text-field__details,
.v-text-field__details,
.v-text-field.v-text-field--enclosed .v-text-field__details {
  margin-bottom: 0px !important;
  padding: 0px !important;
}
.v-messages {
  min-height: 0px !important;
}
</style>

<!-- <style>
.v-dialog {
  background-color: #fff;
}
</style>
<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css";
</style> -->
