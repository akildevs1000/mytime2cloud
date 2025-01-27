<template>
  <div v-if="can(`employee_schedule_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-dialog v-model="editDialog" :key="key" width="600">
      <v-card :loading="loading_dialog">
        <v-card-title dense class="popup_background_noviolet">
          <div style="color: black" v-if="!empId">Add Timezone</div>
          <div style="color: black" v-else>
            {{
              !isEdit
                ? "View Timezone Mapping(s)"
                : "Update Timezone Mapping(s)"
            }}
          </div>
          <v-spacer></v-spacer>

          <v-icon color="black" @click="editDialog = false" outlined dark>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>

        <v-card-text>
          <v-row v-if="!empId"> </v-row>

          <v-row>
            <v-col class="text-right">
              <v-btn class="primary" v-if="isEdit" small @click="addRow(1)">
                <b>Add +</b>
              </v-btn>
            </v-col>
          </v-row>

          <!-- <v-row>
            <v-col md="3" v-if="!empId">
              <v-select
                label="Branch"
                @change="filterDepartmentsByBranch()"
                cols="1"
                :hide-details="true"
                item-value="id"
                item-text="branch_name"
                v-model="filterPopupBranchId"
                outlined
                dense
                clearable
                :items="branchesList"
              ></v-select>
            </v-col>

            <v-col md="3" v-if="!empId">
              <v-autocomplete
                label="Departments"
                height="40px"
                class="announcement-dropdown1"
                outlined
                dense
                @change="employeesByDepartment"
                v-model="filterDepartmentIds"
                :items="departments"
                multiple
                item-text="name"
                item-value="id"
                placeholder="Departments"
              >
                <template v-if="departments.length" #prepend-item>
                  <v-list-item @click="toggleDepartmentSelection">
                    <v-list-item-action>
                      <v-checkbox
                        @click="toggleDepartmentSelection"
                        v-model="selectAllDepartment"
                        :indeterminate="isIndeterminateDepartment"
                        :true-value="true"
                        :false-value="false"
                      ></v-checkbox>
                    </v-list-item-action>
                    <v-list-item-content>
                      <v-list-item-title>
                        {{
                          selectAllDepartment ? "Unselect All" : "Select All"
                        }}
                      </v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </template>
                <template v-slot:selection="{ item, index }">
                  <span v-if="index === 0 && filterDepartmentIds.length == 1">{{
                    item.name
                  }}</span>
                  <span
                    v-else-if="
                      index === 1 &&
                      filterDepartmentIds.length == departments.length
                    "
                    class=" "
                  >
                    All Selected
                  </span>
                  <span v-else-if="index === 1" class=" ">
                    {{ filterDepartmentIds.length }} Seleted
                     
                  </span>
                </template>
              </v-autocomplete>
            </v-col>
            <v-col md="3" v-if="!empId">
              <v-select
                label="All Employees"
                @change="getEmployeesByScheduleFilter()"
                cols="1"
                :hide-details="true"
                v-model="filterPopupEmployeeSchedule"
                outlined
                dense
                clearable
                item-value="id"
                item-text="name"
                :items="[
                  { id: 2, name: 'All Employees' },
                  { id: 0, name: 'Unscheduled' },
                  { id: 1, name: 'Scheduled' },
                ]"
              ></v-select>
            </v-col>
            <v-col md="3" v-if="!empId">
              <v-autocomplete
                label="Employees"
                height="40px"
                class="announcement-dropdown1"
                outlined
                dense
                v-model="filterEmployeeIds"
                :items="employees_dialog"
                multiple
                item-text="name_with_user_id"
                item-value="system_user_id"
                placeholder="Employees"
                :error-messages="
                  errors && errors.employees ? errors.employees[0] : ''
                "
                color="background"
              >
                <template v-if="employees_dialog.length" #prepend-item>
                  <v-list-item @click="toggleEmployeeSelection">
                    <v-list-item-action>
                      <v-checkbox
                        @click="toggleEmployeeSelection"
                        v-model="selectAllEmployee"
                        :indeterminate="isIndeterminateEmployee"
                        :true-value="true"
                        :false-value="false"
                      ></v-checkbox>
                    </v-list-item-action>
                    <v-list-item-content>
                      <v-list-item-title>
                        {{ selectAllEmployee ? "Unselect All" : "Select All" }}
                      </v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </template>
                <template v-slot:selection="{ item, index }">
                  <span v-if="index === 0 && filterEmployeeIds.length == 1"
                    >{{ item.first_name }} {{ item.last_name }}
                    {{ item.schedules_count }}</span
                  >
                  <span
                    v-else-if="
                      index === 1 &&
                      filterEmployeeIds.length == employees_dialog.length
                    "
                    class=" "
                  >
                    All Selected
                  </span>
                  <span v-else-if="index === 1" class=" ">
                    {{ filterEmployeeIds.length }} Seleted
                    
                  </span>
                </template>
              </v-autocomplete>
            </v-col>
          </v-row> -->

          <v-row v-for="(item, i) in schedules_temp_list" :key="key + i">
            <!-- <v-col md="12">
                <v-checkbox
                  :readonly="!isEdit"
                  v-model="item.isAutoShift"
                  hide-details
                  label="Auto Shift"
                ></v-checkbox>
              </v-col> -->

            <!-- <v-col>
                <v-select
                  multiple
                  style="width: 250px"
                  cols="1"
                  :hide-details="true"
                  item-value="id"
                  item-text="branch_name"
                  outlined
                  dense
                  clearable
                  :items="[
                    { branch_name: `All Employees`, id: `` },
                    ...employeesList,
                  ]"
                ></v-select>
              </v-col> -->
            <!-- {{ empId ? "not empty" : "Empty" }} -->
            <v-col>
              <!-- <div>Shift Name</div> -->

              <v-autocomplete
                label="Device Name"
                :error="errors && errors.device_table_id"
                :error-messages="
                  errors && errors.device_table_id
                    ? errors.device_table_id[0]
                    : ''
                "
                @change="runShiftFunctionWithAuto(item)"
                outlined
                dense
                v-model="item.device_table_id"
                x-small
                :items="devicesList"
                item-value="id"
                item-text="name"
                :disabled="!isEdit"
              ></v-autocomplete>
            </v-col>
            <v-col>
              <v-autocomplete
                label="Timezone Name"
                :error="errors && errors.timezone_table_id"
                :error-messages="
                  errors && errors.timezone_table_id
                    ? errors.timezone_table_id[0]
                    : ''
                "
                @change="runShiftFunctionWithAuto(item)"
                outlined
                dense
                v-model="item.timezone_table_id"
                x-small
                :items="timezonesList"
                item-value="id"
                item-text="timezone_name"
                :disabled="!isEdit"
              ></v-autocomplete>
            </v-col>

            <v-col cols="2">
              <v-row>
                <v-col>
                  <v-icon
                    v-if="isEdit"
                    @click="removeItem(i, item)"
                    class="ml-2 pt-3"
                    color="black"
                    dark
                    >mdi mdi-delete</v-icon
                  >
                </v-col>
              </v-row>
              <!-- <v-checkbox
                    :readonly="!isEdit"
                    style="margin-top: 4px"
                    v-model="item.is_over_time"
                  ></v-checkbox> -->
            </v-col>
          </v-row>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="text-center">
          <!-- <v-spacer></v-spacer> -->
          <!-- <v-btn dark small color="grey" @click="editDialog = false">
                Close
              </v-btn> -->
          <div style="width: 100%">
            <v-btn v-if="isEdit" dark small color="primary" @click="update">
              Submit
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialog" width="1300">
      <v-card>
        <v-card-title class="text-h5">
          Schedule Employees
          <v-spacer></v-spacer>
          <v-btn dark small color="grey" @click="close"> Close </v-btn> &nbsp;
          <v-btn dark small color="primary" @click="save"> Submit </v-btn>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-row>
            <v-col md="4">
              <!-- <v-row>
                <v-col md="12">
                  <div class="mb-5">
                    <span class="text-h6">Filters</span>
                  </div>
                  <div class="mb-1">Department</div>

                  <v-autocomplete
                    outlined
                    dense
                    @change="runMultipleFunctions"
                    v-model="department_ids"
                    multiple
                    x-small
                    :items="departments"
                    item-value="id"
                    item-text="name"
                    :disabled="is_edit == true ? true : false"
                  ></v-autocomplete>
                  <div class="mb-1">Sub Department</div>
                  <v-autocomplete
                    outlined
                    dense
                    @change="getEmployeesBySubDepartment"
                    v-model="sub_department_ids"
                    multiple
                    x-small
                    :items="sub_departments"
                    item-value="id"
                    item-text="name"
                    :disabled="is_edit == true ? true : false"
                  ></v-autocomplete>

                  <div class="mb-1">Shift Types</div>

                  <v-autocomplete
                    :error="errors && errors.shift_type_id"
                    :error-messages="
                      errors && errors.shift_type_id
                        ? errors.shift_type_id[0]
                        : ''
                    "
                    @change="runShiftTypeFunction"
                    outlined
                    dense
                    v-model="shift_type_id"
                    x-small
                    :items="shift_types"
                    item-value="id"
                    item-text="name"
                  ></v-autocomplete>

                  <div class="mb-1">Shifts</div>
                  <v-autocomplete
                    :error="errors && errors.shift_id"
                    :error-messages="
                      errors && errors.shift_id ? errors.shift_id[0] : ''
                    "
                    @change="runShiftFunction"
                    outlined
                    dense
                    v-model="shift_id"
                    x-small
                    :items="shifts"
                    item-value="id"
                    item-text="name"
                  ></v-autocomplete>
                  <div class="mb-6">
                    <div>From</div>
                    <v-menu
                      v-model="from_menu"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="from_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          outlined
                          dense
                          :hide-details="true"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="from_date"
                        @input="from_menu = false"
                      ></v-date-picker>
                    </v-menu>
                  </div>
                  <div class="mb-6">
                    <div>To</div>
                    <v-menu
                      v-model="to_menu"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="to_date"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          outlined
                          dense
                          :hide-details="true"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="to_date"
                        @input="to_menu = false"
                      ></v-date-picker>
                    </v-menu>
                  </div>
                  <v-checkbox
                    dense
                    v-model="isOverTime"
                    label="Overtime Allowed"
                  ></v-checkbox>
                </v-col>
              </v-row> -->
            </v-col>

            <v-col md="8">
              <v-row>
                <v-col md="6">
                  <div class="mb-5">
                    <span class="text-h6">Employees List</span>
                  </div>
                </v-col>
                <v-col md="6">
                  <div class="text-right">
                    <v-text-field
                      @input="dialogSearchIt"
                      dense
                      v-model="dialog_search"
                      append-icon="mdi-magnify"
                      single-line
                      hide-details
                    ></v-text-field>
                  </div>
                </v-col>
              </v-row>

              <v-data-table
                v-model="employee_ids"
                show-select
                item-key="id"
                :headers="headers_dialog"
                :items="employees_dialog"
                :server-items-length="total_dialog"
                :loading="loading_dialog"
                :options.sync="options_dialog"
                :footer-props="{
                  itemsPerPageOptions: [20, 50, 100, 500, 1000],
                }"
              >
              </v-data-table>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>

          <v-btn dark small color="grey" @click="close"> Close </v-btn>
          <v-btn dark small color="primary" @click="save"> Submit </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-card elevation="0" class="mt-2">
      <v-toolbar class="mb-2 white--text" color="white" dense flat>
        <v-toolbar-title class="black--text">
          <span>Timezone Employees List</span>
        </v-toolbar-title>
        <!-- <span class="black--text"> Schedule List1</span> -->

        <v-btn
          dense
          class="ma-0 px-0"
          x-small
          :ripple="false"
          text
          title="Reload"
        >
          <v-icon class="ml-2" @click="clearFilters" dark
            >mdi mdi-reload</v-icon
          >
        </v-btn>

        <v-spacer></v-spacer>
        <span class="mt-8" style="width: 220px">
          <v-text-field
            style="width: 200px"
            height="20"
            class="employee-schedule-search-box"
            @input="getDataFromApi()"
            v-model="commonSearch"
            label="Search (min 3)"
            dense
            outlined
            type="text"
            append-icon="mdi-magnify"
            clearable
          ></v-text-field>
        </span>
        <span class="mt-8 pl-3" style="width: 220px">
          <v-autocomplete
            style="width: 200px"
            label="Devices"
            @change="getDataFromApi($event)"
            v-model="filter_device_id"
            :items="[{ id: null, name: `All Devices` }, ...devicesList]"
            dense
            placeholder="All Devices"
            outlined
            item-value="id"
            item-text="name"
            class="dropdownautocomplete"
            clearable
          >
          </v-autocomplete>
        </span>
        <span class="mt-8 pl-3" style="width: 220px">
          <v-autocomplete
            style="width: 200px"
            label="Timezone"
            @change="getDataFromApi($event)"
            v-model="filter_timezone_id"
            :items="[
              { id: null, timezone_name: `All Timezones` },
              ...timezonesList,
            ]"
            dense
            placeholder="All Timezones"
            outlined
            item-value="id"
            item-text="timezone_name"
            class="dropdownautocomplete"
            clearable
          >
          </v-autocomplete>
        </span>
        <span>
          <v-btn
            v-if="can(`timezone_mapping_create`)"
            dense
            class="ma-0 px-0"
            x-small
            :ripple="false"
            text
            title="Add Timezone"
          >
            <v-icon class="ml-2" @click="goToCreatePage()" dark
              >mdi mdi-plus-circle</v-icon
            >
          </v-btn>
        </span>
        <!-- <span cols="4" class="mt-1" style="width: 190px">
          <v-select
            height="30px"
            style="width: 180px"
            class="custom-text-field-height employee-schedule-cropdown"
            :hide-details="true"
            @change="filterEmployees()"
            item-value="id"
            item-text="name"
            v-model="filterScheduledEmp"
            outlined
            dense
            :items="[
              { name: `All Employees  `, id: `` },
              { name: `Scheduled Only`, id: `1` },
              { name: `Un-Scheduled`, id: `0` },
            ]"
          ></v-select>
        </span> -->
        <!-- <span cols="2" class="mt-1" style="max-width: 140px">
          <v-btn
            dense
            class="ma-2 px-1 primary"
            fill
            dark
            small
            @click="openScheduleDialog"
          >
            + Add Schedule
          </v-btn>
        </span>
        <span cols="2" class="mt-1" style="max-width: 140px">
          <ScheduleEmployeeDelete
            @response="handleScheduleEmployeeDeleteResponse"
          />
        </span> -->
      </v-toolbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <v-data-table
        dense
        :headers="headers_table"
        :items="employees"
        v-model="employeesSelected"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [20, 50, 100, 500, 1000],
        }"
        class="elevation-1"
        :server-items-length="totalRowsCount"
      >
        <template v-slot:header="{ props: { headers } }">
          <tr v-if="isFilter">
            <td></td>
            <td v-for="header in headers_table" :key="header.text">
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
                  v-if="
                    header.filterSpecial && header.value == 'schedules_count'
                  "
                  :hide-details="true"
                  @change="applyFilter()"
                  item-value="id"
                  item-text="name"
                  v-model="filters[header.value]"
                  outlined
                  dense
                  clearable
                  :items="[
                    { name: `All`, id: `` },
                    { name: `Scheduled`, id: `1` },
                    { name: `Un-Scheduled`, id: `0` },
                  ]"
                ></v-select>
                <v-select
                  v-if="header.filterSpecial && header.value == 'branch_id'"
                  :hide-details="true"
                  clearable
                  @change="applyFilter()"
                  item-value="id"
                  item-text="branch_name"
                  v-model="filters[header.value]"
                  outlined
                  dense
                  :items="[
                    { branch_name: `All Branches`, id: `` },
                    ...branchesList,
                  ]"
                ></v-select>
                <v-select
                  v-if="header.filterSpecial && header.value == 'isOverTime'"
                  :hide-details="true"
                  clearable
                  @change="applyFilter()"
                  item-value="value"
                  item-text="title"
                  v-model="filters[header.value]"
                  outlined
                  dense
                  :items="[
                    { value: '', title: 'All' },
                    { value: '1', title: 'Yes' },
                    { value: '0', title: 'No' },
                  ]"
                ></v-select>

                <v-select
                  :id="header.key"
                  :hide-details="true"
                  v-if="header.filterSpecial && header.value == 'shift.name'"
                  outlined
                  dense
                  small
                  v-model="filters[header.filterName]"
                  item-text="name"
                  item-value="id"
                  :items="shifts_for_filter"
                  placeholder="Shift"
                  solo
                  flat
                  clearable
                  @change="applyFilters()"
                ></v-select>
                <v-select
                  :id="header.key"
                  :hide-details="true"
                  v-if="
                    header.filterSpecial && header.value == 'shift_type.name'
                  "
                  clearable
                  outlined
                  dense
                  small
                  v-model="filters[header.filterName]"
                  item-text="name"
                  item-value="id"
                  :items="shiftsTypes_for_filter"
                  placeholder="Shift"
                  solo
                  flat
                  @change="applyFilters()"
                ></v-select>

                <v-menu
                  v-if="header.filterSpecial && header.value == 'from_date'"
                  ref="from_menu_filter"
                  v-model="from_menu_filter"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      :hide-details="!from_date_filter"
                      outlined
                      dense
                      v-model="filters[header.value]"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                      placeholder="Schedule Start Date"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    style="height: 350px"
                    v-model="filters[header.value]"
                    no-title
                    scrollable
                    @input="applyFilter()"
                  >
                    <v-spacer></v-spacer>

                    <v-btn
                      text
                      color="primary"
                      @click="
                        filters[header.value] = '';
                        from_menu_filter = false;
                        applyFilter();
                      "
                    >
                      Clear
                    </v-btn>
                  </v-date-picker>
                </v-menu>
                <v-menu
                  v-if="header.filterSpecial && header.value == 'to_date'"
                  ref="to_menu_filter"
                  v-model="to_menu_filter"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      :hide-details="!to_date_filter"
                      outlined
                      dense
                      v-model="filters[header.value]"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                      placeholder="Schedule To Date"
                    ></v-text-field>
                  </template>

                  <v-date-picker
                    style="height: 350px"
                    v-model="filters[header.value]"
                    no-title
                    scrollable
                    @input="applyFilter()"
                  >
                    <v-spacer></v-spacer>
                    <v-btn
                      text
                      color="primary"
                      @click="
                        filters[header.value] = '';
                        to_menu_filter = false;
                        applyFilter();
                      "
                    >
                      Clear
                    </v-btn>
                  </v-date-picker>
                </v-menu>
              </v-container>
            </td>
          </tr>
        </template>
        <template v-slot:item.sno="{ item, index }">
          {{
            currentPage
              ? (currentPage - 1) * perPage +
                (cumulativeIndex + employees.indexOf(item))
              : "-"
          }}
        </template>
        <template v-slot:item.employee_id="{ item }">
          <strong>{{ item.employee_id }} </strong><br />
          <span title="Employee Device ID" class="secondary-value">{{
            item.system_user_id
          }}</span>
        </template>

        <template v-slot:item.first_name="{ item, index }" style="width: 300px">
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
                  item.profile_picture
                    ? item.profile_picture
                    : '/no-profile-image.jpg'
                "
              >
              </v-img>
            </v-col>
            <v-col style="padding: 10px">
              <strong>
                {{ item.first_name ? item.first_name : "---" }}
                {{ item.last_name ? item.last_name : "---" }}</strong
              >
              <div class="secondary-value">
                {{ item.designation ? caps(item.designation.name) : "---" }}
              </div>
            </v-col>
          </v-row>
        </template>

        <template v-slot:item.branch_id="{ item }">
          {{ item.branch && item.branch.branch_name }}
        </template>
        <template v-slot:item.department.name="{ item }">
          <strong>{{ caps(item.department.name) }}</strong>

          <div class="secondary-value">
            {{ caps(item.sub_department.name) }}
          </div>
        </template>
        <template v-slot:item.contact_number="{ item }">
          {{ item.phone_number }}
        </template>
        <!-- <template v-slot:item.employee_id="{ item }">
              {{ caps(item?.employee_id || "") }}
            </template>
            <template v-slot:item.employee.first_name="{ item }">
              {{ caps(item.first_name && item.first_name) }}
              {{ caps(item.last_name && item.last_name) }}
            </template> -->

        <template v-slot:item.devices="{ item }">
          <div title="Total Devices Mapped">
            {{ getDevicesCount(item.timezones_mapped) }}
          </div>
        </template>
        <template v-slot:item.timezones="{ item }">
          <div
            v-if="item.timezones_mapped && item.timezones_mapped.length > 1"
            title="Total Timezones Mapped"
          >
            <div v-if="filter_timezone_id != null">
              <!-- {{ item.timezones_mapped[0].timezone.timezone_name }} -->

              {{
                item.timezones_mapped.find(
                  (e) => e.timezone_table_id == filter_timezone_id
                )?.timezone.timezone_name
              }}(+{{ item.timezones_mapped.length - 1 }})
            </div>
            <div v-else>{{ item.timezones_mapped.length }}</div>
          </div>
          <div
            v-else-if="
              item.timezones_mapped && item.timezones_mapped.length == 1
            "
            title=" Timezones Mapped"
          >
            {{ item.timezones_mapped[0].timezone.timezone_name }}
          </div>
          <div v-else title="Total Timezones Mapped">---</div>
        </template>

        <!-- <template v-slot:item.isOverTime="{ item }">
          <v-icon v-if="item && item.isOverTime" color="success darken-1"
            >mdi-check</v-icon
          >
          <v-icon v-else color="error">mdi-close</v-icon>
        </template> -->

        <template v-slot:item.action="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="160" dense>
              <v-list-item
                v-if="can(`employee_schedule_view`)"
                @click="ScheduleItem(item, 'view')"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-eye </v-icon>
                  View
                </v-list-item-title>
              </v-list-item>
              <!-- <v-list-item @click="ScheduleItem(item, 'edit')">
                    <v-list-item-title style="cursor: pointer">
                      <v-icon color="secondary" small> mdi-plus-circle </v-icon>
                      Add
                    </v-list-item-title>
                  </v-list-item> -->
              <v-list-item
                v-if="can(`employee_schedule_edit`)"
                @click="ScheduleItem(item, 'edit')"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-pencil </v-icon>
                  Edit
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                v-if="can(`employee_schedule_delete`)"
                @click="deleteItem(item, 'edit')"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="error" small> mdi-delete </v-icon>
                  Delete All Timezones
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
import { extensions } from "@tiptap/vue-2";

export default {
  data: () => ({
    devicesList: [],
    filter_device_id: null,
    filter_timezone_id: null,
    timezonesList: [],
    schedulePopupWidth: "60%",
    commonSearch: "",
    key: 1,
    CustomFilterDatekey: 1,
    date_from: "",
    date_to: "",
    filterPopupEmployeeSchedule: 2,
    selectAllEmployee: false,
    filterEmployeeIds: "",

    selectAllDepartment: false,
    filterDepartmentIds: "",
    filterPopupBranchId: "",

    filterShifts: [],

    filterBranchId: "",
    employeesSelected: [],
    filterScheduledEmp: "",
    shifts_branch_wise: [],
    cumulativeIndex: 1,
    perPage: 20,
    currentPage: 1,
    branchesList: [],
    branch_id: null,
    shifts_for_filter: [],
    shiftsTypes_for_filter: [],

    from_date_filter: "",
    to_menu_filter: false,
    to_menu_filter: "",
    to_date_filter: "",

    from_menu_filter: false,
    from_date: "",

    totalRowsCount: 0,

    showFilters: false,
    filters: {},
    isFilter: false,
    datatable_search_textbox: "",
    datatable_searchById: "",
    filter_employeeid: "",
    snack: false,
    snackColor: "",
    snackText: "",
    displayNoRecords: false,
    from_date: new Date().toJSON().slice(0, 10),
    from_menu: false,
    to_date: new Date().toJSON().slice(0, 10),
    to_menu: false,

    from_menu: [],
    to_menu: [],

    pagination: {
      current: 1,
      total: 0,
      per_page: 20,
    },
    Module: "Employee Schedule",
    shift_types: [],
    manual_shift: {},
    options: { perPage: 20 },
    options_dialog: {},
    endpoint: "scheduled_employees_index",
    endpoint_dialog: "scheduled_employees_list",
    search: "",
    shifts_for_filter: [],
    dialog_search: "",
    snackbar: false,
    dialog: false,
    editDialog: false,
    key: 1,

    loading: false,
    loading_dialog: false,
    isEdit: false,
    total: 0,
    total_dialog: 0,

    headers_table: [
      // {
      //   text: "#",
      //   align: "left",
      //   sortable: false,
      //   value: "sno",
      //   filterable: false,
      // },
      {
        text: "Name",
        align: "left",
        sortable: true,
        value: "first_name",
        filterable: true,
        filterName: "employee_first_name",
      },
      {
        text: "Emp Id/Device Id",
        align: "left",
        sortable: true,
        value: "employee_id",
        filterable: true,
        filterName: "employee_id",
      },

      {
        text: "Department",
        align: "left",
        sortable: true,
        value: "department.name",
        filterable: true,
        filterName: "employee_first_name",
      },

      {
        text: "Mobile Number",
        align: "left",
        sortable: true,
        value: "contact_number",
        filterable: true,
        filterName: "contact_number",
        filterSpecial: true,
      },

      {
        text: "Devices",
        align: "left",
        sortable: true,
        value: "devices",
        filterable: true,
        filterName: "schedules",
        filterSpecial: true,
      },
      {
        text: "Timezones",
        align: "left",
        sortable: true,
        value: "timezones",
        filterable: true,
        filterName: "schedules_count",
        filterSpecial: true,
      },

      {
        text: "Actions",
        align: "center",
        value: "action",
        sortable: false,
        filterable: false,
        filterName: "",
      },
    ],
    department_ids: [],
    sub_department_ids: ["---"],
    employee_ids: [],
    shift_id: null,
    shift_type_id: "",
    isOverTime: false,
    is_edit: false,
    shift_slug: "",
    employees: [],
    employees_dialog: [],
    departments: [],
    sub_departments: [],
    shifts: [],
    ids: [],
    response: "",
    data: [],

    errors: [],
    headers_ids: [],
    headers_dialog: [
      {
        text: "Name",
        sortable: true,
        value: "employee.first_name",
      },
      {
        text: "E.ID",
        align: "left",
        sortable: false,
        value: "system_user_id",
      },

      {
        text: "Department",
        sortable: false,
        value: "department.name",
      },
    ],

    deleteIds: [],
    schedules_temp_list: [],
    schedules_temp_list_data: [],
    empId: "",
    branch_id: "",
  }),

  computed: {
    isIndeterminateDepartment() {
      return (
        this.filterDepartmentIds.length > 0 &&
        this.filterDepartmentIds.length < this.departments.length
      );
    },
    isIndeterminateEmployee() {
      return (
        this.filterEmployeeIds.length > 0 &&
        this.filterEmployeeIds.length < this.employees_dialog.length
      );
    },
  },

  watch: {
    filterDepartmentIds(value) {
      this.filterEmployeeIds = [];
      /////this.employeesByDepartment();
    },
    filterEmployeeIds(value) {
      //this.employeesByDepartment();
    },

    selectAllDepartment(value) {
      if (value) {
        this.filterDepartmentIds = this.departments.map((e) => e.id);
        this.employeesByDepartment();
      } else {
        this.filterDepartmentIds = [];

        this.employeesByDepartment();
      }
    },
    selectAllEmployee(value) {
      if (value) {
        this.filterEmployeeIds = this.employees_dialog.map(
          (e) => e.system_user_id
        );
      } else {
        this.filterEmployeeIds = [];
      }
    },
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
      if (!this.is_edit) {
        this.getDepartments(this.options);
        this.getDataFromApiForDialog();
      }
      this.getShiftTypes(this.options);
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
    options_dialog: {
      handler() {
        if (!this.is_edit) {
          this.getDataFromApiForDialog();
        }
      },
      deep: true,
    },
    search() {
      this.pagination.current = 1;
      this.searchIt();
    },
  },
  created() {
    const today = new Date();

    this.date_from = today.toISOString().slice(0, 10);
    this.date_to = today.toISOString().slice(0, 10);
    if (this.$auth.user.branch_id == null || this.$auth.user.branch_id == 0) {
      let branch_header = [
        {
          text: "Branch",
          align: "left",
          sortable: true,
          value: "branch_id",
          filterable: true,
          filterName: "branch_id",
          filterSpecial: true,
        },
      ];
      this.headers_table.splice(2, 0, ...branch_header);
    }
    this.loading = true;
    this.loading_dialog = true;
    this.getShifts();
    this.getDataFromApi();
    this.options = {
      params: {
        per_page: 20,
        company_id: this.$auth.user.company_id,
      },
    };

    // this.getShiftsForFilter();
    // this.getbranchesList();

    // this.filterDepartmentsByBranch();

    this.getTimezoneList();
    this.getDevicesList();
  },

  methods: {
    goToCreatePage() {
      this.$router.push("/timezonemapping/new");
    },
    getDevicesList() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          //branch_id: branch_id,
        },
      };
      this.$axios
        .get("device_list", options)
        .then(({ data }) => {
          this.devicesList = data;
        })
        .catch((err) => console.log(err));
    },
    getTimezoneList() {
      let options = {
        params: {
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company_id,
          //branch_id: branch_id,
        },
      };
      this.$axios
        .get("timezone", options)
        .then(({ data }) => {
          this.timezonesList = data.data;

          //   this.$axios
          //     .get("employee_timezone_mapping", options)
          //     .then(({ data }) => {
          //       data.data.forEach((element) => {
          //         let selectedindex = this.timezones.findIndex(
          //           (e) => e.timezone_id == element.timezone_id
          //         );

          //         if (selectedindex >= 0) this.timezones.splice(selectedindex, 1);
          //       });
          //     });
        })
        .catch((err) => console.log(err));
    },
    getDevicesCount(timezones) {
      if (timezones.length == 1) {
        return timezones[0].device.name;
      } else if (timezones.length > 1) {
        return timezones.length;
      }
      return "---";

      const uniqueDevices = timezones.filter(
        (item, index, self) =>
          self.findIndex((t) => t.device.name === item.device.name) === index
      );
      if (uniqueDevices.length == 1) {
        return uniqueDevices[0].device.name;
      } else if (uniqueDevices.length > 1) {
        return uniqueDevices.length;
      }
      return "---";
    },
    handleScheduleEmployeeDeleteResponse(message) {
      this.response = message;

      this.snackbar = true;

      this.getDataFromApi();
    },
    filterAttr(data, item) {
      this.date_from = data.from;
      this.date_to = data.to;
      item.from_date = data.from;
      item.to_date = data.to;
    },
    toggleDepartmentSelection() {
      this.selectAllDepartment = !this.selectAllDepartment;
    },
    toggleEmployeeSelection() {
      this.selectAllEmployee = !this.selectAllEmployee;
    },
    // filterEmployeesByDepartment() {
    //   this.selectAllEmployee = false;
    //   //this.getEmployees();
    //   this.loading_dialog = true;
    //   const { page, itemsPerPage } = this.options_dialog;

    //   let options = {
    //     params: {
    //       department_ids: this.filterDepartmentIds,

    //       company_id: this.$auth.user.company_id,
    //     },
    //   };
    //   this.employees_dialog = [];
    //   if (!this.filterDepartmentIds.length) {
    //     this.getEmployees();
    //     return;
    //   }

    //   this.$axios
    //     .get("employeesByDepartmentForAnnoucements", options)
    //     .then(({ data }) => {
    //       this.employees_dialog = data.data;
    //       this.loading_dialog = false;

    //       this.toggleEmployeeSelection();
    //     });
    // },
    getEmployees(url = "employee") {
      this.loading = true;

      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.employees_dialog = data.data;
        this.loading = false;
      });
    },
    filterDepartmentsByBranch() {
      this.selectAllDepartment = false;

      // this.shifts_branch_wise = this.shifts.filter(
      //   (e) => e.branch_id == this.filterPopupBranchId
      // );
      this.employeesSelected = [];

      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          branch_id:
            this.filterPopupBranchId != "" ? this.filterPopupBranchId : null,
        },
      };
      this.getDepartments(options);

      this.getShifts();
    },
    // getDepartments(branch_id = null) {
    //   let options = {
    //     params: {
    //       per_page: 10,
    //       company_id: this.$auth.user.company_id,
    //       branch_id: branch_id,
    //       //department_ids: this.$auth.user.assignedDepartments,
    //     },
    //   };
    //   this.$axios.get(`department-list`, options).then(({ data }) => {
    //     this.departments = data;
    //     this.departments.unshift({ name: "All Departments", id: "" });
    //   });
    // },
    applyBranchFilter() {
      this.branch_id = this.filterBranchId;
      this.filters["branch_id"] = this.branch_id;

      this.shifts_branch_wise = this.shifts.filter(
        (e) => e.branch_id == this.branch_id
      );
      this.employeesSelected = [];
      this.getDataFromApi();
    },
    filterEmployees() {
      this.filters["schedules_count"] = this.filterScheduledEmp;
      this.getDataFromApi();
      this.filterEmployeeIds = [];
    },
    updateIndex(page) {
      this.currentPage = page;
      this.cumulativeIndex = (page - 1) * this.perPage;
    },
    getbranchesList() {
      this.payloadOptions = {
        params: {
          company_id: this.$auth.user.company_id,

          // branch_id: this.$auth.user.branch_id,
        },
      };

      this.$axios.get(`branches_list`, this.payloadOptions).then(({ data }) => {
        this.branchesList = data;
        this.filterPopupBranchId = data[0]["id"];
        this.filterDepartmentsByBranch();
      });
    },
    applyFilter() {
      this.getDataFromApi();
      this.from_menu_filter = false;
      this.to_menu_filter = false;
    },
    gotoCreateSchedule() {
      this.$router.push(`/employee_schedule/create`);
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
      //this.datatable_search_textbox = '';
    },
    onPageChange() {
      this.getDataFromApi();
    },

    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    openScheduleDialog() {
      this.empId = null;
      // if (this.branch_id == 0) {
      //   alert("Select the Branch");

      //   return false;
      // }
      this.shifts_branch_wise = this.shifts.filter(
        (e) => e.branch_id == this.branch_id
      );
      this.schedules_temp_list = [];
      this.addRow(0);
      this.isEdit = true;

      this.filterDepartmentIds = [];
      this.filterEmployeeIds = [];
      this.key += 1;

      this.CustomFilterDatekey += 1;
      this.editDialog = true;
    },
    ScheduleItem(item, type) {
      this.key++;
      this.editDialog = true;
      this.empId = item.id;

      let options = {
        company_id: this.$auth.user.company_id,
      };
      this.loading_dialog = true;
      this.$axios
        .get(`get_timezones_by_employee/${item.id}`, { params: options })
        .then(({ data }) => {
          type == "edit" ? (this.isEdit = true) : (this.isEdit = false);
          this.schedules_temp_list = data;

          if (data.length == 0) {
            this.addRow(0);
          }

          //   this.schedules_temp_list.forEach((object) => {
          //     object.branch_id = item.branch_id;

          //     if (object.isAutoShift) {
          //       object.shift_id = 0;
          //     }
          //   });
          this.key += 1;
          this.CustomFilterDatekey += 1;

          // if (this.schedules_temp_list.length == 1) {
          //   this.schedulePopupWidth = "70%";
          // } else {
          //   this.schedulePopupWidth = "800px";
          // }

          // if (type == "edit") {
          //   this.schedulePopupWidth = "800px";
          // } else if (!this.empId) {
          //   this.schedulePopupWidth = "70%";
          // }
          if (!this.empId) {
            this.schedulePopupWidth = "60%";
          } else {
            this.schedulePopupWidth = "800px";
          }
          this.loading_dialog = false;
        });
    },

    set_date_save(from_menu, from, index) {
      from_menu.save(from);
      let toDate = this.setSevenDays(from);
      this.schedules_temp_list[index].to_date = toDate;
    },

    setSevenDays(selected_date) {
      const date = new Date(selected_date);
      date.setDate(date.getDate() + 6);
      let datetime = new Date(date);
      let d = datetime.getDate();
      d = d < "10" ? "0" + d : d;
      let m = datetime.getMonth() + 1;
      m = m < 10 ? "0" + m : m;
      let y = datetime.getFullYear();
      return `${y}-${m}-${d}`;
    },

    async update() {
      // if (this.schedules_temp_list.length == 0) {
      //   alert("Atleast one Shift is required");
      //   return false;
      // }

      if (!this.empId && this.filterEmployeeIds.length == 0) {
        alert("Atleast Select One Employee");
        return false;
      }
      var continueSavedata = true;
      this.loading_dialog = true;

      this.schedules_temp_list.forEach((element) => {
        let shiftsSelected = this.timezonesList.filter(
          (e) => e.id == element.timezone_table_id
        );

        if (shiftsSelected[0])
          element.device_timezone_id = shiftsSelected[0].timezone_id;
      });
      //if (!continueSavedata) return false;

      let payload = {
        //employee_ids: [this.empId],
        // employee_ids: this.empId
        //   ? [this.empId]
        //   : this.employeesSelected.map((e) => e.system_user_id),
        employee_ids: this.empId ? [this.empId] : this.filterEmployeeIds,

        mappings: this.schedules_temp_list,
        company_id: this.$auth.user.company_id,
        //replace_schedules: this.empId ? true : false,
        // branch_id: this.empId
        //   ? this.schedules_temp_list[0] && this.schedules_temp_list[0].branch_id
        //   : this.filterBranchId,
        // branch_id: this.empId
        //   ? this.schedules_temp_list[0] && this.schedules_temp_list[0].branch_id
        //   : this.filterPopupBranchId,
      };
      this.loading_dialog = true;
      await this.process(
        this.$axios.post(`timezones_employees_update`, payload)
      );

      setTimeout(() => {
        this.loading_dialog = false;
        this.editDialog = false;
      }, 1000);
    },

    removeItem(i, item) {
      if (item.id) {
        this.deleteIds.push(item.id);
      }
      this.schedules_temp_list.splice(i, 1);
    },

    addRow(id) {
      let item = {
        device_table_id: null,
        device_timezone_id: null,
        timezone_table_id: false,
      };
      if (this.schedules_temp_list.length < 5) {
        this.schedules_temp_list.push(item);
      }

      if (!this.empId) {
        this.schedulePopupWidth = "60%";
      } else {
        this.schedulePopupWidth = "800px";
      }
      // if (this.schedules_temp_list.length == 1) {
      //   this.schedulePopupWidth = "80%";
      // } else {
      //   this.schedulePopupWidth = "800px";
      // }

      // if (!this.empId) {
      //   this.schedulePopupWidth = "70%";
      // }
    },

    runShiftTypeFunction() {
      this.getShifts(this.shift_type_id);
    },

    close() {
      this.dialog = false;
      this.is_edit = false;
    },

    getShifts(shift_type_id) {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          branch_id: this.filterPopupBranchId,
        },
      };
      this.$axios.get("shift", options).then(({ data }) => {
        this.shifts = data.data
          .filter((e) => e.isAutoShift == false)
          .map((e) => ({
            shift_id: e.id,
            name: e.name,
            shift_type_id: e.shift_type_id,
            from_date: e.from_date,
            to_date: e.to_date,
            branch_id: e.branch_id,
          }));
        let options = {
          params: {
            company_id: this.$auth.user.company_id,
            branch_id: this.filterPopupBranchId,
          },
        };
        this.$axios.get("shift_dropdownlist", options).then(({ data }) => {
          this.filterShifts = data
            .filter((e) => e.isAutoShift == false)
            .map((e) => ({
              shift_id: e.id,
              name: e.name,
              shift_type_id: e.shift_type_id,
              from_date: e.from_date,
              to_date: e.to_date,
              branch_id: e.branch_id,
            }));

          // this.shifts = [
          //   { shift_id: 0, name: `Auto Shift`, shift_type_id: 3 },
          //   ...this.shifts,
          // ];
          this.filterShifts = [
            { shift_id: 0, name: `Auto Shift`, isAutoShift: true },
            ...this.filterShifts,
          ];
        });
      });
      // if (this.shift_type_id == 3) {
      //   this.shift_id = 0;
      //   this.shifts = [];
      //   return;
      // }

      // let options = {
      //   params: {
      //     shift_type_id: shift_type_id,
      //     company_id: this.$auth.user.company_id,
      //   },
      // };
      // this.$axios
      //   .get("shift_by_type", options)
      //   .then(({ data }) => {
      //     this.shifts = data;
      //   })
      //   .catch((err) => console.log(err));
    },
    // getShiftsForFilter() {
    //   let options = {
    //     params: {
    //       company_id: this.$auth.user.company_id,
    //     },
    //   };
    //   this.$axios
    //     .get("shift", options)
    //     .then(({ data }) => {
    //       this.shifts_for_filter = data.data;
    //       this.shifts_for_filter.unshift({ id: "", name: "All" });
    //       if (data[0]) this.addRow(data[0].id);
    //     })
    //     .catch((err) => console.log(err));

    //   this.$axios
    //     .get("shift_type", options)
    //     .then(({ data }) => {
    //       this.shiftsTypes_for_filter = data;
    //       this.shiftsTypes_for_filter.unshift({ id: "", name: "All" });
    //     })
    //     .catch((err) => console.log(err));
    // },
    // getShiftTypes(options) {
    //   this.$axios
    //     .get("shift_type", options)
    //     .then(({ data }) => {
    //       this.shift_types = data;
    //       this.shift_types.unshift({ id: "", name: "Select Shift Type" });
    //     })
    //     .catch((err) => console.log(err));
    // },

    // runShiftFunction() {
    //   this.shifts = this.shifts.filter((e) => e.id !== "---");
    // },

    runShiftFunctionWithAuto(item) {
      if (item.shift_id == "AutoShift") {
        item.isAutoShift = true;
      } else {
        item.isAutoShift = false;
      }

      this.shifts = this.shifts.filter((e) => e.id !== "---");
    },

    // getDepartments(options) {
    //   this.$axios
    //     .get("departments", options)
    //     .then(({ data }) => {
    //       this.departments = data.data;
    //       // this.departments.unshift({ id: "---", name: "Select All" });
    //     })
    //     .catch((err) => console.log(err));
    // },

    // getEmployeesByScheduleFilter() {
    //   this.employeesByDepartment();
    //   this.selectAllEmployee = false;
    //   this.filterEmployeeIds = [];
    // },

    // employeesByDepartment() {
    //   this.loading_dialog = true;

    //   const { page, itemsPerPage } = this.options_dialog;
    //   //this.perPage = 1000; //this.itemsPerPage;
    //   let options = {
    //     params: {
    //       department_ids: this.filterDepartmentIds,
    //       per_page: 10000,
    //       page: 1,
    //       company_id: this.$auth.user.company_id,
    //     },
    //   };

    //   if (!this.filterDepartmentIds.length) {
    //     this.employees_dialog = [];
    //     this.total_dialog = 0;
    //     this.loading_dialog = false;
    //     return;
    //   }

    //   this.$axios
    //     .get("employees_with_timezone_count", options)
    //     .then(({ data }) => {
    //       this.employees_dialog = data.data;

    //       this.employees_dialog_raw = this.employees_dialog;
    //       this.total_dialog = data.total;
    //       this.loading_dialog = false;

    //       if (this.filterPopupEmployeeSchedule == 0) {
    //         //unschedule
    //         this.employees_dialog = this.employees_dialog_raw.filter(
    //           (e) => e.schedule_active.id == null
    //         );
    //       } else if (this.filterPopupEmployeeSchedule == 1) {
    //         //schedule

    //         this.employees_dialog = this.employees_dialog_raw.filter(
    //           (e) => e.schedule_active.id != null
    //         );
    //       } else if (this.filterPopupEmployeeSchedule == 2) {
    //         //schedule

    //         this.employees_dialog = this.employees_dialog_raw;
    //       }

    //       //filterEmployeesBySchedule
    //     });
    // },

    // getEmployeesBySubDepartment() {
    //   this.loading_dialog = true;

    //   const { page, itemsPerPage } = this.options_dialog;

    //   let options = {
    //     params: {
    //       department_ids: this.department_ids,
    //       sub_department_ids: this.sub_department_ids,
    //       per_page: itemsPerPage,
    //       page: page,
    //       company_id: this.$auth.user.company_id,
    //     },
    //   };

    //   if (!this.sub_department_ids.length) {
    //     this.loading_dialog = false;
    //     return;
    //   }

    //   this.$axios
    //     .get(`employeesBySubDepartment`, options)
    //     .then(({ data }) => {
    //       this.employees_dialog = data.data;
    //       this.total_dialog = data.total;
    //       this.loading_dialog = false;
    //     })
    //     .catch((err) => console.log(err));
    // },

    // subDepartmentsByDepartment() {
    //   this.options.params.department_ids = this.department_ids;

    //   this.$axios
    //     .get(`sub-departments-by-departments`, this.options)
    //     .then(({ data }) => {
    //       this.sub_departments = data;
    //       this.sub_departments.unshift({
    //         id: "---",
    //         name: "Select All",
    //       });
    //     })
    //     .catch((err) => console.log(err));
    // },

    // runMultipleFunctions() {
    //   this.employeesByDepartment();
    //   this.subDepartmentsByDepartment();
    // },

    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getSearchRecords(filter_column = "", filter_value = "") {
      this.getDataFromApi(this.endpoint, filter_column, filter_value);
    },
    applyFilters(name, value) {
      this.getDataFromApi();
    },
    toggleFilter() {
      this.isFilter = !this.isFilter;
    },
    clearFilters() {
      this.filters = {};

      this.isFilter = false;
      this.getDataFromApi();
    },
    //main
    getDataFromApi(url = this.endpoint, filter_column = "", filter_value = "") {
      if (
        this.commonSearch &&
        this.commonSearch != "" &&
        this.commonSearch.length < 3
      ) {
        return false;
      }
      this.loading = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";

      let options = {
        params: {
          page: page,
          common_search:
            this.commonSearch && this.commonSearch != ""
              ? this.commonSearch
              : null,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          //department_ids: this.$auth.user.assignedDepartments,
          company_id: this.$auth.user.company_id,
          ...this.filters,
          filter_timezone_id: this.filter_timezone_id,
          filter_device_id: this.filter_device_id,
        },
      };

      //if (filter_value != "") options.params[filter_column] = filter_value;
      this.currentPage = page;
      this.$axios
        .get("employees_with_timezone_count", options)
        .then(({ data }) => {
          // if (filter_column != "" && data.data.length == 0) {
          //   this.snack = true;
          //   this.snackColor = "error";
          //   this.snackText = "No Results Found";
          //   this.loading = false;
          //   return false;
          // }

          this.employees = data.data;
          this.totalRowsCount = data.total;

          // if (this.filters["schedules_count"] != "") {
          //   if (this.filters["schedules_count"] == 0) {
          //     this.employees = this.employees.filter(
          //       (item) => item.schedule_all.length == 0
          //     );
          //   } else if (this.filters["schedules_count"] == 1) {
          //     this.employees = this.employees.filter(
          //       (item) => item.schedule_all.length > 0
          //     );
          //   }

          //   this.totalRowsCount = this.employees.length;
          // } else {
          //   this.totalRowsCount = data.total;
          // }

          this.pagination.current = data.current_page;
          this.pagination.total = data.last_page;
          this.loading = false;

          if (this.employees.length == 0) {
            this.displayNoRecords = true;
          }
        });

      //this.loading = false;
    },

    getDataFromApiForDialog(url = this.endpoint_dialog) {
      this.loading_dialog = true;

      const { page, itemsPerPage } = this.options_dialog;

      let options = {
        params: {
          per_page: itemsPerPage,
          page: page,
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(url, options).then(({ data }) => {
        this.employees_dialog = data.data;
        this.total_dialog = data.total;
        this.loading_dialog = false;
      });
    },

    searchIt() {
      let s = this.search.length;
      let search = this.search;
      if (s == 0) {
        this.getDataFromApi();
      } else if (s > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${search}`);
      }
    },

    dialogSearchIt(e) {
      if (e.length == 0) {
        this.getDataFromApiForDialog();
      } else if (e.length > 2) {
        this.employees_dialog = this.employees.filter(({ display_name: fn }) =>
          fn.includes(e)
        );
      }
    },

    delteteSelectedRecords() {
      let just_ids = this.ids.map((e) => e.schedule.id);

      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`schedule_employee/delete/selected`, {
            ids: just_ids,
          })
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
              alert("1");
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.ids = [];
              this.response = "Selected records has been deleted";
            }
          })
          .catch((err) => console.log(err));
    },

    async deleteItem(item) {
      this.loading_dialog = true;
      let payload = {
        employee_ids: this.empId ? [this.empId] : this.filterEmployeeIds,

        mappings: [],
        company_id: this.$auth.user.company_id,
      };

      await this.process(
        this.$axios.post(`timezones_employees_update`, payload)
      );
      this.loading_dialog = false;
    },

    save() {
      this.loading_dialog = true;
      if (this.employee_ids && this.employee_ids.length == 0) {
        this.loading_dialog = false;
        alert("Atleast 1 Employee must be selected");
        return;
      }

      this.errors = [];

      let payload = {
        shift_type_id: this.shift_type_id,
        shift_id: this.shift_id,
        company_id: this.$auth.user.company_id,
        isOverTime: this.isOverTime ? 1 : 0,
        employee_ids: this.employee_ids.map((e) => e.system_user_id),

        from_date: this.from_date,
        to_date: this.to_date,
      };

      if (this.is_edit) {
        this.process(
          this.$axios.post(
            `schedule_employees/${payload.employee_ids}`,
            payload
          )
        );
      } else {
        this.process(this.$axios.post(`schedule_employees`, payload));
      }
    },

    async process(method) {
      method
        .then(({ data }) => {
          if (!data.status) {
            if (data?.custom_errors) {
              this.custom_errors = data.custom_errors;
              this.errors = [];
            }
            if (data?.errors) {
              this.errors = data.errors;
              this.custom_errors = [];
            }
            this.loading_dialog = false;
            return;
          }
          this.response = data.message;
          this.snackbar = true;
          this.loading_dialog = false;
          this.editDialog = false;
          this.getDataFromApi();
          // this.getDataFromApiForDialog();
        })
        .catch((err) => console.log(err));
    },
  },
};
</script>
<style scoped>
.ele {
  animation: 2s fadeIn;
  animation-fill-mode: forwards;

  visibility: hidden;
}
@keyframes fadeIn {
  0% {
    opacity: 0;
  }
  100% {
    visibility: visible;
    opacity: 1;
  }
}
</style>
