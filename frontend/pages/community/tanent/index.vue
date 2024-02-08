<template>
  <div v-if="can('employee_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="!loading">
      <v-dialog persistent v-model="viewMemberDialogBox" width="700">
        <v-toolbar flat dense>
          <b> Members </b>
          <v-spacer></v-spacer>
          <v-icon color="primary" @click="viewMemberDialogBox = false"
            >mdi-close-circle-outline</v-icon
          >
        </v-toolbar>
        <v-card>
          <v-container>
            <TanentMembers :members="payload.members" />
          </v-container>
        </v-card>
      </v-dialog>
      <v-dialog persistent v-model="memberDialogBox" width="800">
        <v-card>
          <v-toolbar dense class="popup_background" flat>
            {{ formAction }} Member

            <v-spacer></v-spacer>
            <!-- <span v-if="formAction !== 'View'">
              <v-icon
                class="ml-2 primary--text"
                color="primary"
                @click="addMemberItem"
              >
                mdi mdi-plus-circle</v-icon
              >
            </span> -->
          </v-toolbar>

          <v-container>
            <v-row no-gutters>
              <v-col cols="4" class="text-center pt-3">
                <v-avatar size="150" style="border: 1px solid #6946dd">
                  <v-img :src="imageMemberPreview"></v-img>
                </v-avatar>
              </v-col>
              <v-col cols="8" class="pt-2">
                <v-row>
                  <v-col cols="6">
                    <v-text-field
                      label="Full Name"
                      :readonly="disabled"
                      v-model="member.full_name"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.full_name"
                      :error-messages="
                        errors && errors.full_name ? errors.full_name[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6">
                    <v-text-field
                      label="Relation"
                      :readonly="disabled"
                      v-model="member.relation"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.relation"
                      :error-messages="
                        errors && errors.relation ? errors.relation[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6">
                    <v-text-field
                      label="Age"
                      :readonly="disabled"
                      v-model="member.age"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.age"
                      :error-messages="
                        errors && errors.age ? errors.age[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6">
                    <v-text-field
                      label="Phone Number (optional)"
                      :readonly="disabled"
                      v-model="member.phone_number"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.phone_number"
                      :error-messages="
                        errors && errors.phone_number
                          ? errors.phone_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6">
                    <v-file-input
                      v-if="!disabled"
                      v-model="member.profile_picture"
                      dense
                      outlined
                      prepend-icon=""
                      append-icon="mdi-camera"
                      label="Upload Photo"
                      @change="previewMemberImage"
                    ></v-file-input>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-container>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <div class="text-right">
              <v-btn
                small
                color="grey white--text"
                @click="memberDialogBox = false"
              >
                Close
              </v-btn>

              <v-btn
                v-if="can('employee_create') && formAction == 'Create'"
                small
                :loading="loading"
                color="primary"
                @click="submitMembers"
              >
                submit
              </v-btn>
            </div>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <v-dialog persistent v-model="DialogBox" width="900">
        <!-- <v-toolbar class="popup_background" flat>
            {{ formAction }} Tanent

            <v-spacer></v-spacer>
            <span>
              <v-icon class="ml-2" @click="DialogBox = false" dark>
                mdi mdi-close-circle</v-icon
              >
            </span>
          </v-toolbar> -->
        <v-stepper v-model="step" horizontal>
          <v-stepper-header>
            <v-stepper-step :complete="step > 1" step="1" editable>
              Basic Info
            </v-stepper-step>
            <v-divider></v-divider>
            <v-stepper-step :complete="step > 2" step="2" editable>
              Members
            </v-stepper-step>
            <v-divider></v-divider>
            <v-stepper-step :complete="step > 3" step="3" editable>
              Documentation
            </v-stepper-step>
          </v-stepper-header>

          <v-stepper-content step="1">
            <!-- Step 1 Content -->
            <v-row>
              <v-col cols="3">
                <div class="text-center">
                  <SnippetsUploadAttachment
                    :defaultImage="setImagePreview"
                    @uploaded="handleAttachment"
                  />

                  <span v-if="errors && errors.logo" class="text-danger mt-2">{{
                    errors.logo[0]
                  }}</span>
                </div>
              </v-col>
              <v-col cols="9">
                <v-row class="mt-4">
                  <v-col cols="6">
                    <v-autocomplete
                      @change="getRoomsByFloorId(payload.floor_id)"
                      label="Floor Number"
                      outlined
                      :readonly="disabled"
                      v-model="payload.floor_id"
                      :items="floors"
                      dense
                      item-text="floor_number"
                      item-value="id"
                      :hide-details="!errors.floor_id"
                      :error-messages="
                        errors && errors.floor_id ? errors.floor_id[0] : ''
                      "
                    >
                    </v-autocomplete>
                  </v-col>

                  <v-col cols="6">
                    <v-autocomplete
                      @change="getRoomNumber(payload.room_id)"
                      label="Room"
                      outlined
                      :readonly="disabled"
                      v-model="payload.room_id"
                      :items="rooms"
                      dense
                      item-text="room_number"
                      item-value="id"
                      :hide-details="!errors.room_id"
                      :error-messages="
                        errors && errors.room_id ? errors.room_id[0] : ''
                      "
                    >
                    </v-autocomplete>
                  </v-col>
                  <v-col cols="6">
                    <v-text-field
                      label="First Name"
                      :readonly="disabled"
                      v-model="payload.first_name"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.first_name"
                      :error-messages="
                        errors && errors.first_name ? errors.first_name[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="Last Name"
                      :readonly="disabled"
                      v-model="payload.last_name"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.last_name"
                      :error-messages="
                        errors && errors.last_name ? errors.last_name[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="Email"
                      :readonly="disabled"
                      v-model="payload.email"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.email"
                      :error-messages="
                        errors && errors.email ? errors.email[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-menu
                      v-model="menu3"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="payload.date_of_birth"
                          label="Date of Birth"
                          append-icon="mdi-calendar"
                          outlined
                          dense
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.date_of_birth"
                          :error-messages="
                            errors && errors.date_of_birth
                              ? errors.date_of_birth[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="payload.date_of_birth"
                        @input="menu3 = false"
                      ></v-date-picker>
                    </v-menu>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="Phone Number"
                      :readonly="disabled"
                      v-model="payload.phone_number"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.phone_number"
                      :error-messages="
                        errors && errors.phone_number
                          ? errors.phone_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6">
                    <v-text-field
                      label="Whatsapp Number (optional)"
                      :readonly="disabled"
                      v-model="payload.whatsapp_number"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.whatsapp_number"
                      :error-messages="
                        errors && errors.whatsapp_number
                          ? errors.whatsapp_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="Car Number"
                      :readonly="disabled"
                      v-model="payload.car_number"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.car_number"
                      :error-messages="
                        errors && errors.car_number ? errors.car_number[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="Parking Number"
                      :readonly="disabled"
                      v-model="payload.parking_number"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.parking_number"
                      :error-messages="
                        errors && errors.parking_number
                          ? errors.parking_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="RFID"
                      :readonly="disabled"
                      v-model="payload.rfid"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.rfid"
                      :error-messages="
                        errors && errors.rfid ? errors.rfid[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="PIN"
                      :readonly="disabled"
                      v-model="payload.pin"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.pin"
                      :error-messages="
                        errors && errors.pin ? errors.pin[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="Nationality"
                      :readonly="disabled"
                      v-model="payload.nationality"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.nationality"
                      :error-messages="
                        errors && errors.nationality
                          ? errors.nationality[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-text-field
                      label="Address"
                      :readonly="disabled"
                      v-model="payload.address"
                      dense
                      class="text-center"
                      outlined
                      :hide-details="!errors.address"
                      :error-messages="
                        errors && errors.address ? errors.address[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="6">
                    <v-menu
                      v-model="menu"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="payload.start_date"
                          label="Start Date"
                          append-icon="mdi-calendar"
                          outlined
                          dense
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.start_date"
                          :error-messages="
                            errors && errors.start_date
                              ? errors.start_date[0]
                              : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="payload.start_date"
                        @input="menu = false"
                      ></v-date-picker>
                    </v-menu>
                  </v-col>
                  <v-col cols="6">
                    <v-menu
                      v-model="menu2"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="payload.end_date"
                          label="End Date"
                          append-icon="mdi-calendar"
                          outlined
                          dense
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :hide-details="!errors.end_date"
                          :error-messages="
                            errors && errors.end_date ? errors.end_date[0] : ''
                          "
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="payload.end_date"
                        @input="menu2 = false"
                      ></v-date-picker>
                    </v-menu>
                  </v-col>

                  <v-col cols="12">
                    <v-switch
                      style="margin-top: 5px"
                      label="Web Access"
                      outlined
                      :readonly="disabled"
                      v-model="payload.web_access"
                      dense
                      :hide-details="!errors.web_access"
                      :error-messages="
                        errors && errors.web_access ? errors.web_access[0] : ''
                      "
                    >
                    </v-switch>
                  </v-col>

                  <v-col cols="6" class="my-1">
                    <v-btn
                      v-if="formAction == 'Edit'"
                      class="primary"
                      @click="update_data"
                      >Update</v-btn
                    >
                  </v-col>

                  <v-col cols="6" class="text-right my-1">
                    <v-btn @click="DialogBox = false">Close</v-btn>
                    <v-btn
                      v-if="formAction == 'Create'"
                      class="primary"
                      @click="tanentValidate"
                      >Next</v-btn
                    >
                    <v-btn
                      v-if="formAction == 'Edit'"
                      class="primary"
                      @click="tanentUpdateValidate"
                      >Next</v-btn
                    >
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-stepper-content>

          <v-stepper-content step="2">
            <!-- Step 1 Content -->
            <v-card
              outlined
              v-for="(member, index) in payload.members"
              :key="index"
              class="mb-2"
            >
              <v-container>
                <v-row>
                  <v-col cols="3">
                    <div class="text-center">
                      <SnippetsUploadAttachment
                        :defaultImage="member.profile_picture"
                        @uploaded="
                          (e) => {
                            member.profile_picture = e;
                          }
                        "
                      />

                      <span
                        v-if="errors && errors.logo"
                        class="text-danger mt-2"
                        >{{ errors.logo[0] }}</span
                      >
                    </div>
                  </v-col>
                  <v-col cols="9">
                    <v-row class="mt-1">
                      <v-col cols="6">
                        <v-text-field
                          label="Full Name"
                          :readonly="disabled"
                          v-model="member.full_name"
                          dense
                          class="text-center"
                          outlined
                          :hide-details="!errors.full_name"
                          :error-messages="
                            errors && errors.full_name
                              ? errors.full_name[0]
                              : ''
                          "
                        ></v-text-field>
                      </v-col>

                      <v-col cols="6">
                        <v-text-field
                          label="Relation"
                          :readonly="disabled"
                          v-model="member.relation"
                          dense
                          class="text-center"
                          outlined
                          :hide-details="!errors.relation"
                          :error-messages="
                            errors && errors.relation ? errors.relation[0] : ''
                          "
                        ></v-text-field>
                      </v-col>

                      <v-col cols="6">
                        <v-text-field
                          label="Age"
                          :readonly="disabled"
                          v-model="member.age"
                          dense
                          class="text-center"
                          outlined
                          :hide-details="!errors.age"
                          :error-messages="
                            errors && errors.age ? errors.age[0] : ''
                          "
                        ></v-text-field>
                      </v-col>

                      <v-col cols="6">
                        <v-text-field
                          label="Phone Number (optional)"
                          :readonly="disabled"
                          v-model="member.phone_number"
                          dense
                          class="text-center"
                          outlined
                          :hide-details="!errors.phone_number"
                          :error-messages="
                            errors && errors.phone_number
                              ? errors.phone_number[0]
                              : ''
                          "
                        ></v-text-field>
                      </v-col>

                      <v-col cols="12" class="text-right mt-12">
                        <v-btn
                          v-if="formAction == 'Edit'"
                          class="primary"
                          @click="update_member(member)"
                          >Update</v-btn
                        >
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-container>
            </v-card>
          </v-stepper-content>

          <v-stepper-content step="3">
            <!-- Step 2 Content -->
            <v-row>
              <v-col
                v-for="(document, index) in documents"
                :key="index"
                cols="6"
              >
                <SnippetsUploadDocument
                  :label="document.label"
                  @uploaded="updatePayload(document.key, $event)"
                />
              </v-col>

              <v-col cols="12" class="text-right my-1">
                <v-btn @click="DialogBox = false">close</v-btn>
                <v-btn
                  v-if="formAction == 'Create'"
                  class="primary"
                  @click="submit"
                  >Submit</v-btn
                >
                <v-btn
                  v-else-if="formAction == 'Edit'"
                  class="primary"
                  @click="update_data"
                  >Update</v-btn
                >
              </v-col>
            </v-row>
          </v-stepper-content>
        </v-stepper>
      </v-dialog>
      <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
      <div v-if="can(`employee_view`)">
        <v-container>
          <v-card elevation="0">
            <v-toolbar class="mb-2" dense flat>
              <v-toolbar-title
                ><span>{{ Model }}s </span></v-toolbar-title
              >
              <span>
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
              </span>
              <v-spacer></v-spacer>
              <!-- <span>
                <v-btn
                  dense
                  x-small
                  class="ma-0 px-0"
                  :ripple="false"
                  text
                  title="Add Tanent"
                  @click="addItem"
                >
                  <v-icon
                    right
                    size="x-large"
                    dark
                    v-if="can('employee_create')"
                    >mdi-plus-circle</v-icon
                  >
                </v-btn>
              </span> -->
              <TanentCreate @success="getDataFromApi"/>
            </v-toolbar>
            <v-data-table
              dense
              :headers="headers"
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
              <template v-slot:item.category="{ item }">
                {{ item?.room?.room_category?.name }}
              </template>
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
                    </v-container>
                  </td>
                </tr>
              </template>

              <template v-slot:item.members="{ item }">
                <v-icon color="primary" class="mx-1" @click="viewMember(item)">
                  mdi-eye
                </v-icon>
                {{ item.members.length }}
              </template>

              <template
                v-slot:item.full_name="{ item, index }"
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
                        height: 50px;
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
                    <strong> {{ item.full_name }}</strong>
                    <p>{{ item.phone_number }}</p>
                  </v-col>
                </v-row>
              </template>

              <template v-slot:item.options="{ item }">
                <v-menu bottom left>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dark-2 icon v-bind="attrs" v-on="on">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list width="150" dense>
                    <v-list-item @click="addMember(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="secondary" small> mdi-account </v-icon>
                        Add Member(s)
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-title style="cursor: pointer">
                        <TanentSingle :key="generateRandomId()" :item="item" />
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
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";

export default {
  components: {
    VueCropper,
  },

  data: () => ({
    originalURL: `https://mytime2cloud.com/register/visitor/`,
    fullCompanyLink: ``,
    encryptedID: "",
    fullLink: "",
    qrCodeDataURL: "",
    qrCompanyCodeDataURL: "",
    disabled: false,
    step: 1,

    members: [],

    payload: {
      full_name: "",
      phone_number: "",
      floor_id: "",
      room_id: "",
      start_date: "",
      end_date: "",
    },
    documents: [
      { label: "Passport", key: "passport_doc" },
      { label: "ID", key: "id_doc" },
      { label: "Contract", key: "contract_doc" },
      { label: "Ejari", key: "ejari_doc" },
      { label: "License", key: "license_doc" },
      { label: "Other", key: "others_doc" },
    ],
    imagePreview: "/no-profile-image.jpg",
    setImagePreview: null,
    imageMemberPreview: "/no-profile-image.jpg",

    tab: null,

    totalRowsCount: 0,
    filters: {},
    isFilter: false,
    sortBy: "id",
    sortDesc: false,
    snack: false,
    snackColor: "",
    snackText: "",
    loadinglinear: true,
    displayErrormsg: false,
    image: "",
    mime_type: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
    dialogCropping: false,
    tabMenu: [],
    tab: "0",
    employeeId: 0,
    employeeObject: {},
    attrs: [],
    dialog: false,
    editDialog: false,
    viewDialog: false,
    selectedFile: "",
    DialogBox: false,
    memberDialogBox: false,
    viewMemberDialogBox: false,
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

    pagination: {
      current: 1,
      total: 0,
      per_page: 10,
    },
    options: {},
    Model: "Tanent",
    endpoint: "tanent",
    search: "",
    snackbar: false,
    ids: [],
    loading: false,
    //total: 0,
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: [],
    designations: [],
    roles: [],
    employees: [],
    department_filter_id: "",
    dialogVisible: false,
    // "": "03:50:00",
    // "": "08:50:00",
    // "zone_id": 1,
    // "weekend": true,
    // "webaccess": true,
    headers: [
      {
        text: "User Device Id",
        align: "left",
        sortable: true,
        key: "system_user_id",
        value: "system_user_id",
        filterable: true,
        filterSpecial: false,
      },

      {
        text: "Full Name",
        align: "left",
        sortable: true,
        key: "full_name",
        value: "full_name",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Nationality",
        align: "left",
        sortable: true,
        key: "nationality",
        value: "nationality",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Category",
        align: "left",
        sortable: true,
        key: "category",
        value: "category",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Members",
        align: "left",
        sortable: true,
        key: "members",
        value: "members",
        filterable: true,
        filterSpecial: false,
      },

      // {
      //   text: "Phone No",
      //   align: "left",
      //   sortable: true,
      //   key: "phone_number",
      //   value: "phone_number",
      //   filterable: true,
      //   filterSpecial: false,
      // },

      {
        text: "Floor No",
        align: "left",
        sortable: true,
        key: "floor.floor_number",
        value: "floor.floor_number",
        filterable: true,
        filterSpecial: false,
      },

      {
        text: "Room No",
        align: "left",
        sortable: true,
        key: "room.room_number",
        value: "room.room_number",
        filterable: true,
        filterSpecial: false,
      },

      {
        text: "Start Date",
        align: "left",
        sortable: true,
        key: "start_date",
        value: "start_date",
        filterable: true,
        filterSpecial: false,
      },

      {
        text: "End Date",
        align: "left",
        sortable: true,
        key: "end_date",
        value: "end_date",
        filterable: true,
        filterSpecial: false,
      },

      {
        text: "Details",
        align: "left",
        sortable: false,
        key: "options",
        value: "options",
      },
    ],
    formAction: "Create",

    date: new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
      .toISOString()
      .substr(0, 10),
    menu: false,
    menu2: false,
    menu3: false,

    floors: [],
    rooms: [],
    member: {
      full_name: null,
      phone_number: null,
      age: null,
      relation: null,
      tanent_id: 0,
    },
  }),

  async created() {
    this.loading = false;
    this.boilerplate = true;

    this.getDataFromApi();
    await this.getFloors();
  },

  mounted() {},
  watch: {
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    updatePayload(key, document) {
      this.payload[key] = document;
    },
    generateRandomId() {
      const length = 8; // Adjust the length of the ID as needed
      const randomNumber = Math.floor(Math.random() * Math.pow(10, length)); // Generate a random number
      return randomNumber.toString().padStart(length, "0"); // Convert to string and pad with leading zeros if necessary
    },
    handleAttachment(e) {
      this.payload.profile_picture = e;
    },
    nextStep() {
      this.step++;
    },
    prevStep() {
      this.step--;
    },
    addMemberItem() {
      this.members.push({
        full_name: null,
        phone_number: null,
        age: null,
        relation: null,
        tanent_id: this.payload.id,
      });
    },
    removeMemberItem(index) {
      this.members.splice(index, 1);
    },
    getRoomNumber(room_id) {
      let { room_number } = this.rooms.find((e) => e.id == room_id);
      this.payload.room_number = room_number || 0;
    },
    async getFloors() {
      let { data: floors } = await this.$axios.get(`floor`, {
        params: { company_id: this.$auth.user.company_id },
      });
      this.floors = floors.data;
    },
    async getRoomsByFloorId(floor_id) {
      let { floor_number } = this.floors.find((e) => e.id == floor_id);
      this.payload.floor_number = floor_number || 0;

      let { data } = await this.$axios.get(`room-by-floor-id`, {
        params: {
          company_id: this.$auth.user.company_id,
          floor_id: floor_id,
        },
      });
      this.rooms = data;
    },
    encrypt() {
      this.encryptedID = this.$crypto.encrypt(id);
      // this.fullLink = this.originalURL + this.encryptedID;
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
      this.$refs.otherDoc_input.value = null;
      this.dialogCropping = false;
    },
    close() {
      this.dialog = false;
      this.errors = [];
      setTimeout(() => {}, 300);
    },
    can(per) {
      return this.$pagePermission.can(per, this);
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
    getDataFromApi() {
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
          ...this.filters,
        },
      };

      this.$axios.get(this.endpoint, options).then(({ data }) => {
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
    addItem() {
      this.disabled = false;
      this.formAction = "Create";
      this.DialogBox = true;
      this.payload = {};
      this.setImagePreview = "/no-profile-image.jpg";
    },
    addMember(item) {
      this.disabled = false;
      this.formAction = "Create";
      this.memberDialogBox = true;
      this.payload = item;
      this.member.tanent_id = item.id;

      this.getExistingMembers(item.id);
    },
    viewMember(item) {
      this.disabled = true;
      this.formAction = "View";
      this.viewMemberDialogBox = true;
      this.payload = item;

      this.getExistingMembers(item.id);
    },
    editItem({
      profile_picture,
      passport_doc,
      id_doc,
      contract_doc,
      ejari_doc,
      license_doc,
      others_doc,
      floor,
      room,
      ...payload
    }) {
      this.formAction = "Edit";
      this.disabled = false;
      this.DialogBox = true;

      this.setImagePreview = profile_picture;
      this.payload = payload;

      this.getRoomsByFloorId(payload.floor_id);
    },
    viewItem({ profile_picture, ...payload }) {
      this.formAction = "View";
      this.disabled = true;
      this.DialogBox = true;

      this.imagePreview = profile_picture;
      this.payload = payload;
    },
    getExistingMembers(id) {
      this.$axios.get(`/members/${id}`).then(({ data }) => {
        this.members = data;

        if (!data.length) {
          this.members.push({
            full_name: null,
            phone_number: null,
            age: null,
            relation: null,
            tanent_id: id,
          });
        }
      });
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(`${this.endpoint}/${item.id}`)
          .then(({ data }) => {
            this.getDataFromApi();
            this.snackbar = true;
            this.response = "Record deleted successfully";
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
    previewImage(event) {
      const file = this.payload.profile_picture;

      if (file) {
        // Read the selected file and create a preview
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.imagePreview = null;
      }
    },

    previewMemberImage(event) {
      const file = this.member.profile_picture;

      if (file) {
        // Read the selected file and create a preview
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imageMemberPreview = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.imageMemberPreview = null;
      }
    },
    others_doc(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.otherDoc_input;
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
      let formData = new FormData();

      for (let x in obj) {
        formData.append(x, obj[x]);
      }
      if (this.payload.profile_picture) {
        formData.append("profile_picture", this.upload.name);
      }

      if (this.payload.passport_doc) {
        formData.append("passport_doc", this.payload.passport_doc.name);
      }

      formData.append("company_id", this.$auth.user.company_id);

      return formData;
    },

    async generateQRCode(fullLink) {
      try {
        this.qrCodeDataURL = await this.$qrcode.generate(fullLink);
      } catch (error) {
        console.error("Error generating QR code:", error);
      }
    },

    async generateCompanyQRCode(fullLink) {
      try {
        this.qrCompanyCodeDataURL = await this.$qrcode.generate(fullLink);
      } catch (error) {
        console.error("Error generating QR code:", error);
      }
    },
    submitMembers() {
      this.$axios
        .post(
          `/members/${this.payload.id}`,
          this.mapper(Object.assign(this.member))
        )
        .then(({ data }) => {
          this.handleSuccessResponse("Member(s) inserted successfully");
        })
        .catch(({ response }) => {
          this.handleErrorResponse(response);
        });
    },
    tanentValidate() {
      this.$axios
        .post(
          this.endpoint + "-validate",
          this.mapper(Object.assign(this.payload))
        )
        .then(({ data }) => {
          this.errors = [];
          this.nextStep();
        })
        .catch(({ response }) => {
          this.handleErrorResponse(response);
        });

      // }
    },

    tanentUpdateValidate() {
      this.$axios
        .post(
          this.endpoint + "-update-validate/" + this.payload.id,
          this.mapper(Object.assign(this.payload))
        )
        .then(({ data }) => {
          this.errors = [];
          this.nextStep();
        })
        .catch(({ response }) => {
          this.handleErrorResponse(response);
        });

      // }
    },

    submit() {
      this.$axios
        .post(this.endpoint, this.mapper(Object.assign(this.payload)))
        .then(({ data }) => {
          this.handleSuccessResponse("Tanent inserted successfully");
        })
        .catch(({ response }) => {
          this.handleErrorResponse(response);
        });

      // }
    },
    update_data() {
      this.$axios
        .post(
          this.endpoint + "-update/" + this.payload.id,
          this.mapper(Object.assign(this.payload))
        )
        .then(({ data }) => {
          this.handleSuccessResponse("Tanent updated successfully");
        })
        .catch(({ response }) => {
          this.handleErrorResponse(response);
        });

      // }
    },

    update_member(member) {
      let formData = new FormData();

      if (member.profile_picture.name) {
        formData.append("profile_picture", member.profile_picture);
      }

      formData.append("full_name", member.full_name);
      formData.append("relation", member.relation);
      formData.append("age", member.age);
      formData.append("phone_number", member.phone_number);

      this.$axios
        .post("/members-update/" + member.id, formData)
        .then(({ data }) => {
          this.handleSuccessResponse("Member updated successfully");
        })
        .catch(({ response }) => {
          this.handleErrorResponse(response);
        });

      // }
    },

    handleSuccessResponse(message) {
      this.errors = [];
      this.snackbar = true;
      this.response = message;
      this.memberDialogBox = false;
      this.DialogBox = false;
      this.dialog = true;
      this.getDataFromApi();
    },
    handleErrorResponse(response) {
      if (!response) {
        this.snackbar = true;
        this.response = "An unexpected error occurred.";
        return;
      }
      let { status, data, statusText } = response;

      if (status && status == 422) {
        this.errors = data.errors;
        return;
      }

      this.snackbar = true;
      this.response = statusText;
    },
  },
};
</script>
