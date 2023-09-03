<template>
  <div>
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
    <v-card>
      <v-tabs
        v-model="tab"
        background-color="background"
        right
        dark
        icons-and-text
      >
        <v-tabs-slider></v-tabs-slider>

        <v-tab href="#tab-1">
          Visitor
          <v-icon>mdi-account</v-icon>
        </v-tab>

        <v-tab href="#tab-2">
          Host
          <v-icon>mdi-account-tie</v-icon>
        </v-tab>

        <v-tab href="#tab-3">
          Status
          <v-icon>mdi-pencil</v-icon>
        </v-tab>
      </v-tabs>

      <v-tabs-items v-model="tab">
        <v-tab-item value="tab-1">
          <v-container>
            <v-row>
              <v-col cols="3" class="pt-5">
                <div class="form-group" style="margin: 0 auto; width: 200px">
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
                    :disabled="disabled"
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
              <v-col cols="9" class="pt-5">
                <v-row>
                  <v-col cols="4">
                    <label class="col-form-label"
                      >Visit From<span class="text-danger">*</span></label
                    >

                    <v-menu
                      ref="visit_from_menu_ref"
                      v-model="visit_from_menu"
                      :close-on-content-click="false"
                      :return-value.sync="payload.visit_from"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          :disabled="disabled"
                          :hide-details="!errors.visit_from"
                          :error="errors.visit_from"
                          :error-messages="
                            errors && errors.visit_from
                              ? errors.visit_from[0]
                              : ''
                          "
                          v-model="payload.visit_from"
                          append-icon="mdi-calendar"
                          outlined
                          dense
                          readonly
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="payload.visit_from"
                        no-title
                        scrollable
                      >
                        <v-spacer></v-spacer>
                        <v-btn
                          text
                          color="primary"
                          @click="visit_from_menu = false"
                        >
                          Cancel
                        </v-btn>
                        <v-btn
                          text
                          color="primary"
                          @click="
                            $refs.visit_from_menu_ref.save(payload.visit_from)
                          "
                        >
                          OK
                        </v-btn>
                      </v-date-picker>
                    </v-menu>
                  </v-col>
                  <v-col cols="4">
                    <label class="col-form-label"
                      >Visit To<span class="text-danger">*</span></label
                    >
                    <v-menu
                      ref="visit_to_menu_ref"
                      v-model="visit_to_menu"
                      :close-on-content-click="false"
                      :return-value.sync="payload.visit_to"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          :disabled="disabled"
                          :hide-details="!errors.visit_to"
                          :error="errors.visit_to"
                          :error-messages="
                            errors && errors.visit_to ? errors.visit_to[0] : ''
                          "
                          v-model="payload.visit_to"
                          append-icon="mdi-calendar"
                          outlined
                          dense
                          readonly
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="payload.visit_to"
                        no-title
                        scrollable
                      >
                        <v-spacer></v-spacer>
                        <v-btn
                          text
                          color="primary"
                          @click="visit_to_menu = false"
                        >
                          Cancel
                        </v-btn>
                        <v-btn
                          text
                          color="primary"
                          @click="
                            $refs.visit_to_menu_ref.save(payload.visit_to)
                          "
                        >
                          OK
                        </v-btn>
                      </v-date-picker>
                    </v-menu>
                  </v-col>
                  <v-col cols="4">
                    <label class="col-form-label"
                      >Purpose<span class="text-danger">*</span></label
                    >
                    <v-select
                      :disabled="disabled"
                      v-model="payload.purpose_id"
                      :items="purposes"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      item-text="name"
                      item-value="id"
                      :hide-details="!errors.purpose_id"
                      :error="errors.purpose_id"
                      :error-messages="
                        errors && errors.purpose_id ? errors.purpose_id[0] : ''
                      "
                    ></v-select>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >First Name<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.first_name"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.first_name"
                      :error="errors.first_name"
                      :error-messages="
                        errors && errors.first_name ? errors.first_name[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Last Name<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.last_name"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.last_name"
                      :error="errors.last_name"
                      :error-messages="
                        errors && errors.last_name ? errors.last_name[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Gender<span class="text-danger">*</span></label
                    >
                    <v-select
                      :disabled="disabled"
                      :items="[`Male`, `Female`]"
                      v-model="payload.gender"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.gender"
                      :error="errors.gender"
                      :error-messages="
                        errors && errors.gender ? errors.gender[0] : ''
                      "
                    ></v-select>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Phone Number<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.phone_number"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.phone_number"
                      :error="errors.phone_number"
                      :error-messages="
                        errors && errors.phone_number
                          ? errors.phone_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label">Email Address</label>
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.email"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.email"
                      :error="errors.email"
                      :error-messages="
                        errors && errors.email ? errors.email[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Company Name<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.visitor_company_name"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.visitor_company_name"
                      :error="errors.visitor_company_name"
                      :error-messages="
                        errors && errors.visitor_company_name
                          ? errors.visitor_company_name[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >ID Type<span class="text-danger">*</span></label
                    >
                    <v-select
                      :disabled="disabled"
                      v-model="payload.id_type"
                      :items="[
                        { id: 1, name: `Emirates ID` },
                        { id: 2, name: `National ID` },
                      ]"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      item-text="name"
                      item-value="id"
                      :hide-details="!errors.id_type"
                      :error="errors.id_type"
                      :error-messages="
                        errors && errors.id_type ? errors.id_type[0] : ''
                      "
                    ></v-select>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >ID Number<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.id_number"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.id_number"
                      :error="errors.id_number"
                      :error-messages="
                        errors && errors.id_number ? errors.id_number[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >ID Copy<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.id_copy"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.id_copy"
                      :error="errors.id_copy"
                      :error-messages="
                        errors && errors.id_copy ? errors.id_copy[0] : ''
                      "
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-container>
        </v-tab-item>
        <v-tab-item value="tab-2">
          <v-container>
            <v-row>
              <v-col cols="3" class="pt-5">
                <div class="form-group" style="margin: 0 auto; width: 200px">
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
                    :disabled="disabled"
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
              <v-col cols="9" class="pt-5">
                <v-row>
                  <v-col cols="4">
                    <label class="col-form-label"
                      >First Name<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.host_first_name"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.host_first_name"
                      :error="errors.host_first_name"
                      :error-messages="
                        errors && errors.host_first_name
                          ? errors.host_first_name[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Last Name<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.host_last_name"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.host_last_name"
                      :error="errors.host_last_name"
                      :error-messages="
                        errors && errors.host_last_name
                          ? errors.host_last_name[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Gender<span class="text-danger">*</span></label
                    >
                    <v-select
                      :disabled="disabled"
                      :items="[`Male`, `Female`]"
                      v-model="payload.host_gender"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      item-text="name"
                      item-value="id"
                      :hide-details="!errors.host_gender"
                      :error="errors.host_gender"
                      :error-messages="
                        errors && errors.host_gender
                          ? errors.host_gender[0]
                          : ''
                      "
                    ></v-select>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Phone Number<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.host_phone_number"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.host_phone_number"
                      :error="errors.host_phone_number"
                      :error-messages="
                        errors && errors.host_phone_number
                          ? errors.host_phone_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Email Address<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.host_email"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.host_email"
                      :error="errors.host_email"
                      :error-messages="
                        errors && errors.host_email ? errors.host_email[0] : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Company<span class="text-danger">*</span></label
                    >
                    <v-select
                      :disabled="disabled"
                      v-model="payload.host_company_id"
                      :items="host_company_list"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      item-text="company_name"
                      item-value="id"
                      :hide-details="!errors.host_company_id"
                      :error="errors.host_company_id"
                      :error-messages="
                        errors && errors.host_company_id
                          ? errors.host_company_id[0]
                          : ''
                      "
                    ></v-select>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-container>
        </v-tab-item>
        <v-tab-item value="tab-3">
          <v-container>
            <v-row>
              <v-col cols="3" class="pt-5">
                <div class="form-group" style="margin: 0 auto; width: 200px">
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
                    :disabled="disabled"
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
              <v-col cols="9" class="pt-5">
                <v-row>
                  <v-col cols="4">
                    <label class="col-form-label"
                      >Status<span class="text-danger">*</span></label
                    >
                    <v-select
                      :disabled="disabled"
                      v-model="payload.status_id"
                      :items="statuses"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      item-text="name"
                      item-value="id"
                      :hide-details="!errors.status_id"
                      :error="errors.status_id"
                      :error-messages="
                        errors && errors.status_id ? errors.status_id[0] : ''
                      "
                    ></v-select>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Date<span class="text-danger">*</span></label
                    >

                    <v-menu
                      ref="date_menu_ref"
                      v-model="date_menu"
                      :close-on-content-click="false"
                      :return-value.sync="payload.date"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          :disabled="disabled"
                          :hide-details="!errors.date"
                          :error="errors.date"
                          :error-messages="
                            errors && errors.date ? errors.date[0] : ''
                          "
                          v-model="payload.date"
                          append-icon="mdi-calendar"
                          outlined
                          dense
                          readonly
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker v-model="payload.date" no-title scrollable>
                        <v-spacer></v-spacer>
                        <v-btn
                          text
                          color="primary"
                          @click="date_menu_ref = false"
                        >
                          Cancel
                        </v-btn>
                        <v-btn
                          text
                          color="primary"
                          @click="$refs.date_menu_ref.save(payload.date)"
                        >
                          OK
                        </v-btn>
                      </v-date-picker>
                    </v-menu>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4">
                    <label class="col-form-label"
                      >Approved BY<span class="text-danger">*</span></label
                    >
                    <v-select
                      :disabled="disabled"
                      v-model="payload.updated_by"
                      :items="users"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      item-text="name"
                      item-value="id"
                      :hide-details="!errors.updated_by"
                      :error="errors.updated_by"
                      :error-messages="
                        errors && errors.updated_by ? errors.updated_by[0] : ''
                      "
                    ></v-select>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Phone Number<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.status_phone_number"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.status_phone_number"
                      :error="errors.status_phone_number"
                      :error-messages="
                        errors && errors.status_phone_number
                          ? errors.status_phone_number[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>

                  <v-col cols="4">
                    <label class="col-form-label"
                      >Company Name<span class="text-danger">*</span></label
                    >
                    <v-text-field
                      :disabled="disabled"
                      v-model="payload.company_name"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.company_name"
                      :error="errors.company_name"
                      :error-messages="
                        errors && errors.company_name
                          ? errors.company_name[0]
                          : ''
                      "
                    ></v-text-field>
                  </v-col>
                </v-row>

                <v-row>
                  <v-col cols="12">
                    <label class="col-form-label"
                      >Reason<span class="text-danger">*</span></label
                    >
                    <v-textarea
                      :disabled="disabled"
                      v-model="payload.reason"
                      dense
                      menu-props="min-width: auto; max-height: 200px;"
                      class="text-center"
                      outlined
                      :hide-details="!errors.reason"
                      :error="errors.reason"
                      :error-messages="
                        errors && errors.reason ? errors.reason[0] : ''
                      "
                    ></v-textarea>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-container>
        </v-tab-item>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <div class="text-right px-3 pb-3">
            <v-btn small :loading="loading" color="primary" @click="store_data">
              Submit
            </v-btn>
          </div>
        </v-card-actions>
      </v-tabs-items>
    </v-card>
  </div>
</template>

<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";

let date = new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
  .toISOString()
  .substring(0, 10);
export default {
  components: {
    VueCropper,
  },

  layout: "login",
  auth: false,

  data: () => ({
    decryptedID: "",
    disabled: false,
    visit_from_menu: false,
    visit_to_menu: false,

    date_menu: false,

    checkboxItems: [
      { label: "Item 1", value: 1 },
      { label: "Item 2", value: 2 },
      { label: "Item 3", value: 3 },
      // Add more items as needed
    ],

    payload: {
      system_user_id: "",
      visit_from: date,
      visit_to: date,
      timezone_id: 1,
      purpose_id: 1,
      first_name: "",
      last_name: "",
      host_first_name: "",
      host_last_name: "",
      gender: "Male",
      host_gender: "Male",
      phone_number: "",
      host_phone_number: "",
      email: "",
      host_email: "",
      visitor_company_name: "",
      id_type: 1,
      id_number: "",
      id_copy: "jpg",
      host_company_id: "",

      status_id: 1,
      date,
      updated_by: 1,
      status_phone_number: "",
      company_name: "",
      reason: "",
      company_id: "",
    },

    tab: null,

    statuses: [],
    timezones: [],
    zones: [],
    joiningDate: null,
    VisitFromMenuOpen: false,
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

    upload: {
      name: "",
    },
    previewImage: null,
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
    Model: "Visitor",
    endpoint: "visitor",
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
    purposes: [],
    users: [],
    departments: [],
    sub_departments: [],
    designations: [],
    roles: [],
    department_filter_id: "",
    dialogVisible: false,
    payloadOptions: {},
    company_id: 1,
    host_company_list: [],
    device_ids: [],
    formAction: "Create",
  }),
  mounted() {
    this.decryptedID = this.$crypto.decrypt(this.$route.params.id);
    this.payload.first_name = this.decryptedID;
  },
  created() {
    this.loading = false;
    this.boilerplate = true;
  },

  methods: {
    closeViewDialog() {
      this.viewDialog = false;
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
    getPurposes() {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
        },
      };
      this.$axios.get(`purpose_list`, options).then(({ data }) => {
        this.purposes = data;
      });
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
      let formData = new FormData();

      for (let x in obj) {
        formData.append(x, obj[x]);
      }
      if (this.upload) {
        formData.append("logo", this.upload.name);
      }

      formData.append("company_id", this.$auth.user.company_id);

      return formData;
    },
    store_data() {
      this.$axios
        .post("/visitor", this.mapper(Object.assign(this.payload)))
        .then(({ data }) => {
          this.errors = [];
          this.snackbar = true;
          this.response = "Visitor inserted successfully";
          this.getDataFromApi();
          this.DialogBox = false;
        })
        .catch(({ response }) => {
          if (!response) {
            return false;
          }
          let { status, data, statusText } = response;

          if (status && status == 422) {
            this.errors = data.errors;
            return;
          }

          this.snackbar = true;
          this.response = statusText;
        });
    },
  },
};
</script>
