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
                    @change="filterDepartmentsByBranch(employee.branch_id)"
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
    <v-dialog persistent v-model="editDialog" width="1400" :key="employeeId">
      <v-card>
        <v-tabs
          v-model="tab"
          class="popup_background_noviolet"
          centered
          icons-and-text
          color="violet"
        >
          <v-tabs-slider></v-tabs-slider>

          <v-tab v-for="(item, index) in tabMenu" :key="index">
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
            <v-tab-item v-for="(item, index) in tabMenu" :key="index">
              <component
                :is="getComponent(item.value)"
                :employeeId="employeeId"
                @close-popup="closePopup2"
                @eventFromChild="handleEventFromChild"
                v-if="tab == item.value"
                :currentItem="currentItem"
              />
            </v-tab-item>
          </v-tabs-items>
        </v-card-text>
      </v-card>
    </v-dialog>
    <div v-if="!loading">
      <div class="text-center">
        <!-- <v-dialog v-model="viewDialog" width="1400px" :key="employeeId">
          <EmployeeDetails2
            @close-parent-dialog="closeViewDialog"
            :employeeObject="employeeObject"
          />
        </v-dialog> -->
        <v-dialog v-model="viewDialog" width="80%" :key="employeeId">
          <v-card>
            <v-card-title dense>
              Employee Information
              <v-spacer></v-spacer>
              <v-icon @click="viewDialog = false" outlined dark color="black">
                mdi mdi-close-circle
              </v-icon>
            </v-card-title>
            <v-card-text>
              <EmployeeProfileView
                :table_id="employeeId"
                :employee_id="employee_id"
                :system_user_id="system_user_id"
              />
            </v-card-text>
          </v-card>
        </v-dialog>
      </div>
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
          <v-col>
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
            <div class="input-group" style="width: 100%">
              <WidgetsEmployeeDowloadDialog @response="getDataFromApi" />
              <input
                class="custom-input"
                type="text"
                placeholder="Search"
                @input="searchData"
                v-model="search"
              />
              <v-icon style="position: absolute; top: 16px; right: 107px"
                >mdi-magnify</v-icon
              >
              <v-btn
                style="margin-top: -6px"
                class="primary"
                small
                @click="openNewPage()"
                v-if="can('employee_create')"
                >+ New</v-btn
              >

              <v-menu offset-y :nudge-width="100">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    dark-2
                    icon
                    v-bind="attrs"
                    v-on="on"
                    style="margin-top: -9px"
                  >
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list dense>
                  <v-list-item>
                    <v-list-item-title
                      style="
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                      "
                    >
                      <div style="height: 17px; width: 17px">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 512 512"
                          class="icon align-text-top"
                        >
                          <path
                            fill="#6946dd"
                            d="M447.6 270.8c-8.8 0-15.9 7.1-15.9 15.9v142.7H80.4V286.8c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v158.6c0 8.8 7.1 15.9 15.9 15.9h383.1c8.8 0 15.9-7.1 15.9-15.9V286.8c0-8.8-7.1-16-15.9-16z"
                          ></path>
                          <path
                            fill="#6946dd"
                            d="M244.7 328.4c.4.4.8.7 1.2 1.1.2.1.4.3.5.4.2.2.5.4.7.5.2.1.4.3.7.4.2.1.4.3.7.4.2.1.5.2.7.3.2.1.5.2.7.3.2.1.5.2.7.3.3.1.5.2.8.3.2.1.5.1.7.2.3.1.5.1.8.2.3.1.6.1.8.1.2 0 .5.1.7.1.5.1 1 .1 1.6.1s1 0 1.6-.1c.2 0 .5-.1.7-.1.3 0 .6-.1.8-.1.3-.1.5-.1.8-.2.2-.1.5-.1.7-.2.3-.1.5-.2.8-.3.2-.1.5-.2.7-.3.2-.1.5-.2.7-.3.2-.1.5-.2.7-.3.2-.1.5-.3.7-.4.2-.1.4-.3.7-.4.3-.2.5-.4.7-.5.2-.1.4-.3.5-.4.4-.3.8-.7 1.2-1.1l95-95c6.2-6.2 6.2-16.3 0-22.5-6.2-6.2-16.3-6.2-22.5 0L272 278.7v-212c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v212l-67.8-67.8c-6.2-6.2-16.3-6.2-22.5 0-6.2 6.2-6.2 16.3 0 22.5l94.8 95z"
                          ></path>
                        </svg>
                      </div>

                      <div style="margin: 4px 0 0 5px">
                        <span style="font-size: 12px"
                          ><a
                            style="text-decoration: none; color: black"
                            href="/employees.csv"
                            download
                          >
                            Download Sample File</a
                          ></span
                        >
                      </div>
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item
                    @click="() => ((dialog = true), handleChangeEvent())"
                  >
                    <v-list-item-title
                      style="
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                      "
                    >
                      <div style="height: 17px; width: 17px">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 512 512"
                          class="icon align-text-top"
                        >
                          <path
                            fill="#6946dd"
                            d="M356 169.2c-3.1 3.1-7.2 4.7-11.3 4.7-4.1 0-8.2-1.6-11.3-4.7L272 107.8v205c0 8.8-7.2 16-16 16s-16-7.2-16-16v-205l-61.4 61.4c-6.2 6.2-16.4 6.2-22.6 0-6.2-6.2-6.2-16.4 0-22.6l88.7-88.7c6.2-6.2 16.4-6.2 22.6 0l88.7 88.7c6.3 6.2 6.3 16.3 0 22.6z"
                          ></path>
                          <path
                            fill="#6946dd"
                            d="M423 463.6H89c-44.9 0-81.4-39.8-81.4-88.7v-97.3c0-8.8 7.2-16 16-16s16 7.2 16 16v97.3c0 31.3 22.2 56.7 49.4 56.7h334c27.2 0 49.4-25.4 49.4-56.7v-98.5c0-8.8 7.2-16 16-16s16 7.2 16 16v98.5c0 48.9-36.5 88.7-81.4 88.7z"
                          ></path>
                        </svg>
                      </div>

                      <div style="margin: 4px 0 0 5px">
                        <span style="font-size: 12px"> Upload Employees</span>
                      </div>
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item @click="export_submit">
                    <v-list-item-title
                      style="
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                      "
                    >
                      <div style="height: 17px; width: 17px">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 512 512"
                          class="icon align-text-top"
                        >
                          <path
                            fill="#6946dd"
                            d="M447.6 270.8c-8.8 0-15.9 7.1-15.9 15.9v142.7H80.4V286.8c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v158.6c0 8.8 7.1 15.9 15.9 15.9h383.1c8.8 0 15.9-7.1 15.9-15.9V286.8c0-8.8-7.1-16-15.9-16z"
                          ></path>
                          <path
                            fill="#6946dd"
                            d="M244.7 328.4c.4.4.8.7 1.2 1.1.2.1.4.3.5.4.2.2.5.4.7.5.2.1.4.3.7.4.2.1.4.3.7.4.2.1.5.2.7.3.2.1.5.2.7.3.2.1.5.2.7.3.3.1.5.2.8.3.2.1.5.1.7.2.3.1.5.1.8.2.3.1.6.1.8.1.2 0 .5.1.7.1.5.1 1 .1 1.6.1s1 0 1.6-.1c.2 0 .5-.1.7-.1.3 0 .6-.1.8-.1.3-.1.5-.1.8-.2.2-.1.5-.1.7-.2.3-.1.5-.2.8-.3.2-.1.5-.2.7-.3.2-.1.5-.2.7-.3.2-.1.5-.2.7-.3.2-.1.5-.3.7-.4.2-.1.4-.3.7-.4.3-.2.5-.4.7-.5.2-.1.4-.3.5-.4.4-.3.8-.7 1.2-1.1l95-95c6.2-6.2 6.2-16.3 0-22.5-6.2-6.2-16.3-6.2-22.5 0L272 278.7v-212c0-8.8-7.1-15.9-15.9-15.9s-15.9 7.1-15.9 15.9v212l-67.8-67.8c-6.2-6.2-16.3-6.2-22.5 0-6.2 6.2-6.2 16.3 0 22.5l94.8 95z"
                          ></path>
                        </svg>
                      </div>

                      <div style="margin: 4px 0 0 5px">
                        <span style="font-size: 12px">Download Employees</span>
                      </div>
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
              <template v-slot:item.qrcode="{ item }">
                <v-icon
                  v-if="item.rfid_card_number && item.rfid_card_number != ''"
                  size="30"
                  color="black"
                  @click="viewDialogQrCode(item)"
                >
                  mdi-qrcode-scan</v-icon
                >
                <div v-else>No RFID</div>
              </template>
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
                    <v-list-item
                      v-if="can('employee_profile_view')"
                      @click="viewItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-eye </v-icon>
                        View
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="can('employee_edit')"
                      @click="editItem(item)"
                    >
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-pencil </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-title style="cursor: pointer">
                        <DeviceUser
                          iconColor="secondary"
                          label="Employee"
                          :key="generateRandomId()"
                          :system_user_id="item.system_user_id"
                        />
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
import Rfid from "../../components/employee/Rfid.vue";

import EmployeeProfileView from "../../components/EmployeesLogin/EmployeeLanding.vue";

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
    EmployeeProfileView,
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
    Rfid,
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
    comp: "EmployeeEdit",
    tabMenu: [
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
        text: "Passport",
        icon: "mdi-file-powerpoint-outline",
        value: 2,
      },
      {
        text: "Emirates",
        icon: "mdi-city-variant",
        value: 3,
      },
      {
        text: "Visa",
        icon: "mdi-file-document-multiple",
        value: 4,
      },
      {
        text: "Bank",
        icon: "mdi-bank",
        value: 5,
      },
      {
        text: "Document",
        icon: "mdi-file",
        value: 6,
      },
      {
        text: "Qualification",
        icon: "mdi-account-box",
        value: 7,
      },
      {
        text: "Setting",
        icon: "mdi-phone",
        value: 8,
      },
      {
        text: "Payroll",
        icon: "mdi-briefcase",
        value: 9,
      },
      {
        text: "Login",
        icon: "mdi-lock",
        value: 10,
      },
      {
        text: "RFID",
        icon: "mdi-lock",
        value: 11,
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
        width: "10%",
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
        text: "QR(RFID)",
        align: "left",
        sortable: true,
        key: "email",
        value: "qrcode",
        filterable: false,
        filterSpecial: false,
        width: "15%",
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
  },
  methods: {
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
            process.env.BACKEND_URL +
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
    generateRandomId() {
      const length = 8; // Adjust the length of the ID as needed
      const randomNumber = Math.floor(Math.random() * Math.pow(10, length)); // Generate a random number
      return randomNumber.toString().padStart(length, "0"); // Convert to string and pad with leading zeros if necessary
    },
    searchData() {
      //if (this.search.length == 0 || this.search.length >= 1) {
      this.getDataFromApi();
      //}
    },
    closePopup2() {
      this.editDialog = false;
      this.getDataFromApi();
    },
    async handleChangeEvent() {
      this.branchList = await this.$store.dispatch("fetchDropDowns", {
        key: "branchList",
        endpoint: "branch-list",
      });
    },
    getComponent(value) {
      const componentsList = {
        0: "EmployeeEdit",
        1: "Contact",
        2: "Passport",
        3: "Emirates",
        4: "Visa",
        5: "Bank",
        6: "Document",
        7: "Qualification",
        8: "Setting",
        9: "Payroll",
        10: "Login",
        11: "Rfid",
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
<style scoped>
.custom-input {
  padding: 6px 10px;
  height: 30px;
  position: relative;
  border-radius: 5px;
  border: 1px solid grey;
  font-size: 16px;
  transition: border-color 0.3s ease-in-out;
  outline: none;
}

.custom-input:focus {
  border-color: purple;
}
</style>
