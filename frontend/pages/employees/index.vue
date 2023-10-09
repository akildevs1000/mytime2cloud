<template>
  <div v-if="can('employee_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
    </div>
    <div v-if="!loading">
      <v-dialog persistent v-model="dialogCropping" width="500">
        <v-card style="padding-top: 20px">
          <v-card-text>
            <VueCropper
              v-show="selectedFile"
              ref="cropper"
              :src="selectedFile"
              alt="Source Image"
              :aspectRatio="1"
              :autoCropArea="0.9"
              :viewMode="3"
            ></VueCropper>
          </v-card-text>

          <v-card-actions>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 pull-left">
              <v-btn
                class="danger btn btn-danger text-left"
                text
                @click="closePopup()"
                style="float: left"
                >Cancel</v-btn
              >
            </div>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 text-right">
              <v-btn
                class="primary btn btn-danger text-right"
                @click="saveCroppedImageStep2(), (dialog = false)"
                >Crop</v-btn
              >
            </div>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog persistent v-model="employeeDialog" width="900">
        <v-card>
          <v-card-title dark class="popup_background" style="font-weight: 400">
            Add {{ Model }}
            <v-spacer></v-spacer>

            <v-icon
              outlined
              dark
              color="black"
              class="mr-5"
              title="Save Employee"
              :loading="loading"
              @click="store_data"
            >
              mdi mdi-content-save-all
            </v-icon>
            <v-icon
              title="Close"
              @click="employeeDialog = false"
              outlined
              dark
              color="black"
            >
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col md="6" sm="12" cols="12" dense>
                <v-row>
                  <v-col md="6" sm="12" cols="12">
                    <label class="col-form-label"
                      >Title <span class="text-danger">*</span></label
                    >
                    <v-select
                      v-model="employee.title"
                      :items="titleItems"
                      :hide-details="!errors.title"
                      :error="errors.title"
                      :error-messages="
                        errors && errors.title ? errors.title[0] : ''
                      "
                      dense
                      outlined
                    ></v-select>
                  </v-col>
                  <v-col md="6" sm="12" cols="12">
                    <label class="col-form-label"
                      >Joining Date <span class="text-danger">*</span></label
                    >
                    <div>
                      <v-menu
                        v-model="joiningDateMenuOpen"
                        :close-on-content-click="false"
                        transition="scale-transition"
                        offset-y
                        max-width="290px"
                        min-width="auto"
                      >
                        <template v-slot:activator="{ on, attrs }">
                          <v-text-field
                            :hide-details="!errors.joining_date"
                            :error-messages="
                              errors && errors.joining_date
                                ? errors.joining_date[0]
                                : ''
                            "
                            v-model="employee.joining_date"
                            persistent-hint
                            append-icon="mdi-calendar"
                            readonly
                            outlined
                            dense
                            v-bind="attrs"
                            v-on="on"
                          ></v-text-field>
                        </template>
                        <v-date-picker
                          style="min-height: 320px"
                          v-model="employee.joining_date"
                          no-title
                          @input="joiningDateMenuOpen = false"
                        ></v-date-picker>
                      </v-menu>
                    </div>
                  </v-col>
                  <v-col md="12" sm="12" cols="12" dense>
                    <label class="col-form-label"
                      >Display Name <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      :hide-details="!errors.display_name"
                      type="text"
                      v-model="employee.display_name"
                      :error="errors.display_name"
                      :error-messages="
                        errors && errors.display_name
                          ? errors.display_name[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="6" sm="12" cols="12" dense>
                    <label class="col-form-label"
                      >First Name <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      :hide-details="!errors.first_name"
                      type="text"
                      v-model="employee.first_name"
                      :error="errors.first_name"
                      :error-messages="
                        errors && errors.first_name ? errors.first_name[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="6" sm="12" cols="12" dense>
                    <label class="col-form-label"
                      >Last Name <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      :hide-details="!errors.last_name"
                      type="text"
                      v-model="employee.last_name"
                      :error="errors.last_name"
                      :error-messages="
                        errors && errors.last_name ? errors.last_name[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="6" cols="6" sm="6" dense>
                    <label class="col-form-label"
                      >Employee ID <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      type="text"
                      v-model="employee.employee_id"
                      :hide-details="!errors.employee_id"
                      :error="errors.employee_id"
                      :error-messages="
                        errors && errors.employee_id
                          ? errors.employee_id[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="6" cols="6" sm="6" dense>
                    <label class="col-form-label"
                      >Employee Device Id<span class="text-danger"
                        >*</span
                      ></label
                    >
                    <v-text-field
                      dense
                      outlined
                      type="text"
                      v-model="employee.system_user_id"
                      :hide-details="!errors.system_user_id"
                      :error="errors.system_user_id"
                      :error-messages="
                        errors && errors.system_user_id
                          ? errors.system_user_id[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col md="6" cols="6" sm="6" dense>
                    <label class="col-form-label"
                      >Mobile Number <span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      type="number"
                      v-model="employee.phone_number"
                      :hide-details="!errors.phone_number"
                      :error="errors.phone_number"
                      :error-messages="
                        errors && errors.phone_number
                          ? errors.phone_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col md="6" cols="6" sm="6" dense>
                    <label class="col-form-label"
                      >Whatsapp Number<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      dense
                      outlined
                      type="number"
                      v-model="employee.whatsapp_number"
                      :hide-details="!errors.whatsapp_number"
                      :error="errors.whatsapp_number"
                      :error-messages="
                        errors && errors.whatsapp_number
                          ? errors.whatsapp_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6">
                    <label class="col-form-label"
                      >Branch <span class="text-danger">*</span></label
                    >

                    <v-select
                      @change="filterDepartmentsByBranch($event)"
                      v-model="employee.branch_id"
                      :items="branchesList"
                      dense
                      placeholder="Select Branch"
                      outlined
                      item-value="id"
                      item-text="branch_name"
                      :error="errors.branch_id"
                      :error-messages="
                        errors && errors.branch_id ? errors.branch_id[0] : ''
                      "
                    >
                    </v-select>
                  </v-col>

                  <v-col cols="6">
                    <label class="col-form-label"
                      >Department <span class="text-danger">*</span></label
                    >
                    <v-autocomplete
                      :items="departments"
                      item-text="name"
                      item-value="id"
                      placeholder="Select"
                      v-model="employee.department_id"
                      :hide-details="!errors.department_id"
                      :error="errors.department_id"
                      :error-messages="
                        errors && errors.department_id
                          ? errors.department_id[0]
                          : ''
                      "
                      dense
                      outlined
                    ></v-autocomplete>
                  </v-col>
                </v-row>
              </v-col>
              <v-col class="col-sm-6">
                <div
                  class="form-group pt-15"
                  style="margin: 0 auto; width: 200px"
                >
                  <v-img
                    style="
                      width: 100%;
                      height: 200px;
                      border: 1px solid #5fafa3;
                      border-radius: 50%;
                      margin: 0 auto;
                    "
                    :src="previewImage || '/no-profile-image.jpg'"
                  ></v-img>
                  <br />
                  <v-btn
                    small
                    class="form-control primary"
                    @click="onpick_attachment"
                    >{{ !upload.name ? "Upload" : "Change" }} Profile Image
                    <v-icon right dark>mdi-cloud-upload</v-icon>
                  </v-btn>
                  <input
                    required
                    type="file"
                    @change="attachment"
                    style="display: none"
                    accept="image/*"
                    ref="attachment_input"
                  />

                  <span
                    v-if="errors && errors.profile_picture"
                    class="text-danger mt-2"
                    >{{ errors.profile_picture[0] }}</span
                  >
                </div>
              </v-col>
            </v-row>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions>
            <v-spacer></v-spacer>
            <!-- <v-btn small color="grey white--text" @click="employeeDialog = false">
              Close
            </v-btn> -->

            <v-btn
              v-if="can('employee_create')"
              small
              :loading="loading"
              color="primary"
              @click="store_data"
            >
              Submit
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog persistent v-model="editDialog" width="1250" :key="employeeId">
        <v-card>
          <v-tabs
            v-model="tab"
            class="popup_background"
            centered
            icons-and-text
            color="violet"
          >
            <v-tabs-slider></v-tabs-slider>

            <v-tab
              v-for="(item, index) in tabMenu"
              :key="index"
              :href="item.value"
            >
              {{ item.text }}
              <v-icon>{{ item.icon }}</v-icon>
            </v-tab>
            <v-icon
              @click="editDialog = false"
              style="margin-right: 4px"
              text-right
              outlined
              dark
              color="black"
            >
              mdi mdi-close-circle
            </v-icon>
          </v-tabs>

          <v-card-text>
            <v-tabs-items v-model="tab">
              <v-tab-item
                v-for="(tb, index) in tabMenu"
                :key="index"
                :value="`${index}`"
              >
                <component
                  :is="getComponent(tab)"
                  :employeeId="employeeId"
                  @close-popup="editDialog = false"
                  @eventFromchild="getDataFromApi"
                />
              </v-tab-item>
            </v-tabs-items>
          </v-card-text>
        </v-card>
      </v-dialog>
      <div class="text-center">
        <v-dialog v-model="viewDialog" width="1400px" :key="employeeId">
          <!-- <EmployeeDetails
            @close-parent-dialog="closeViewDialog"
            :employeeObject="employeeObject"
          /> -->
          <EmployeeDetails2
            @close-parent-dialog="closeViewDialog"
            :employeeObject="employeeObject"
          />
        </v-dialog>
      </div>
      <v-dialog persistent v-model="dialog" max-width="500px">
        <v-card>
          <v-card-title dense class="primary white--text background">
            Import Employee
            <v-spacer></v-spacer>
            <v-icon @click="dialog = false" outlined dark color="white">
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-file-input
                    accept="text/csv"
                    v-model="files"
                    placeholder="Upload your file"
                    label="File"
                    prepend-icon="mdi-paperclip"
                  >
                    <template v-slot:selection="{ text }">
                      <v-chip v-if="text" small label color="primary">
                        {{ text }}
                      </v-chip>
                    </template>
                  </v-file-input>
                  <br />
                  <a href="/employees.csv" download> Download Sample</a>
                  <br />
                  <span
                    v-if="errors && errors.length > 0"
                    class="error--text"
                    >{{ errors[0] }}</span
                  >
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn class="error" small @click="close"> Cancel </v-btn>

            <v-btn
              class="primary"
              :loading="btnLoader"
              small
              @click="importEmployee"
              >Save</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-dialog>

      <div v-if="can(`employee_view`)">
        <v-container>
          <v-card elevation="0" class="mt-2">
            <v-toolbar class="mb-2 white--text" color="white" dense flat>
              <v-toolbar-title>
                <span style="color: black">
                  {{ Model }}s
                </span></v-toolbar-title
              >
              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    dense
                    class="ma-0 px-0"
                    x-small
                    :ripple="false"
                    text
                    v-bind="attrs"
                    v-on="on"
                  >
                    <v-icon
                      color="white"
                      class="ml-2"
                      @click="clearFilters"
                      dark
                      >mdi mdi-reload</v-icon
                    >
                  </v-btn>
                </template>
                <span>Reload</span>
              </v-tooltip> -->
              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }"> -->
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
              <!-- </template>
                <span>Filter</span>
              </v-tooltip> -->

              <v-spacer></v-spacer>
              <!-- <v-btn rounded color="grey" dark flat> Rounded Button </v-btn>
              <v-btn color="blue-grey">Import </v-btn>
              <v-btn text>Import </v-btn>
              <v-btn text>Export </v-btn>
              <v-btn text> New </v-btn> -->
              <v-col cols="12" md="2">
                <v-text-field
                  @input="serachAll($event)"
                  style="height: 30px; margin-top: 5px"
                  clearable
                  :hide-details="true"
                  outlined
                  dense
                  autocomplete="off"
                  placeholder="Employee Details"
                ></v-text-field>
              </v-col>

              <v-icon
                title="Import File"
                @click="dialog = true"
                right
                dark
                color="black"
                size="x-large"
                >mdi-file-import</v-icon
              >
              <v-icon
                title="Download"
                @click="export_submit"
                right
                dark
                color="black"
                size="x-large"
                >mdi-arrow-down-bold-circle</v-icon
              >

              <v-icon
                title="New Employee"
                @click="openNewPage()"
                right
                dark
                color="black"
                size="x-large"
                >mdi-account-plus mdi-flip-h</v-icon
              >

              <!-- <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    dense
                    x-small
                    :ripple="false"
                    text
                    v-bind="attrs"
                    v-on="on"
                    @click="dialog = true"
                  >
                    <v-icon
                      right
                      dark
                      size="x-large"
                      style="transform: rotate(180deg)"
                      >mdi-download-circle</v-icon
                    >
                  </v-btn>
                </template>
                <span>Import</span>
              </v-tooltip> 
              <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    dense
                    x-small
                    :ripple="false"
                    text
                    v-bind="attrs"
                    v-on="on"
                    @click="export_submit"
                  >
                    <v-icon right size="x-large" dark
                      >mdi-download-circle</v-icon
                    >
                  </v-btn>
                </template>
                <span>Download</span>
              </v-tooltip>
              <v-tooltip top color="primary">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    dense
                    x-small
                    class="ma-0 px-0"
                    :ripple="false"
                    text
                    v-bind="attrs"
                    v-on="on"
                    @click="employeeDialog = true"
                  >
                    <v-icon
                      right
                      size="x-large"
                      dark
                      v-if="can('employee_create')"
                      >mdi-plus-circle</v-icon
                    >
                  </v-btn>
                </template>
                <span>Add New Employee</span>
              </v-tooltip>-->
            </v-toolbar>
            <v-data-table
              dense
              v-model="selectedItems"
              :headers="headers_table"
              :items="data"
              model-value="data.id"
              :loading="loadinglinear"
              :options.sync="options"
              :footer-props="{
                itemsPerPageOptions: [100, 500, 1000],
              }"
              class="elevation-1"
              :server-items-length="totalRowsCount"
            >
              <template v-slot:header="{ props: { headers } }">
                <tr v-if="isFilter">
                  <td v-for="header in headers" :key="header.text">
                    <v-container>
                      <v-text-field
                        clearable
                        @click:clear="
                          filters[header.value] = '';
                          applyFilters();
                        "
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
                        clearable
                        @click:clear="
                          filters[header.value] = '';
                          applyFilters();
                        "
                        :id="header.key"
                        :hide-details="true"
                        v-if="
                          header.filterSpecial &&
                          header.value == 'department.name.id'
                        "
                        outlined
                        dense
                        small
                        v-model="filters[header.key]"
                        item-text="name"
                        item-value="id"
                        :items="[
                          { name: `All Departments`, id: `` },
                          ...departments,
                        ]"
                        placeholder="Department"
                        solo
                        flat
                        @change="applyFilters(header.key, id)"
                      ></v-select>

                      <v-select
                        clearable
                        @click:clear="
                          filters[header.value] = '';
                          applyFilters();
                        "
                        :id="header.key"
                        :hide-details="true"
                        v-if="
                          header.filterSpecial &&
                          header.value == 'department.branch.id'
                        "
                        outlined
                        dense
                        small
                        v-model="filters[header.key]"
                        item-text="branch_name"
                        item-value="id"
                        :items="[
                          { branch_name: `All Branches`, id: `` },
                          ...branchesList,
                        ]"
                        placeholder="All Branches"
                        solo
                        flat
                        @change="applyFilters(header.key, id)"
                      ></v-select>

                      <v-select
                        clearable
                        @click:clear="
                          filters[header.value] = '';
                          applyFilters();
                        "
                        :id="header.key"
                        :hide-details="true"
                        v-if="
                          header.filterSpecial &&
                          header.value == 'schedule.shift_name'
                        "
                        outlined
                        dense
                        small
                        v-model="filters[header.key]"
                        item-text="name"
                        item-value="id"
                        :items="[{ name: `All Shifts`, id: `` }, ...shifts]"
                        placeholder="Shift"
                        solo
                        flat
                        @change="applyFilters(header.key, id)"
                      ></v-select>
                      <v-select
                        clearable
                        @click:clear="
                          filters[header.value] = '';
                          applyFilters();
                        "
                        :id="header.key"
                        :hide-details="true"
                        v-if="
                          header.filterSpecial &&
                          header.value == 'timezone.name'
                        "
                        outlined
                        dense
                        small
                        v-model="filters[header.key]"
                        item-text="timezone_name"
                        item-value="timezone_id"
                        :items="[
                          {
                            name: `All Timezones`,
                            timezone_name: `All Timezones`,
                            timezone_id: '',
                            id: ``,
                          },
                          ...timezones,
                        ]"
                        placeholder="Timezone"
                        solo
                        flat
                        @change="applyFilters(header.key, id)"
                      ></v-select>
                    </v-container>
                  </td>
                </tr>
              </template>
              <template v-slot:item.employee_id="{ item }">
                <strong>{{ item.employee_id }} </strong><br /><span
                  style="font-size: 12px"
                  >{{ item.system_user_id }}</span
                >
              </template>

              <template
                v-slot:item.first_name="{ item, index }"
                style="width: 300px"
              >
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
                    <div>
                      {{
                        item.designation ? caps(item.designation.name) : "---"
                      }}
                    </div>
                  </v-col>
                </v-row>
              </template>

              <template v-slot:item.department.branch.id="{ item }">
                {{
                  caps(
                    item.department &&
                      item.department.branch &&
                      item.department.branch.branch_name
                  )
                }}
              </template>
              <template v-slot:item.department.name.id="{ item }">
                <strong>{{ caps(item.department.name) }}</strong>
                <div>{{ caps(item.sub_department.name) }}</div>
              </template>
              <template v-slot:item.phone_number="{ item }">
                {{ item.phone_number }}
              </template>
              <template v-slot:item.user.email="{ item }" style="width: 200px">
                {{ item.user.email }}
              </template>
              <!-- <template v-slot:item.schedule.shift_name="{ item }">
                {{
                  (item.schedule &&
                    item.schedule.shift &&
                    getCurrentShift(item.schedule)) ||
                  "---"
                }}
                <div
                  v-if="
                    item.schedule &&
                    item.schedule.shift &&
                    item.schedule.shift.working_hours
                  "
                >
                  Working Hours: {{ item.schedule.shift.working_hours }}
                </div>
              </template> -->
              <template v-slot:item.timezone.name="{ item }">
                {{ item.timezone ? item.timezone.timezone_name : "" }}
              </template>
              <template v-slot:item.options="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="120" dense>
                    <v-list-item @click="viewItem(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-eye </v-icon>
                        View
                      </v-list-item-title>
                    </v-list-item>
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
        </v-container>
      </div>
    </div>
    <Preloader v-else />
  </div>

  <NoAccess v-else />
</template>

<script>
import EmployeeEdit from "../../components/employee/EmployeeEdit.vue";
import Contact from "../../components/employee/Contact.vue";
import Passport from "../../components/employee/Passport.vue";
import Emirates from "../../components/employee/Emirates.vue";
import Visa from "../../components/employee/Visa.vue";
import Bank from "../../components/employee/Bank.vue";
import Document from "../../components/employee/Document.vue";
import Qualification from "../../components/employee/Qualification.vue";
import Setting from "../../components/employee/Setting.vue";
import Payroll from "../../components/employee/Payroll.vue";
import Login from "../../components/employee/Login.vue";

import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
const compList = [
  EmployeeEdit,
  Contact,
  Passport,
  Emirates,
  Visa,
  Bank,
  Document,
  Qualification,
  Setting,
  Payroll,
  Login,
];

export default {
  components: {
    VueCropper,
  },

  data: () => ({
    departments: [],
    shifts: [],
    timezones: [],
    joiningDate: null,
    joiningDateMenuOpen: false,
    totalRowsCount: 0,
    showFilters: false,
    filters: {},
    isFilter: false,
    sortBy: "employee_id",
    sortDesc: false,
    server_datatable_totalItems: 1000,
    snack: false,
    snackColor: "",
    snackText: "",
    selectedItems: [],
    datatable_search_textbox: "",
    datatable_searchById: "",
    loadinglinear: true,
    displayErrormsg: false,
    image: "",
    mime_type: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
    dialogCropping: false,
    compList,
    comp: "EmployeeEdit",
    tabMenu: [],
    tab: "0",
    employeeId: 0,
    employeeObject: {},
    attrs: [],
    dialog: false,
    editDialog: false,
    viewDialog: false,
    selectedFile: "",
    employeeDialog: false,
    m: false,
    expand: false,
    expand2: false,
    boilerplate: false,
    right: true,
    rightDrawer: false,
    drawer: true,
    tab: null,
    selectedItem: 1,
    on: "",
    files: "",
    search: "",
    loading: false,
    //total: 0,
    next_page_url: "",
    prev_page_url: "",
    current_page: 1,
    per_page: 1000,
    ListName: "",
    color: "background",
    response: "",
    snackbar: false,
    btnLoader: false,
    max_employee: 0,
    employee: {
      title: "Mr",
      display_name: "",
      employee_id: "",
      system_user_id: "",
    },
    upload: {
      name: "",
    },
    previewImage: null,
    payload: {},
    personalItem: {},
    contactItem: {},
    emirateItems: {},
    setting: {},

    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    options: {},
    Model: "Employee",
    endpoint: "employee",
    search: "",
    snackbar: false,
    ids: [],
    loading: false,
    //total: 0,
    headers: [],
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: [],
    departments: [],
    sub_departments: [],
    designations: [],
    roles: [],
    department_filter_id: "",
    dialogVisible: false,
    payloadOptions: {},
    headers_table: [
      {
        text: "Emp Id / Device Id",
        align: "left",
        sortable: true,
        key: "employee_id",
        value: "employee_id",
        filterable: true,
        width: "150px",
        filterSpecial: false,
      },
      {
        text: "Name",
        align: "left",
        sortable: true,
        key: "first_name",
        value: "first_name",
        width: "300px",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Branch",
        align: "left",
        sortable: true,
        key: "branch_id",
        value: "department.branch.id", //template name should be match for sorting sub table should be the same
        width: "200px",
        filterable: true,
        filterSpecial: true,
      },

      {
        text: "Department",
        align: "left",
        sortable: true,
        key: "department_name_id",
        value: "department.name.id", //template name should be match for sorting sub table should be the same
        width: "200px",
        filterable: true,
        filterSpecial: true,
      },

      {
        text: "Mobile",
        align: "left",
        sortable: true,
        key: "mobile",
        value: "phone_number", // search and sorting enable if value matches with template name
        width: "150px",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Email",
        align: "left",
        sortable: true,
        key: "email",
        value: "user.email",
        filterable: true,
        filterSpecial: false,
      },
      // {
      //   text: "Shift",
      //   align: "left",
      //   sortable: false,
      //   key: "shceduleshift_id", //sorting without . _
      //   value: "schedule.shift_name",
      //   filterable: true,
      //   filterSpecial: true,
      // },
      {
        text: "Timezone",
        align: "left",
        sortable: true,
        key: "timezone",
        value: "timezone.name",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Options",
        align: "left",
        sortable: false,
        key: "options",
        value: "options",
      },
    ],
    branchesList: [],
  }),

  async created() {
    this.loading = false;
    this.boilerplate = true;

    this.payloadOptions = {
      params: {
        per_page: 10,
        company_id: this.$auth.user.company_id,
      },
    };

    this.getDataFromApi();
    // this.getDepartments();
    //this.getShifts();
    //this.getTimezone();
    this.getbranchesList();
  },
  mounted() {
    //this.getDataFromApi();
    this.tabMenu = [
      {
        text: "Profile",
        icon: "mdi-account-box",
        value: "#0",
      },
      {
        text: "Contact",
        icon: "mdi-phone",
        value: "#1",
      },
      {
        text: "Passport",
        icon: "mdi-file-powerpoint-outline",
        value: "#2",
      },
      {
        text: "Emirates",
        icon: "mdi-city-variant",
        value: "#3",
      },
      {
        text: "Visa",
        icon: "mdi-file-document-multiple",
        value: "#4",
      },
      {
        text: "Bank",
        icon: "mdi-bank",
        value: "#5",
      },
      {
        text: "Documents",
        icon: "mdi-file",
        value: "#6",
      },
      {
        text: "Qualification",
        icon: "mdi-account-box",
        value: "#7",
      },
      {
        text: "Setting",
        icon: "mdi-phone",
        value: "#8",
      },
      {
        text: "Payroll",
        icon: "mdi-briefcase",
        value: "#9",
      },
      {
        text: "Login",
        icon: "mdi-lock",
        value: "#10",
      },
    ];
    this.headers = [
      // { text: "#" },
      { text: "E.ID" },
      { text: "Profile" },
      { text: "Name" },
      { text: "Email" },
      { text: "Timezone" },
      { text: "Dept" },
      { text: "Sub Dept" },
      { text: "Desgnation" },
      { text: "Role" },
      { text: "Mobile" },
      { text: "Shift" },
      { text: "Actions" },
    ];
  },
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    openNewPage() {
      this.employee = {};
      this.departments = [];
      this.employeeDialog = true;
    },
    filterDepartmentsByBranch(filterBranchId) {
      this.getDepartments(filterBranchId);
      this.getShifts(filterBranchId);
      this.getTimezone(filterBranchId);
    },
    getbranchesList() {
      this.payloadOptions = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };

      this.$axios.get(`branches_list`, this.payloadOptions).then(({ data }) => {
        this.branchesList = data;
        if (this.$auth.user.branch_id) {
          this.branch_id = this.$auth.user.branch_id;
        } else {
          // this.branchesList = [
          //   { branch_name: `All Branches`, id: `` },
          //   ,
          //   ...this.branchesList,
          // ];
          this.branch_id = "";
        }
      });
    },
    getCurrentShift(item) {
      // Define an array of day names
      const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
      const dayName = daysOfWeek[new Date().getDay()];
      const { shift_name } =
        item && item.roster.json.find((e) => e.day == dayName);

      return shift_name || "---";
    },
    closeViewDialog() {
      this.viewDialog = false;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

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
    closePopup() {
      //croppingimagestep5
      this.$refs.attachment_input.value = null;
      this.dialogCropping = false;
    },
    saveCroppedImageStep2() {
      this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

      this.image_name = this.cropedImage;
      this.previewImage = this.cropedImage;

      this.dialogCropping = false;
    },
    getComponent() {
      return this.compList[this.tab];
    },
    close() {
      this.dialog = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },
    json_to_csv(json) {
      let data = json.map((e) => ({
        first_name: e.first_name,
        last_name: e.last_name,
        branch_name: e.department.branch && e.department.branch.branch_name,
        email: e.user.email,
        phone_number: e.phone_number,
        whatsapp_number: e.whatsapp_number,
        phone_relative_number: e.phone_relative_number,
        whatsapp_relative_number: e.whatsapp_relative_number,
        employee_id: e.employee_id,
        joining_date: e.show_joining_date,
        department_code: e.department_id,
        designation_code: e.designation_id,
        department: e.department.name,
        sub_department: e.sub_department.name,
        designation: e.designation.name,
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
    importEmployee() {
      let payload = new FormData();
      payload.append("employees", this.files);
      payload.append("company_id", this.$auth?.user?.company?.id);
      let options = {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      };
      this.btnLoader = true;
      this.$axios
        .post("/employee/import", payload, options)
        .then(({ data }) => {
          this.btnLoader = false;
          if (!data.status) {
            this.errors = data.errors;
            payload.delete("employees");
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees imported successfully";
            this.getDataFromApi();
            this.close();
          }
        })
        .catch((e) => {
          if (e.toString().includes("Error: Network Error")) {
            this.errors = [
              "File is modified.Please cancel the current file and try again",
            ];
            this.btnLoader = false;
          }
        });
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    can_old(per) {
      return true;
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
    serachAll(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length <= 3) {
        return false;
      }

      let url = `${this.endpoint}/search/${e}`;
      //this.loading = true;
      this.loadinglinear = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      let options = {
        params: {
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage,
          company_id: this.$auth.user.company_id,
          // department_id: this.department_filter_id,
        },
      };

      this.$axios.get(`${url}?page = ${page}`, options).then(({ data }) => {
        if (data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.loading = false;
          return false;
        }

        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;

        this.data.length == 0
          ? (this.displayErrormsg = true)
          : (this.displayErrormsg = false);

        this.loadinglinear = false;
      });
    },
    getDataFromApi(url = this.endpoint) {
      //this.loading = true;
      this.loadinglinear = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      let sortedBy = sortBy ? sortBy[0] : "";
      let sortedDesc = sortDesc ? sortDesc[0] : "";
      let options = {
        params: {
          page: page,
          sortBy: sortedBy,
          sortDesc: sortedDesc,
          per_page: itemsPerPage, //this.pagination.per_page,
          company_id: this.$auth.user.company_id,
          department_id: this.department_filter_id,
          department_ids: this.$auth.user.assignedDepartments,
          ...this.filters,
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        //this.server_datatable_totalItems = data.total;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;

        this.totalRowsCount = data.total;

        this.data.length == 0
          ? (this.displayErrormsg = true)
          : (this.displayErrormsg = false);

        this.loadinglinear = false;
      });
    },

    getDepartments(filterBranchId) {
      let options = {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
          department_ids: this.$auth.user.assignedDepartments,
          filter_branch_id: filterBranchId,
        },
      };
      this.$axios.get(`departments`, options).then(({ data }) => {
        this.departments = data.data;
        // this.departments.unshift({ name: "All Departments", id: "" });
      });
    },
    getShifts(filterBranchId) {
      let options = {
        per_page: 1000,
        company_id: this.$auth.user.company_id,
        filter_branch_id: filterBranchId,
      };
      this.$axios.get("shift", { params: options }).then(({ data }) => {
        this.shifts = data.data;
        //this.shifts.unshift({ name: "All Shifts", id: "" });
      });
    },
    getTimezone(filterBranchId) {
      let options = {
        per_page: 1000,
        company_id: this.$auth.user.company_id,
        filter_branch_id: filterBranchId,
      };
      this.$axios.get("timezone", { params: options }).then(({ data }) => {
        this.timezones = data.data;
        // this.timezones.unshift({ name: "All Timezones", id: "" });
        //this.timezones.unshift({ timezone_name: "24HOURS", id: "1", timezone_id: '1' });
      });
    },
    editItem(item) {
      this.employeeId = item.id;
      this.editDialog = true;
    },
    viewItem(item) {
      this.employeeId = item.id;

      this.employeeObject = item;
      this.viewDialog = true;
    },
    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(`${this.endpoint}/${item.id}`)
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
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    save() {
      let payload = {
        name: this.editedItem.name.toLowerCase(),
        company_id: this.$auth.user.company_id,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              const index = this.data.findIndex(
                (item) => item.id == this.editedItem.id
              );
              this.data.splice(index, 1, {
                id: this.editedItem.id,
                name: this.editedItem.name,
              });
              this.snackbar = data.status;
              this.response = data.message;
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
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      if (file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.errors["profile_picture"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          //croppedimage step6
          // this.previewImage = e.target.result;

          this.selectedFile = event.target.result;

          this.$refs.cropper.replace(this.selectedFile);
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);

        this.dialogCropping = true;
      }
    },
    mapper(obj) {
      let employee = new FormData();

      for (let x in obj) {
        employee.append(x, obj[x]);
      }
      employee.append("profile_picture", this.upload.name);
      employee.append("company_id", this.$auth.user.company_id);

      return employee;
    },
    store_data() {
      let final = Object.assign(this.employee);
      let employee = this.mapper(final);

      //croppedimageStep3
      if (this.$refs.attachment_input.files[0]) {
        this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

        this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
          // Create a FormData object and append the Blob as a file
          //const formData = new FormData();
          employee.append("profile_picture", blob, "cropped_image.jpg");
          employee.append("attachment_input", blob, "cropped_image.jpg");

          //croppedimagesptep4 //push to API in blob method only
          this.saveToAPI(employee);
        }, "image/jpeg");
      } else {
        this.saveToAPI(employee);
      }
    },
    saveToAPI(employee) {
      this.$axios
        .post("/employee-store", employee)
        .then(({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees inserted successfully";
            this.getDataFromApi();
            this.employeeDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
