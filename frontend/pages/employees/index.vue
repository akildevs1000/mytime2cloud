<template>
  <div v-if="can('employee_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
      <v-snackbar v-model="snack" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
    </div>
    <v-dialog v-model="printCardDialog" width="440" content-class="print-card-dialog">
      <v-card>
        <v-card-title dense class="popup_background no-print">
          Access Card
          <v-spacer></v-spacer>
          <v-icon @click="printCardDialog = false" outlined dark>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text class="text-center pa-4" style="background:#eceae3">
          <div class="d-flex justify-center" v-if="printCardItem">
            <div class="idc">
              <div class="header">
                <div class="company">{{ printCardCompany }}</div>
                <div class="company-tag">Employee Info</div>
              </div>

              <div class="portrait-wrap">
                <div class="portrait">
                  <img
                    v-if="printCardItem.profile_picture"
                    :src="printCardItem.profile_picture"
                    alt=""
                    class="portrait-img"
                  />
                  <svg
                    v-else
                    viewBox="0 0 160 160"
                    width="122"
                    height="122"
                    aria-hidden="true"
                  >
                    <rect width="160" height="160" fill="#e7ebf0" />
                    <circle cx="80" cy="68" r="28" fill="#8d95a1" />
                    <path
                      d="M0,160 C 0,116 38,92 80,92 C 122,92 160,116 160,160 Z"
                      fill="#8d95a1"
                    />
                    <path
                      d="M62,104 L80,122 L98,104 L98,160 L62,160 Z"
                      fill="#e7ebf0"
                    />
                  </svg>
                </div>
              </div>

              <div class="body">
                <div class="name">
                  {{ printCardItem.first_name }}
                  {{ printCardItem.last_name }}
                </div>
                <div class="phone">{{ printCardItem.phone_number || "—" }}</div>

                <div class="eid">
                  <div class="eid__label">Employee ID</div>
                  <div class="eid__num">
                    {{ printCardItem.system_user_id }}
                  </div>
                </div>
              </div>

              <div class="meta">
                <div class="row">
                  <span class="k">Join date</span>
                  <span class="v">{{ printCardJoiningDate }}</span>
                </div>
                <div class="row">
                  <span class="k">Dept</span>
                  <span class="v">{{
                    printCardItem.department && printCardItem.department.name
                      ? printCardItem.department.name
                      : "—"
                  }}</span>
                </div>
              </div>

              <div class="qr-wrap">
                <img v-if="printCardQr" :src="printCardQr" alt="QR" />
              </div>

              <div class="foot"><span>mytime2cloud.com</span></div>
            </div>
          </div>
        </v-card-text>
        <v-card-actions class="no-print">
          <v-spacer></v-spacer>
          <v-btn text @click="printCardDialog = false">Close</v-btn>
          <v-btn color="primary" @click="printAccessCard">
            <v-icon left small>mdi-printer</v-icon>
            Print
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="DialogQrCode" width="300">
      <v-card>
        <v-card-title dense class="popup_background">
          QR Code - Employee Id -
          {{ currentItem && currentItem.system_user_id }}
          <v-spacer></v-spacer>
          <v-icon @click="DialogQrCode = false" outlined dark>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text class="text-center">
          <img :src="qr_codeImage" :key="key" style="width: 100%" />

          <v-btn
            dense
            class="ma-0"
            x-small
            small
            color="primary"
            @click="
              downloadImage(
                qr_codeImage,
                currentItem.system_user_id + ' QR Code'
              )
            "
          >
            <v-icon class="mx-1 ml-2">mdi mdi-download</v-icon>
            Download
          </v-btn>
        </v-card-text>
      </v-card>
    </v-dialog>
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
        <v-toolbar dark dense class="primary" style="font-weight: 400">
          Add {{ Model }}
          <v-spacer></v-spacer>

          <v-icon
            outlined
            dark
            class="mr-5"
            :loading="loading"
            @click="store_data"
          >
            mdi-content-save-all
          </v-icon>
          <v-icon @click="employeeDialog = false" outlined dark>
            mdi-close-circle
          </v-icon>
        </v-toolbar>
        <v-card-text>
          <v-row>
            <v-col md="6" sm="12" cols="12" class="mt-5" dense>
              <v-row>
                <v-col md="6" sm="12" cols="12">
                  <!-- <label class="col-form-label"
                    >Title <span class="text-danger">*</span></label
                  > -->
                  <v-select
                    label="Title"
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
                  <!-- <label class="col-form-label"
                    >Joining Date <span class="text-danger">*</span></label
                  > -->
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
                          label="Joining Date"
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
                  <!-- <label class="col-form-label"
                    >Display Name <span class="text-danger">*</span></label
                  > -->
                  <v-text-field
                    label="Full Name"
                    dense
                    outlined
                    :hide-details="!errors.full_name"
                    type="text"
                    v-model="employee.full_name"
                    :error="errors.full_name"
                    :error-messages="
                      errors && errors.full_name ? errors.full_name[0] : ''
                    "
                  ></v-text-field>
                </v-col>

                <v-col md="12" sm="12" cols="12" dense>
                  <!-- <label class="col-form-label"
                    >Display Name <span class="text-danger">*</span></label
                  > -->
                  <v-text-field
                    label="Display Name"
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
                  <!-- <label class="col-form-label"
                    >First Name <span class="text-danger">*</span></label
                  > -->
                  <v-text-field
                    label="First Name"
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
                  <!-- <label class="col-form-label"
                    >Last Name <span class="text-danger">*</span></label
                  > -->
                  <v-text-field
                    label="Last Name"
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
                  <!-- <label class="col-form-label"
                    >Employee ID <span class="text-danger">*</span></label
                  > -->
                  <v-text-field
                    label="Employee ID"
                    dense
                    outlined
                    type="text"
                    v-model="employee.employee_id"
                    :hide-details="!errors.employee_id"
                    :error="errors.employee_id"
                    :error-messages="
                      errors && errors.employee_id ? errors.employee_id[0] : ''
                    "
                  ></v-text-field>
                </v-col>
                <v-col md="6" cols="6" sm="6" dense>
                  <!-- <label class="col-form-label"
                    >Employee Device Id<span class="text-danger">*</span></label
                  > -->
                  <v-text-field
                    label="Employee Device Id"
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
                  <!-- <label class="col-form-label"
                    >Mobile Number <span class="text-danger">*</span></label
                  > -->
                  <v-text-field
                    label="Mobile Numbers"
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
                  <!-- <label class="col-form-label"
                    >Whatsapp <span class="text-danger">*</span> ( ex:
                    971XXXX)</label
                  > -->
                  <v-text-field
                    label="Whatsapp ( ex:
                    971XXXX)"
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
                <v-col v-if="isCompany" cols="6">
                  <!-- <label class="col-form-label"
                    >Branch <span class="text-danger">*</span></label
                  > -->

                  <v-select
                    label="Branch"
                    v-model="employee.branch_id"
                    :items="branchList"
                    dense
                    placeholder="Select Branch"
                    outlined
                    item-value="id"
                    item-text="name"
                    :error="errors.branch_id"
                    :error-messages="
                      errors && errors.branch_id ? errors.branch_id[0] : ''
                    "
                  >
                  </v-select>
                </v-col>

                <v-col cols="6" v-if="$auth.user.user_type !== 'department'">
                  <v-autocomplete
                    label="Department"
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
                    border: 1px solid #6946dd;
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
          <!-- <pre>{{ employee }}</pre> -->
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
    <div v-if="!loading">
      <v-dialog
        persistent
        v-model="viewDialog"
        width="1200px"
        :key="employeeId"
      >
        <WidgetsClose left="1190" @click="viewDialog = false" />

        <v-card flat style="overflow: hidden">
          <v-row
            v-if="employeeObject && employeeObject.id"
            align="center"
            no-gutters
            class="pa-0 ma-0"
          >
            <v-col cols="12">
              <div dense flat dark class="white--text primary pa-2">
                Employee Profile
              </div>
            </v-col>
            <v-col cols="12">
              <div style="display: flex">
                <div
                  style="min-width: 30%; max-width: 30%"
                  class="d-flex flex-column gap-3"
                >
                  <v-container>
                    <v-card class="pa-4 text-center" flat>
                      <v-avatar size="120">
                        <img
                          :src="`${
                            employeeObject?.profile_picture ||
                            '/no-profile-image.jpg'
                          }`"
                          alt="Profile Image"
                        />
                      </v-avatar>
                      <v-list dense class="mt-3">
                        <v-list-item>
                          <v-list-item-content>
                            <v-list-item-title class="font-weight-bold">
                              {{ employeeObject.title }}.
                              {{ employeeObject.full_name }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                              {{ employeeObject.user.email }}
                            </v-list-item-subtitle>
                            <v-list-item-subtitle>
                              Employee ID: {{ employeeObject.employee_id }}
                            </v-list-item-subtitle>
                          </v-list-item-content>
                        </v-list-item>
                      </v-list>
                      <v-divider></v-divider>
                    </v-card>
                    <v-card flat class="d-flex align-center">
                      <v-card-text class="mt-5 pt-0">
                        <table
                          style="width: 100%"
                          dense
                          flat
                          class="my-simple-table"
                        >
                          <tbody>
                            <tr>
                              <td style="font-size: 10px">Status</td>
                              <td>
                                <img
                                  :src="`/${
                                    employeeObject.status ? 'on1' : 'off1'
                                  }.png`"
                                  style="height: 15px"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="font-size: 10px">Web Access</td>
                              <td>
                                <img
                                  :src="`/${
                                    employeeObject.user.mobile_app_login_access
                                      ? 'on1'
                                      : 'off1'
                                  }.png`"
                                  style="height: 15px"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="font-size: 10px">Mobile Access</td>
                              <td>
                                <img
                                  :src="`/${
                                    employeeObject.user.web_login_access
                                      ? 'on1'
                                      : 'off1'
                                  }.png`"
                                  style="height: 15px"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="font-size: 10px">Whatsapp OTP</td>
                              <td>
                                <img
                                  :src="`/${
                                    employeeObject.user.enable_whatsapp_otp
                                      ? 'on1'
                                      : 'off1'
                                  }.png`"
                                  style="height: 15px"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="font-size: 10px">Location Tracking</td>
                              <td>
                                <img
                                  :src="`/${
                                    employeeObject.user.tracking_status
                                      ? 'on1'
                                      : 'off1'
                                  }.png`"
                                  style="height: 15px"
                                />
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </v-card-text>
                    </v-card>
                  </v-container>
                </div>
                <div
                  style="
                    min-width: 5%;
                    max-width: 5%;
                    background-color: #ecf0f4 !important;
                  "
                  class="d-flex justify-center align-center"
                >
                  <v-tabs
                    vertical
                    v-model="tab"
                    class="popup_background_noviolet"
                    color="violet"
                  >
                    <v-tab
                      style="margin-left: -15px"
                      dense
                      v-for="(item, index) in viewTabMenu"
                      :key="index"
                      class="d-flex justify-center align-center"
                    >
                      <v-icon size="20">{{ item.icon }}</v-icon>
                    </v-tab>
                  </v-tabs>
                </div>
                <div
                  style="min-width: 65%; max-width: 65%"
                  class="d-flex flex-column gap-3"
                >
                  <v-container>
                    <v-tabs-items v-model="tab">
                      <v-tab-item
                        v-for="(item, index) in viewTabMenu"
                        :key="index"
                      >
                        <component
                          :is="getViewComponents(item.value)"
                          :employeeId="employeeId"
                          @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild"
                          v-if="tab == item.value"
                          :employeeObject="employeeObject"
                        />
                      </v-tab-item>
                    </v-tabs-items>
                  </v-container>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card>
      </v-dialog>
      <v-dialog persistent v-model="dialog" max-width="500px">
        <v-card>
          <v-card-title dense class="popup_background">
            Import Employee
            <v-spacer></v-spacer>
            <v-icon @click="dialog = false" outlined dark>
              mdi mdi-close-circle
            </v-icon>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col>
                  <v-select
                    label="Select Branch"
                    :hide-details="true"
                    clearable
                    item-value="id"
                    item-text="name"
                    v-model="import_branch_id"
                    outlined
                    dense
                    :items="branchList"
                  ></v-select>
                </v-col>
                <v-col cols="12">
                  <v-file-input
                    outlined
                    accept="text/csv"
                    v-model="files"
                    placeholder="Upload your file"
                    label="File"
                    prepend-icon=""
                    append-icon="mdi-upload"
                    dense
                  >
                    <template v-slot:selection="{ text }">
                      <v-chip v-if="text" small label color="primary">
                        {{ text }}
                      </v-chip>
                    </template>
                  </v-file-input>
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

      <v-card>
        <v-row>
          <v-col cols="8">
            <b class="ml-5" style="font-size: 18px; font-weight: 600"
              >Employees</b
            >
            <span>
              <v-btn
                dense
                class="ma-0 px-0"
                x-small
                :ripple="false"
                text
                title="Filter"
              >
                <v-icon @click="getDataFromApi()" class="mx-1 ml-2"
                  >mdi mdi-reload</v-icon
                >
              </v-btn>
            </span>
          </v-col>
          <v-col class="text-right">
            <div class="d-flex align-center" style="gap: 10px; width: 100%">
              <!-- Branch Dropdown -->
              <v-autocomplete
                class="custom-text-field-height"
                label="Branch"
                @change="getDataFromApi()"
                v-model="filters.branch_id"
                :items="[{ id: null, name: 'Select All' }, ...branchList]"
                dense
                placeholder="Select Branch"
                outlined
                item-value="id"
                item-text="name"
                hide-details
              ></v-autocomplete>

              <v-text-field
                class="custom-text-field-height"
                append-icon="mdi-magnify"
                label="Search"
                v-model="search"
                dense
                outlined
                hide-details
              ></v-text-field>

              <!-- New Button -->
              <v-btn
                small
                class="primary"
                @click="openNewPage()"
                v-if="can('employee_create')"
                >+ New</v-btn
              >

              <!-- Options Menu -->
              <v-menu offset-y>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon v-bind="attrs" v-on="on">
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>

                <v-list dense>
                  <!-- Download Sample -->
                  <v-list-item>
                    <v-list-item-title
                      class="d-flex align-center"
                      style="cursor: pointer"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512"
                        style="height: 17px; width: 17px; fill: #6946dd"
                      >
                        <path
                          d="M447.6 270.8c-8.8 0-15.9 7.1-15.9 15.9v142.7H80.4V286.8c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v158.6c0 8.8 7.1 15.9 15.9 15.9h383.1c8.8 0 15.9-7.1 15.9-15.9V286.8c0-8.8-7.1-16-15.9-16z"
                        />
                        <path
                          d="M244.7 328.4c.4.4.8.7 1.2 1.1l95-95c6.2-6.2 6.2-16.3 0-22.5-6.2-6.2-16.3-6.2-22.5 0L272 278.7v-212c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v212l-67.8-67.8c-6.2-6.2-16.3-6.2-22.5 0-6.2 6.2-6.2 16.3 0 22.5l94.8 95z"
                        />
                      </svg>
                      <span style="margin-left: 5px; font-size: 12px">
                        <a
                          href="/employees.csv"
                          style="text-decoration: none; color: black"
                          download
                          >Download Sample File</a
                        >
                      </span>
                    </v-list-item-title>
                  </v-list-item>

                  <!-- Upload Employees -->
                  <v-list-item
                    @click="() => ((dialog = true), handleChangeEvent())"
                  >
                    <v-list-item-title
                      class="d-flex align-center"
                      style="cursor: pointer"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512"
                        style="height: 17px; width: 17px; fill: #6946dd"
                      >
                        <path
                          d="M356 169.2c-3.1 3.1-7.2 4.7-11.3 4.7-4.1 0-8.2-1.6-11.3-4.7L272 107.8v205c0 8.8-7.2 16-16 16s-16-7.2-16-16v-205l-61.4 61.4c-6.2 6.2-16.4 6.2-22.6 0-6.2-6.2-6.2-16.4 0-22.6l88.7-88.7c6.2-6.2 16.4-6.2 22.6 0l88.7 88.7c6.3 6.2 6.3 16.3 0 22.6z"
                        />
                        <path
                          d="M423 463.6H89c-44.9 0-81.4-39.8-81.4-88.7v-97.3c0-8.8 7.2-16 16-16s16 7.2 16 16v97.3c0 31.3 22.2 56.7 49.4 56.7h334c27.2 0 49.4-25.4 49.4-56.7v-98.5c0-8.8 7.2-16 16-16s16 7.2 16 16v98.5c0 48.9-36.5 88.7-81.4 88.7z"
                        />
                      </svg>
                      <span style="margin-left: 5px; font-size: 12px"
                        >Upload Employees</span
                      >
                    </v-list-item-title>
                  </v-list-item>

                  <!-- Export Employees -->
                  <v-list-item @click="export_submit">
                    <v-list-item-title
                      class="d-flex align-center"
                      style="cursor: pointer"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512"
                        style="height: 17px; width: 17px; fill: #6946dd"
                      >
                        <path
                          d="M447.6 270.8c-8.8 0-15.9 7.1-15.9 15.9v142.7H80.4V286.8c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v158.6c0 8.8 7.1 15.9 15.9 15.9h383.1c8.8 0 15.9-7.1 15.9-15.9V286.8c0-8.8-7.1-16-15.9-16z"
                        />
                        <path
                          d="M244.7 328.4c.4.4.8.7 1.2 1.1l95-95c6.2-6.2 6.2-16.3 0-22.5-6.2-6.2-16.3-6.2-22.5 0L272 278.7v-212c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v212l-67.8-67.8c-6.2-6.2-16.3-6.2-22.5 0-6.2 6.2-6.2 16.3 0 22.5l94.8 95z"
                        />
                      </svg>
                      <span style="margin-left: 5px; font-size: 12px"
                        >Download Employees</span
                      >
                    </v-list-item-title>
                  </v-list-item>

                  <!-- Custom Widget -->
                  <v-list-item>
                    <v-list-item-title>
                      <WidgetsEmployeeDowloadDialog
                        @response="getDataFromApi"
                      />
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </div>
          </v-col>

          <v-col cols="12">
            <v-data-table
              elevation="0"
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
              :server-items-length="totalRowsCount"
            >
              <template v-slot:item.employee_id="{ item }">
                <div style="font-size: 13px">{{ item.employee_id }}</div>
                <small style="font-size: 11px">{{ item.system_user_id }}</small>
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
                    <v-avatar>
                      <v-img
                        :src="
                          item.profile_picture
                            ? item.profile_picture
                            : '/no-profile-image.jpg'
                        "
                      >
                      </v-img>
                    </v-avatar>
                  </v-col>
                  <v-col style="padding: 10px">
                    <div style="font-size: 13px">
                      {{ item.first_name ? item.first_name : "" }}
                      {{ item.last_name ? item.last_name : "" }}
                    </div>
                    <small style="font-size: 12px; color: #6c7184"
                      >{{ item.designation.name }}
                    </small>
                  </v-col>
                </v-row>
              </template>

              <template v-slot:item.branch.branch_name="{ item }">
                <div style="font-size: 13px">
                  {{ caps(item.branch && item.branch.branch_name) }}
                </div>
                <small style="font-size: 11px">
                  {{ item.user.branch_login && "(Branch Owner)" }}</small
                >
              </template>
              <template v-slot:item.department_name_id="{ item }">
                <div style="font-size: 13px">
                  {{ caps(item.department.name) }}
                </div>
                <small style="font-size: 11px">{{
                  caps(item.sub_department.name)
                }}</small>
              </template>

              <template v-slot:item.phone_number="{ item }">
                <div style="font-size: 13px">
                  {{ item.phone_number }}
                </div>
                <div style="font-size: 11px">
                  {{ item.local_email }}
                </div>
              </template>
              <!-- <template v-slot:item.qrcode="{ item }">
                <v-icon
                  v-if="item.rfid_card_number && item.rfid_card_number != ''"
                  size="30"
                  color="black"
                  @click="viewDialogQrCode(item)"
                >
                  mdi-qrcode-scan</v-icon
                >
                <div v-else>No RFID</div>
              </template> -->
              <template v-slot:item.timezone.name="{ item }">
                {{ item.timezone ? item.timezone.timezone_name : "" }}
              </template>
              <template v-slot:item.access="{ item }">
                <span
                  v-for="(icon, index) in getRelatedIcons(item)"
                  :key="index"
                >
                  <v-avatar
                    class="mx-1"
                    tile
                    size="20"
                    v-if="icon.type == 'image'"
                    ><img style="width: 100%" :src="icon.name"
                  /></v-avatar>
                  <v-icon v-else small color="black" class="mr-1">{{
                    icon.name
                  }}</v-icon>
                </span>
              </template>
              <template v-slot:item.print_card="{ item }">
                <v-btn
                  icon
                  title="Print Card"
                  @click="openPrintCard(item)"
                >
                  <v-icon  color="primary">mdi-printer</v-icon>
                </v-btn>
              </template>
              <template v-slot:item.options="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list dense>
                    <v-list-item
                      v-if="can('employee_profile_view')"
                      @click="viewItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-eye </v-icon>
                        View/Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('employee_edit')">
                      <v-list-item-title style="cursor: pointer">
                        <WidgetsEmployeeDowloadDialogSingle
                          :key="item.id"
                          :id="item.id"
                          :system_user_id="item.system_user_id"
                          @response="getDataFromApi"
                        />
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item v-if="can('employee_edit')">
                      <v-list-item-title style="cursor: pointer">
                        <DeviceUser
                          iconColor="secondary"
                          label="Employee"
                          :key="generateRandomId()"
                          :system_user_id="item.system_user_id"
                        />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="openPrintCard(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-printer </v-icon>
                        Print Card
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="can('employee_delete')"
                      @click="deleteItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="error" small> mdi-delete </v-icon>
                        Delete
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card>
    </div>
    <Preloader v-else />
  </div>

  <NoAccess v-else />
</template>

<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
import { getQrCode } from "@/utils/cardqrercode.js"; // Adjust the path as needed

export default {
  head() {
    return {
      link: [
        {
          rel: "stylesheet",
          href: "~/assets/source-sans-pro.css", // Adjust the path if needed
        },
      ],
    };
  },
  components: {
    VueCropper,
  },

  data: () => ({
    totalRowsCount: 0,
    refresh: true,
    id: "",
    employee_id: "",
    system_user_id: "",
    shifts: [],
    timezones: [],
    joiningDate: null,
    joiningDateMenuOpen: false,
    showFilters: false,
    filters: {},
    isFilter: false,
    sortBy: "employee_id",
    sortDesc: false,
    server_datatable_totalItems: 1000,
    snack: false,
    snackColor: "",
    DialogQrCode: false,
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

    viewTabMenu: [
      {
        text: "Profile",
        icon: "mdi-account-box",
        value: 0,
      },
      {
        text: "Contact",
        icon: "mdi-phone",
        value: 1,
      },
      {
        text: "Emirates",
        icon: "mdi-city-variant",
        value: 2,
      },
      {
        text: "Bank",
        icon: "mdi-bank",
        value: 3,
      },
      {
        text: "Document",
        icon: "mdi-file",
        value: 4,
      },
      {
        text: "Qualification",
        icon: "mdi-school",
        value: 5,
      },
      {
        text: "Leaves",
        icon: "mdi-calendar",
        value: 6,
      },
      {
        text: "Settings",
        icon: "mdi-cog",
        value: 7,
      },
    ],
    tab: 0,
    employeeId: 0,
    currentItem: {},
    employee_id: 0,
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
    loading: false,
    //total: 0,
    per_page: 1000,
    color: "background",
    response: "",
    snackbar: false,
    btnLoader: false,
    max_employee: 0,
    qr_codeImage: null,
    printCardDialog: false,
    printCardItem: null,
    printCardQr: null,
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
    options: {},
    Model: "Employee",
    endpoint: "employee",
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
    key: 1,
    headers_table: [
      {
        text: "Name",
        align: "left",
        sortable: true,
        key: "first_name",
        value: "first_name",
        width: "15%",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Emp Id / Device Id",
        align: "left",
        sortable: true,
        key: "employee_id",
        value: "employee_id",
        filterable: true,
        width: "15%",
        filterSpecial: false,
      },
      {
        text: "Department",
        align: "left",
        sortable: true,
        key: "department_name_id",
        value: "department_name_id", //template name should be match for sorting sub table should be the same
        width: "15%",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Mobile",
        align: "left",
        sortable: true,
        key: "mobile",
        value: "phone_number", // search and sorting enable if value matches with template name
        width: "10%",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Timezone",
        align: "left",
        sortable: true,
        key: "timezone_id",
        value: "timezone.name",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Access",
        align: "left",
        sortable: true,
        key: "access",
        value: "access",
        filterable: true,
        filterSpecial: true,
      },
      // {
      //   text: "Print",
      //   align: "center",
      //   sortable: false,
      //   key: "print_card",
      //   value: "print_card",
      //   width: "60px",
      // },
      {
        text: "Options",
        align: "left",
        sortable: false,
        key: "options",
        value: "options",
      },
    ],
    branchList: [],
    branch_id: null,
    isCompany: true,
    import_branch_id: "",

    refresh: false,
    search: null,
    debounceTimeout:null
  }),

  async created() {
    this.loading = false;
    this.boilerplate = true;

    if (this.$auth.user.branch_id) {
      this.branch_id = this.$auth.user.branch_id;
      this.employee.branch_id = this.$auth.user.branch_id;
      this.isCompany = false;
      await this.getDepartments(null);
      return;
    }
    this.headers_table.splice(2, 0, {
      text: "Branch",
      align: "left",
      sortable: true,
      key: "branch_id",
      value: "branch.branch_name",
      filterable: true,
      width: "10%",
      filterSpecial: true,
    });

    if (!this.data) {
      this.refresh = true;
      await this.getDataFromApi();
    }
  },
  computed: {
    printCardJoiningDate() {
      const item = this.printCardItem;
      if (!item) return "—";
      const raw = item.joining_date || item.show_joining_date;
      if (!raw) return "—";
      const iso = String(raw).match(/^(\d{4})-(\d{2})-(\d{2})/);
      if (iso) return `${iso[3]}·${iso[2]}·${iso[1]}`;
      const d = new Date(raw);
      if (!isNaN(d.getTime())) {
        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yyyy = d.getFullYear();
        return `${dd}·${mm}·${yyyy}`;
      }
      return String(raw);
    },
    printCardCompany() {
      const c = this.$auth && this.$auth.user && this.$auth.user.company;
      return (c && (c.name || c.company_name)) || "Company Name";
    },
  },
  mounted() {
    //this.getDataFromApi();
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
    this.handleChangeEvent();
    this.getDepartments(null);

    this.getTimezone(null);
  },
  watch: {
    options: {
      async handler() {
        await this.getDataFromApi();
      },
      deep: true,
    },
    search() {
      this.debounceGetData();
    },
  },
  methods: {
     debounceGetData() {
      clearTimeout(this.debounceTimeout); // clear previous timer
      this.debounceTimeout = setTimeout(() => {
        this.getDataFromApi();
      }, 500); // 500ms delay
    },
    getRelatedIcons({
      profile_picture,
      rfid_card_number,
      rfid_card_password,
      finger_prints,
      palms,
    }) {
      let icons = [];

      let iconPath = "/icons/employee-access/";

      let colorMode = this.$vuetify.theme.dark ? "w" : "b"; // b = black, w = white

      if (profile_picture) {
        icons.push({
          type: "image",
          name: `${iconPath}1-${colorMode}.png`,
        });
      }

      if (
        rfid_card_password != "" &&
        rfid_card_password != "FFFFFFFF" &&
        rfid_card_password != null
      ) {
        icons.push({
          type: "image",
          name: `${iconPath}2-${colorMode}.png`,
        });
      }
      if (
        rfid_card_number != "" &&
        rfid_card_number != "0" &&
        rfid_card_number != null
      ) {
        icons.push({
          type: "image",
          name: `${iconPath}3-${colorMode}.png`,
        });

        icons.push({
          name: "mdi-qrcode-scan",
        });
      }
      if (finger_prints.length) {
        icons.push({
          type: "image",
          name: `${iconPath}4-${colorMode}.png`,
        });
      }
      if (palms.length) {
        icons.push({
          type: "image",
          name: `${iconPath}5-${colorMode}.png`,
        });
      }
      return icons;
    },
    // getRelatedIcons(mode) {
    //   let iconPath = "/icons/employee-access/";
    //   let colorMode = this.$isDark() ? "w" : "b"; // b = black, w = white
    //   const icons = {
    //     Card: [iconPath + "3-" + colorMode + ".png"],
    //     Fing: [iconPath + "4-" + colorMode + ".png"],
    //     Face: [iconPath + "1-" + colorMode + ".png"],
    //     "Fing + Card": [
    //       iconPath + "1-" + colorMode + ".png",
    //       iconPath + "3-" + colorMode + ".png",
    //     ],
    //     "Face + Fing": [
    //       iconPath + "1-" + colorMode + ".png",
    //       iconPath + "4-" + colorMode + ".png",
    //     ],
    //     "Face + Card": [
    //       iconPath + "1-" + colorMode + ".png",
    //       iconPath + "3-" + colorMode + ".png",
    //     ],
    //     "Card + Pin": [
    //       iconPath + "3-" + colorMode + ".png",
    //       iconPath + "2-" + colorMode + ".png",
    //     ],
    //     "Face + Pin": [
    //       iconPath + "1-" + colorMode + ".png",
    //       iconPath + "2-" + colorMode + ".png",
    //     ],
    //     "Fing + Pin": [
    //       iconPath + "4-" + colorMode + ".png",
    //       iconPath + "2-" + colorMode + ".png",
    //     ],
    //     "Fing + Card + Pin": [
    //       iconPath + "4-" + colorMode + ".png",
    //       iconPath + "3-" + colorMode + ".png",
    //       iconPath + "2-" + colorMode + ".png",
    //     ],
    //     "Face + Card + Pin": [
    //       iconPath + "1-" + colorMode + ".png",
    //       iconPath + "3-" + colorMode + ".png",
    //       iconPath + "2-" + colorMode + ".png",
    //     ],
    //     "Face + Fing + Pin": [
    //       iconPath + "1-" + colorMode + ".png",
    //       iconPath + "4-" + colorMode + ".png",
    //       iconPath + "2-" + colorMode + ".png",
    //     ],
    //     "Face + Fing + Card": [
    //       iconPath + "1-" + colorMode + ".png",
    //       iconPath + "4-" + colorMode + ".png",
    //       iconPath + "3-" + colorMode + ".png",
    //     ],
    //     Manual: [], // assuming no icons for Manual
    //     Repeated: [], // assuming no icons for Repeated
    //   };

    //   return icons[mode] || [iconPath + "2-" + colorMode + ".png"];
    // },
    downloadImage(faceImage, userId) {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          face_image: faceImage,
          system_user_id: userId,
        },
      };
      this.$axios
        .post(`/download-profilepic-sdk`, options.params)
        .then(({ data }) => {
          this.downloadProfileLink =
            this.$backendUrl +
            "/download-profilepic-disk?image=" +
            data +
            "&name=" +
            userId;

          //this.$refs.goTo.click;

          let path = this.downloadProfileLink;
          let pdf = document.createElement("a");
          pdf.setAttribute("href", path);
          pdf.setAttribute("target", "_blank");
          pdf.click();
        })
        .catch((e) => console.log(e));
    },
    async viewDialogQrCode(item) {
      this.currentItem = item;
      this.key++;
      let year = new Date().getFullYear() + 1;
      const date = new Date(year + "-12-31 23:00:00");
      const cardNum = item.rfid_card_number;
      const cardType = 1; // Use 1 for numeric card numbers, 2 for Chinese User IDs

      let qrCodeResult = (
        await getQrCode(date, cardNum, cardType)
      ).toUpperCase();

      this.qr_codeImage = await this.$qrcode.generate(qrCodeResult, {
        width: 200,
        margin: 2,
        color: {
          dark: "#000000", // Black dots
          light: "#FFFFFF", // White background
        },
      });

      this.DialogQrCode = true;
    },
    async openPrintCard(item) {
      this.printCardItem = item;
      this.printCardQr = null;
      this.printCardDialog = true;
      try {
        const value = String(item.system_user_id || item.employee_id || "");
        this.printCardQr = await this.$qrcode.generate(value, {
          width: 200,
          margin: 0,
          color: { dark: "#0f1114", light: "#FFFFFF" },
        });
      } catch (e) {
        console.log(e);
      }
    },
    printAccessCard() {
      const item = this.printCardItem;
      if (!item) return;
      const esc = (v) =>
        String(v == null ? "" : v)
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#39;");

      const photo = item.profile_picture || "";
      const name = `${item.first_name || ""} ${item.last_name || ""}`.trim();
      const phone = item.phone_number || "—";
      const empId = item.system_user_id || "";
      const dept =
        item.department && item.department.name ? item.department.name : "—";
      const doj = this.printCardJoiningDate;
      const qr = this.printCardQr || "";
      const company = this.printCardCompany;

      const portraitInner = photo
        ? `<img src="${esc(photo)}" alt="" class="portrait-img" />`
        : `<svg viewBox="0 0 160 160" width="122" height="122" aria-hidden="true">
             <rect width="160" height="160" fill="#e7ebf0"/>
             <circle cx="80" cy="68" r="28" fill="#8d95a1"/>
             <path d="M0,160 C 0,116 38,92 80,92 C 122,92 160,116 160,160 Z" fill="#8d95a1"/>
             <path d="M62,104 L80,122 L98,104 L98,160 L62,160 Z" fill="#e7ebf0"/>
           </svg>`;

      const html = `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Employee ID card - ${esc(empId)}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#0f1114;
    --ink-2:#3a3f46;
    --ink-3:#6c727b;
    --rule:#e4e6ea;
    --card:#ffffff;
    --tint:#f4f6f9;
    --brand:#0f4c5c;
    --brand-2:#166978;
    --brand-3:#1f8294;
    --brand-ink:#ffffff;
    --font-sans:'Inter Tight','SF Pro Text',-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;
    --font-mono:'JetBrains Mono','SF Mono',ui-monospace,Menlo,monospace;
  }
  *{ box-sizing:border-box; }
  @page { size: A4; margin: 12mm; }
  html, body { margin:0; padding:0; background:#fff; }
  body {
    font-family:var(--font-sans); color:var(--ink);
    -webkit-print-color-adjust:exact; print-color-adjust:exact;
    -webkit-font-smoothing:antialiased;
    display:flex; align-items:center; justify-content:center;
  }
  .idc{
    width: 110mm; height: 194mm;
    background: var(--card); position: relative; overflow: hidden;
    border-radius: 5mm;
    box-shadow: 0 0 0 1px rgba(15,17,20,0.08);
    letter-spacing: -0.006em;
  }
  .header{
    position: relative; height: 52mm;
    background: var(--brand); color: var(--brand-ink); overflow: hidden;
  }
  .header::before, .header::after{
    content:""; position:absolute; inset:0;
    background: var(--brand-2);
    clip-path: polygon(0 0, 55% 0, 100% 55%, 100% 100%, 45% 100%, 0 45%);
    opacity: 0.55;
  }
  .header::after{
    background: var(--brand-3);
    clip-path: polygon(0 0, 35% 0, 100% 70%, 100% 100%, 65% 100%, 0 30%);
    opacity: 0.35;
  }
  .company{
    position:absolute; top:11mm; left:0; right:0;
    text-align:center; font-size: 18pt; font-weight:700;
    letter-spacing: -0.02em; z-index:2;
  }
  .company-tag{
    position:absolute; top:21mm; left:0; right:0;
    text-align:center; font-family: var(--font-mono);
    font-size: 8pt; letter-spacing: 0.18em; text-transform: uppercase;
    color: rgba(255,255,255,0.78); z-index:2;
  }
  .portrait-wrap{
    position:absolute; left:50%; top:28mm; transform:translateX(-50%);
    width: 40mm; height: 40mm; border-radius: 50%;
    padding: 1.6mm; background:#fff;
    box-shadow: 0 8px 22px -10px rgba(0,0,0,.35);
    z-index:3;
  }
  .portrait{
    width:100%; height:100%; border-radius:50%;
    background:#e7ebf0; overflow:hidden;
    display:flex; align-items:flex-end; justify-content:center;
  }
  .portrait-img{ width:100%; height:100%; object-fit:cover; display:block; }
  .body{ padding: 22mm 8mm 0; text-align:center; }
  .name{
    font-size: 18pt; font-weight:700; letter-spacing:-0.028em;
    line-height: 1.05; color: var(--ink);
  }
  .phone{
    margin-top: 2mm; font-family: var(--font-mono);
    font-size: 10pt; color: var(--ink-2); letter-spacing: 0.01em;
  }
  .eid{
    margin: 5mm auto 0; width: 56mm; border-radius: 3mm; overflow:hidden;
    box-shadow: 0 6px 14px -8px rgba(15,76,92,.6);
  }
  .eid__label{
    background: var(--brand); color: var(--brand-ink);
    font-size: 8.5pt; font-weight:600; letter-spacing: 0.14em;
    text-transform: uppercase; padding: 2mm 3mm;
  }
  .eid__num{
    background:#fff; color: var(--brand);
    font-weight:700; font-size: 22pt; letter-spacing:-0.01em;
    padding: 2.5mm 3mm 3mm; line-height:1;
    border:1.5px solid var(--brand); border-top:none;
    border-bottom-left-radius:3mm; border-bottom-right-radius:3mm;
    font-variant-numeric: tabular-nums;
  }
  .meta{
    margin-top: 5mm; padding: 0 7mm;
    display:flex; flex-direction:column; gap: 1.5mm; align-items:center;
  }
  .meta .row{ display:flex; justify-content:center; gap:2mm; }
  .meta .k{
    font-family:var(--font-mono); font-size:8pt;
    letter-spacing:0.1em; text-transform:uppercase; color: var(--ink-3);
  }
  .meta .v{
    font-family:var(--font-mono); font-size:8.5pt;
    color: var(--ink); font-weight:500;
  }
  .qr-wrap{
    margin: 5mm auto 6mm; width: 26mm; height: 26mm;
    background:#fff; border:1px solid var(--rule);
    border-radius: 2mm; padding: 1.5mm;
  }
  .qr-wrap img{ width:100%; height:100%; display:block; }
  .foot{
    position:absolute; left:0; right:0; bottom:0;
    height: 10mm; background: var(--brand); color: var(--brand-ink);
    display:flex; align-items:center; justify-content:center;
    font-family: var(--font-mono); font-size: 8pt;
    letter-spacing: 0.18em; text-transform: uppercase;
  }
  .foot::before{
    content:""; position:absolute; inset:0; background: var(--brand-2);
    clip-path: polygon(0 0, 22% 0, 14% 100%, 0 100%); opacity:.7;
  }
  .foot::after{
    content:""; position:absolute; inset:0; background: var(--brand-2);
    clip-path: polygon(78% 0, 100% 0, 100% 100%, 86% 100%); opacity:.7;
  }
  .foot span{ position:relative; z-index:1; }
</style>
</head>
<body>
  <div class="idc">
    <div class="header">
      <div class="company">${esc(company)}</div>
      <div class="company-tag">Employee Info</div>
    </div>

    <div class="portrait-wrap">
      <div class="portrait">${portraitInner}</div>
    </div>

    <div class="body">
      <div class="name">${esc(name)}</div>
      <div class="phone">${esc(phone)}</div>

      <div class="eid">
        <div class="eid__label">Employee ID</div>
        <div class="eid__num">${esc(empId)}</div>
      </div>
    </div>

    <div class="meta">
      <div class="row"><span class="k">Join date</span><span class="v">${esc(doj)}</span></div>
      <div class="row"><span class="k">Dept</span><span class="v">${esc(dept)}</span></div>
    </div>

    <div class="qr-wrap">${qr ? `<img src="${esc(qr)}" alt="QR" />` : ""}</div>

    <div class="foot"><span>mytime2cloud.com</span></div>
  </div>

  <script>
    (function () {
      var imgs = document.images;
      var pending = imgs.length;
      function go() { setTimeout(function () { window.focus(); window.print(); }, 80); }
      if (!pending) { go(); return; }
      var done = function () { if (--pending <= 0) go(); };
      for (var i = 0; i < imgs.length; i++) {
        if (imgs[i].complete) done();
        else { imgs[i].onload = done; imgs[i].onerror = done; }
      }
    })();
  <\/script>
</body>
</html>`;

      const w = window.open("", "_blank", "width=900,height=900");
      if (!w) {
        alert("Please allow popups to print the access card.");
        return;
      }
      w.document.open();
      w.document.write(html);
      w.document.close();
    },
    generateRandomId() {
      const length = 8; // Adjust the length of the ID as needed
      const randomNumber = Math.floor(Math.random() * Math.pow(10, length)); // Generate a random number
      return randomNumber.toString().padStart(length, "0"); // Convert to string and pad with leading zeros if necessary
    },
    closePopup2() {
      this.editDialog = false;
      this.viewDialog = false;
      this.getDataFromApi();
    },
    async handleChangeEvent() {
      this.branchList = await this.$store.dispatch("fetchDropDowns", {
        key: "branchList",
        endpoint: "branch-list",
      });
    },

    getViewComponents(value) {
      const componentsList = {
        0: "EmployeeViewProfile",
        1: "EmployeeViewContact",
        2: "EmployeeViewEmirates",
        3: "EmployeeViewBank",
        4: "EmployeeViewDocument",
        5: "EmployeeViewQualification",
        6: "EmployeeViewLeaveQuotta",
        7: "EmployeeViewSetting",
      };
      return componentsList[value] || "div"; // default to a div if no component found
    },
    async handleEventFromChild() {
      this.refresh = true;
      await this.getDataFromApi();
    },
    async openNewPage() {
      this.employee = {};
      this.departments = [];
      this.employeeDialog = true;

      if (this.$auth.user.user_type == "department") {
        this.isCompany = false;
        this.employee.department_id = this.$auth.user.department_id;
        this.employee.branch_id = this.$auth.user.branch_id;
        return;
      }

      if (this.$auth.user.branch_id) {
        await this.getDepartments(this.$auth.user.branch_id);
      } else {
        await this.getDepartments(null);
      }

      await this.handleChangeEvent();
    },
    async filterDepartmentsByBranch(filterBranchId) {
      this.isFilter = true;
      await this.getDepartments(filterBranchId);
      await this.getTimezone(filterBranchId);
    },
    async getDepartments(filterBranchId) {
      // if (filterBranchId > 0)
      {
        let options = {
          endpoint: "department-list",
          isFilter: this.isFilter,
          params: {
            company_id: this.$auth.user.company_id,
            branch_id: filterBranchId,
          },
        };
        this.departments = await this.$store.dispatch(
          "department_list",
          options
        );
      }
      // else {
      //   this.departments = [];
      // }
    },

    async getTimezone(filterBranchId) {
      let options = {
        endpoint: "timezone-list",
        isFilter: this.isFilter,
        params: {
          company_id: this.$auth.user.company_id,
          branch_id: filterBranchId,
        },
      };
      this.timezones = await this.$store.dispatch("timezone_list", options);
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
      if (this.import_branch_id > 0) {
        let payload = new FormData();
        payload.append("employees", this.files);
        payload.append("company_id", this.$auth?.user?.company?.id);
        payload.append("branch_id", this.import_branch_id);
        let options = {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        };
        this.btnLoader = true;
        this.$axios
          .post("/employee/import", payload, options)
          .then(async ({ data }) => {
            this.btnLoader = false;
            if (!data.status) {
              this.errors = data.errors;
              payload.delete("employees");
            } else {
              this.errors = [];
              this.snackbar = true;
              this.response = "Employees imported successfully";
              this.refresh = true;
              await this.getDataFromApi();
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
      } else {
        alert("Select Branch");
      }
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async toggleFilter() {
      // this.filters = {};
      this.isFilter = !this.isFilter;

      if (this.isFilter) {
        this.refresh = true;
        this.handleChangeEvent();
      }
    },
    async serachAll(e) {
      if ((e && e.length == 0) || e == null) {
        this.refresh = true;
        await this.getDataFromApi();
        return;
      } else if (e.length <= 3) {
        return false;
      }

      this.loadinglinear = true;

      let { sortBy, sortDesc, page, itemsPerPage } = this.options;

      this.$axios
        .get(`${this.endpoint}/search/${e}`, {
          params: {
            page,
            sortBy: sortBy ? sortBy[0] : "",
            sortDesc: sortDesc ? sortDesc[0] : "",
            per_page: itemsPerPage,
            company_id: this.$auth.user.company_id,
          },
        })
        .then(({ data }) => {
          this.data = data.data;
          this.totalRowsCount = data.total;
          this.loadinglinear = false;
        })
        .catch(({ err }) => {
          console.log(`err`);
          this.loadinglinear = false;
        });
    },
    async applyFilters(id) {
      this.refresh = true;
      await this.getDataFromApi();
      await this.getDepartments(id);
      await this.getTimezone(id);
    },
    async getDataFromApi() {
      this.loadinglinear = true;

      this.filters.search = this.search;

      console.log("🚀 ~ this.filters:", this.filters);

      const data = await this.$store.dispatch("fetchData", {
        key: "employees",
        options: this.options,
        refresh: this.refresh,
        endpoint: "employeev1",
        filters: this.filters,
      });

      this.data = data.data;
      this.totalRowsCount = data.total;
      this.loadinglinear = false;
    },

    editItem(item) {
      this.employeeId = item.id;
      this.currentItem = item;
      this.editDialog = true;
    },
    viewItem(item) {
      this.employeeId = item.id;

      this.system_user_id = item.system_user_id;
      this.employee_id = item.employee_id;

      this.employeeObject = item;
      this.viewDialog = true;
    },
    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(`${this.endpoint}/${item.id}`)
          .then(async ({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.refresh = true;
              await this.getDataFromApi();
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
          .then(async ({ data }) => {
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
              this.refresh = true;
              await this.getDataFromApi();
              this.close();
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, payload)
          .then(async ({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.refresh = true;
              await this.getDataFromApi();
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
        .then(async ({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = [];
            if (data.errors) this.errors = data.errors;
            else {
              this.snackbar = true;
              this.response = data.message;
            }
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Employees inserted successfully";
            this.refresh = true;
            await this.getDataFromApi();
            this.employeeDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>

<style>
.idc {
  --ink: #0f1114;
  --ink-2: #3a3f46;
  --ink-3: #6c727b;
  --rule: #e4e6ea;
  --card: #ffffff;
  --brand: #0f4c5c;
  --brand-2: #166978;
  --brand-3: #1f8294;
  --brand-ink: #ffffff;
  --font-sans: "Inter Tight", "SF Pro Text", -apple-system, BlinkMacSystemFont,
    "Segoe UI", Helvetica, Arial, sans-serif;
  --font-mono: "JetBrains Mono", "SF Mono", ui-monospace, Menlo, monospace;

  width: 340px;
  height: 600px;
  background: var(--card);
  position: relative;
  overflow: hidden;
  border-radius: 14px;
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.7) inset,
    0 0 0 1px rgba(15, 17, 20, 0.06),
    0 24px 60px -28px rgba(15, 17, 20, 0.35),
    0 6px 16px -6px rgba(15, 17, 20, 0.12);
  letter-spacing: -0.006em;
  font-family: var(--font-sans);
  color: var(--ink);
  text-align: left;
}
.idc * {
  box-sizing: border-box;
}

.idc .header {
  position: relative;
  height: 160px;
  background: var(--brand);
  color: var(--brand-ink);
  overflow: hidden;
}
.idc .header::before,
.idc .header::after {
  content: "";
  position: absolute;
  inset: 0;
  background: var(--brand-2);
  clip-path: polygon(0 0, 55% 0, 100% 55%, 100% 100%, 45% 100%, 0 45%);
  opacity: 0.55;
}
.idc .header::after {
  background: var(--brand-3);
  clip-path: polygon(0 0, 35% 0, 100% 70%, 100% 100%, 65% 100%, 0 30%);
  opacity: 0.35;
}
.idc .company {
  position: absolute;
  top: 34px;
  left: 0;
  right: 0;
  text-align: center;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: -0.02em;
  z-index: 2;
}
.idc .company-tag {
  position: absolute;
  top: 60px;
  left: 0;
  right: 0;
  text-align: center;
  font-family: var(--font-mono);
  font-size: 9.5px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.72);
  z-index: 2;
}
.idc .portrait-wrap {
  position: absolute;
  left: 50%;
  top: 86px;
  transform: translateX(-50%);
  width: 124px;
  height: 124px;
  border-radius: 50%;
  padding: 5px;
  background: #fff;
  box-shadow: 0 8px 22px -10px rgba(0, 0, 0, 0.35);
  z-index: 3;
}
.idc .portrait {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: #e7ebf0;
  overflow: hidden;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}
.idc .portrait-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.idc .body {
  padding: 68px 24px 0;
  text-align: center;
}
.idc .name {
  font-size: 24px;
  font-weight: 700;
  letter-spacing: -0.028em;
  line-height: 1.05;
  color: var(--ink);
}
.idc .phone {
  margin-top: 6px;
  font-family: var(--font-mono);
  font-size: 12px;
  color: var(--ink-2);
  letter-spacing: 0.01em;
}
.idc .eid {
  margin: 14px auto 0;
  width: 164px;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 6px 14px -8px rgba(15, 76, 92, 0.6);
}
.idc .eid__label {
  background: var(--brand);
  color: var(--brand-ink);
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  padding: 6px 10px;
}
.idc .eid__num {
  background: #fff;
  color: var(--brand);
  font-family: var(--font-sans);
  font-weight: 700;
  font-size: 28px;
  letter-spacing: -0.01em;
  padding: 8px 10px 10px;
  line-height: 1;
  border: 1.5px solid var(--brand);
  border-top: none;
  border-bottom-left-radius: 10px;
  border-bottom-right-radius: 10px;
  font-variant-numeric: tabular-nums;
}
.idc .meta {
  margin-top: 14px;
  padding: 0 22px;
  display: flex;
  flex-direction: column;
  gap: 5px;
  align-items: center;
}
.idc .meta .row {
  display: flex;
  justify-content: center;
  gap: 6px;
  font-size: 11px;
  color: var(--ink-2);
}
.idc .meta .k {
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--ink-3);
}
.idc .meta .v {
  font-family: var(--font-mono);
  font-size: 10.5px;
  color: var(--ink);
  font-weight: 500;
}
.idc .qr-wrap {
  margin: 14px auto 16px;
  width: 78px;
  height: 78px;
  background: #fff;
  border: 1px solid var(--rule);
  border-radius: 6px;
  padding: 4px;
}
.idc .qr-wrap img {
  width: 100%;
  height: 100%;
  display: block;
}
.idc .foot {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 32px;
  background: var(--brand);
  color: var(--brand-ink);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}
.idc .foot::before {
  content: "";
  position: absolute;
  inset: 0;
  background: var(--brand-2);
  clip-path: polygon(0 0, 22% 0, 14% 100%, 0 100%);
  opacity: 0.7;
}
.idc .foot::after {
  content: "";
  position: absolute;
  inset: 0;
  background: var(--brand-2);
  clip-path: polygon(78% 0, 100% 0, 100% 100%, 86% 100%);
  opacity: 0.7;
}
.idc .foot span {
  position: relative;
  z-index: 1;
}
</style>
