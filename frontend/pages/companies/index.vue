<template>
  <div>
    <div v-if="!preloader">
      <div class="text-center ma-2">
        <v-snackbar
          v-model="snackbar"
          top="top"
          color="secondary"
          elevation="24"
        >
          {{ response }}
        </v-snackbar>
      </div>

      <v-card class="mt-2">
        <v-tabs class="pt-3" :vertical="vertical">
          <v-tabs-slider color="violet"></v-tabs-slider>
          <v-tab>
            <v-icon left> mdi-domain </v-icon>
            <span>Profile</span>
          </v-tab>
          <v-tab>
            <v-icon left> mdi-license </v-icon>
            <span>License</span>
          </v-tab>
          <v-tab>
            <v-icon left> mdi-file </v-icon>
            <span>Documents</span>
          </v-tab>
          <v-tab>
            <v-icon left> mdi-lock </v-icon>
            <span>Password</span>
          </v-tab>
          <v-tab>
            <v-icon left> mdi-account-tie </v-icon>
            <span>Admins</span>
          </v-tab>

          <v-tab>
            <v-icon left> mdi-whatsapp </v-icon>
            <span>Whatsapp</span>
          </v-tab>

          <v-tab>
            <v-icon left> mdi-star-outline </v-icon>
            <span>Attendance Rating</span>
          </v-tab>
          <v-tab-item>
            <v-card v-if="can('company_profile_access')" outlined class="ma-5">
              <v-card-text>
                <v-row class="mt-5">
                  <v-col cols="2">
                    <div>
                      <div class="d-flex justify-center">
                        <v-avatar size="150" color="grey">
                          <img
                            :src="
                              previewImage ||
                              company_payload.logo ||
                              '/no-image.PNG'
                            "
                          />
                        </v-avatar>
                      </div>
                    </div>
                    <div class="my-2">
                      <div class="d-flex justify-center">
                        <div v-if="!upload.name" class="mx-auto">
                          <v-btn
                            @click="onpick_attachment"
                            class="mb-5"
                            rounded
                            outlined
                            small
                            color="primary"
                            >Change Logo</v-btn
                          >
                        </div>
                        <v-icon
                          dense
                          v-if="upload.name"
                          @click="cancelAttachment"
                          color="red"
                          >mdi-close
                        </v-icon>
                        <v-icon
                          dense
                          v-if="upload.name"
                          @click="update_company"
                          color="primary"
                          >mdi-floppy
                        </v-icon>
                        <input
                          required
                          type="file"
                          @change="attachment"
                          style="display: none"
                          accept="image/*"
                          ref="attachment_input"
                        />

                        <span
                          v-if="errors && errors.logo"
                          class="text-danger mt-2"
                          >{{ errors.logo[0] }}</span
                        >
                      </div>
                    </div>
                    <v-divider></v-divider>

                    <div class="text-center mt-5">
                      <div>Visitor QR Code</div>
                      <div>
                        <v-avatar v-if="qrCompanyCodeDataURL" size="150" tile>
                          <img :src="qrCompanyCodeDataURL" alt="Avatar" />
                        </v-avatar>
                      </div>
                    </div>
                  </v-col>
                  <v-divider vertical></v-divider>
                  <v-col cols="10">
                    <v-row>
                      <v-col cols="12">
                        <v-card flat>
                          <v-card-title>Information</v-card-title>
                          <v-card-text>
                            <v-simple-table dense flat class="my-simple-table">
                              <tbody>
                                <tr>
                                  <td style="width: 200px">Company Code</td>
                                  <td>
                                    <span>{{
                                      company_payload.company_code
                                    }}</span>
                                  </td>
                                </tr>

                                <tr>
                                  <td style="width: 200px">Company Name</td>
                                  <td>
                                    <span>{{ company_payload.name }}</span>
                                  </td>
                                </tr>

                                <tr>
                                  <td style="width: 200px">Company Email</td>
                                  <td>
                                    <span>{{ user_payload.email }}</span>
                                  </td>
                                </tr>

                                <tr>
                                  <td style="width: 200px">Member From</td>
                                  <td>
                                    <span>{{
                                      company_payload.member_from
                                    }}</span>
                                  </td>
                                </tr>

                                <tr>
                                  <td style="width: 200px">Expiry Date</td>
                                  <td>
                                    <span>{{ company_payload.expiry }}</span>
                                  </td>
                                </tr>

                                <tr>
                                  <td style="width: 200px">Max Branches</td>
                                  <td>
                                    <span>{{
                                      company_payload.max_branches
                                    }}</span>
                                  </td>
                                </tr>

                                <tr>
                                  <td style="width: 200px">Max Employees</td>
                                  <td>
                                    <span>{{
                                      company_payload.max_employee
                                    }}</span>
                                  </td>
                                </tr>

                                <tr>
                                  <td style="width: 200px">Max Devices</td>
                                  <td>
                                    <span>{{
                                      company_payload.max_devices
                                    }}</span>
                                  </td>
                                </tr>

                                <!-- <tr>
                                  <td style="width: 200px">Full Name</td>
                                  <td>
                                    <span v-if="!editForm">{{
                                      employee.full_name
                                    }}</span>
                                    <v-text-field
                                      v-else
                                      autofocus
                                      :readonly="!editForm"
                                      style="border-bottom: 1px solid #eaeaea"
                                      class="small-input-font"
                                      dense
                                      v-model="employee.full_name"
                                      color="primary"
                                      :hide-details="!errors.full_name"
                                      :error-messages="
                                        errors && errors.full_name
                                          ? errors.full_name[0]
                                          : ''
                                      "
                                    />
                                  </td>
                                </tr> -->
                              </tbody>
                            </v-simple-table>
                          </v-card-text>
                        </v-card>
                      </v-col>
                      <v-col cols="12">
                        <v-card flat>
                          <v-card-title
                            >Contact Details <v-spacer></v-spacer>
                            <v-icon
                              small
                              color="primary"
                              @click="editForm = !editForm"
                              >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
                            ></v-card-title
                          >
                          <v-card-text>
                            <v-simple-table dense flat class="my-simple-table">
                              <tbody>
                                <tr>
                                  <td style="width: 200px">
                                    Contact Person Name
                                  </td>
                                  <td>
                                    <span v-if="!editForm">{{
                                      contact_payload.name
                                    }}</span>
                                    <v-text-field
                                      v-else
                                      autofocus
                                      :readonly="!editForm"
                                      style="border-bottom: 1px solid #eaeaea"
                                      class="small-input-font"
                                      dense
                                      v-model="contact_payload.name"
                                      color="primary"
                                      :hide-details="!errors.name"
                                      :error-messages="
                                        errors && errors.name
                                          ? errors.name[0]
                                          : ''
                                      "
                                    />
                                  </td>
                                </tr>
                                <tr>
                                  <td style="width: 200px">
                                    Contact Person Number
                                  </td>
                                  <td>
                                    <v-text-field
                                      :readonly="!editForm"
                                      style="border-bottom: 1px solid #eaeaea"
                                      class="small-input-font"
                                      dense
                                      v-model="contact_payload.number"
                                      color="primary"
                                      :hide-details="!errors.number"
                                      :error-messages="
                                        errors && errors.number
                                          ? errors.number[0]
                                          : ''
                                      "
                                    />
                                  </td>
                                </tr>
                                <tr>
                                  <td style="width: 200px">
                                    Contact Person Position
                                  </td>
                                  <td>
                                    <v-text-field
                                      :readonly="!editForm"
                                      style="border-bottom: 1px solid #eaeaea"
                                      class="small-input-font"
                                      dense
                                      v-model="contact_payload.position"
                                      color="primary"
                                      :hide-details="!errors.position"
                                      :error-messages="
                                        errors && errors.position
                                          ? errors.position[0]
                                          : ''
                                      "
                                    />
                                  </td>
                                </tr>
                                <tr>
                                  <td style="width: 200px">
                                    Whatsapp (with Country Code ex: 9199XXX)
                                  </td>
                                  <td>
                                    <v-text-field
                                      :readonly="!editForm"
                                      style="border-bottom: 1px solid #eaeaea"
                                      class="small-input-font"
                                      dense
                                      v-model="contact_payload.whatsapp"
                                      color="primary"
                                      :hide-details="!errors.whatsapp"
                                      :error-messages="
                                        errors && errors.whatsapp
                                          ? errors.whatsapp[0]
                                          : ''
                                      "
                                    />
                                  </td>
                                </tr>
                                <tr>
                                  <td style="width: 200px">Phone Number</td>
                                  <td>
                                    <v-text-field
                                      :readonly="!editForm"
                                      style="border-bottom: 1px solid #eaeaea"
                                      class="small-input-font"
                                      dense
                                      v-model="contact_payload.number"
                                      color="primary"
                                      :hide-details="!errors.number"
                                      :error-messages="
                                        errors && errors.number
                                          ? errors.number[0]
                                          : ''
                                      "
                                    />
                                  </td>
                                </tr>
                                <tr>
                                  <td style="width: 200px">P.O Box</td>
                                  <td>
                                    <v-text-field
                                      :readonly="!editForm"
                                      style="border-bottom: 1px solid #eaeaea"
                                      class="small-input-font"
                                      dense
                                      v-model="contact_payload.p_o_box_no"
                                      color="primary"
                                      :hide-details="!errors.p_o_box_no"
                                      :error-messages="
                                        errors && errors.p_o_box_no
                                          ? errors.p_o_box_no[0]
                                          : ''
                                      "
                                    />
                                  </td>
                                </tr>
                                <tr>
                                  <td style="width: 200px">Location</td>
                                  <td>
                                    <v-text-field
                                      :readonly="!editForm"
                                      style="border-bottom: 1px solid #eaeaea"
                                      class="small-input-font"
                                      dense
                                      v-model="contact_payload.location"
                                      color="primary"
                                      :hide-details="!errors.location"
                                      :error-messages="
                                        errors && errors.location
                                          ? errors.location[0]
                                          : ''
                                      "
                                    />
                                  </td>
                                </tr>
                              </tbody>
                            </v-simple-table>
                          </v-card-text>
                          <v-card-actions>
                            <v-spacer></v-spacer>
                            <div class="text-right">
                              <v-btn
                                v-if="can('company_profile_edit')"
                                small
                                :loading="loading"
                                color="primary"
                                @click="update_company"
                              >
                                Submit
                              </v-btn>
                            </div>
                          </v-card-actions>
                        </v-card>
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tab-item>

          <v-tab-item>
            <v-card flat v-if="can('license_access')">
              <v-card-text>
                <v-row>
                  <v-col cols="6">
                    <v-row>
                      <v-col cols="3">
                        <!-- <label class="col-form-label"> License Type</label>
                            <span class="text-danger">*</span> -->
                        <v-select
                          label="License Type"
                          outlined
                          dense
                          v-model="company_trade_license.license_type"
                          x-small
                          :items="[
                            {
                              value: '',
                              text: 'Select Type',
                            },
                            {
                              value: 'commercial_licenses',
                              text: 'Commercial licenses',
                            },
                            {
                              value: 'industrial_license',
                              text: 'Industrial License',
                            },
                            {
                              value: 'professional_license',
                              text: 'Professional license',
                            },
                          ]"
                          item-value="value"
                          item-text="text"
                          :hide-details="true"
                        ></v-select>
                      </v-col>
                      <v-col cols="3">
                        <!-- <label class="col-form-label"> License </label>
                            <span class="text-danger">*</span> -->
                        <v-text-field
                          label="License"
                          dense
                          outlined
                          v-model="company_trade_license.license_no"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3">
                        <!-- <label class="col-form-label"> Emirate </label>
                            <span class="text-danger">*</span> -->
                        <v-text-field
                          label="Emirate"
                          dense
                          outlined
                          v-model="company_trade_license.emirate"
                        ></v-text-field>
                        <span
                          v-if="errors && errors.emirate"
                          class="text-danger mt-2"
                          >{{ errors.emirate[0] }}</span
                        >
                      </v-col>

                      <v-col cols="3">
                        <!-- <label class="col-form-label"> Manager </label>
                            <span class="text-danger">*</span> -->
                        <v-text-field
                          label="Manager"
                          dense
                          outlined
                          v-model="company_trade_license.manager"
                        ></v-text-field>
                        <span
                          v-if="errors && errors.manager"
                          class="text-danger mt-2"
                          >{{ errors.manager[0] }}</span
                        >
                      </v-col>

                      <v-col cols="3">
                        <!-- <label class="col-form-label"> Issue Date </label>
                            <span class="text-danger">*</span> -->
                        <v-menu
                          v-model="menuIssueDate"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              label="Issue Date"
                              dense
                              outlined
                              v-model="company_trade_license.issue_date"
                              readonly
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="company_trade_license.issue_date"
                            @input="menuIssueDate = false"
                          ></v-date-picker>
                        </v-menu>
                        <span
                          v-if="errors && errors.issue_date"
                          class="text-danger mt-2"
                          >{{ errors.issue_date[0] }}</span
                        >
                      </v-col>

                      <v-col cols="3">
                        <!-- <label class="col-form-label"> Expiry Date </label>
                            <span class="text-danger">*</span> -->
                        <v-menu
                          v-model="menuExpiryDate"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              label="Expiry Date "
                              dense
                              outlined
                              v-model="company_trade_license.expiry_date"
                              readonly
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="company_trade_license.expiry_date"
                            @input="menuExpiryDate = false"
                          ></v-date-picker>
                        </v-menu>
                        <span
                          v-if="errors && errors.expiry_date"
                          class="text-danger mt-2"
                          >{{ errors.expiry_date[0] }}</span
                        >
                      </v-col>

                      <v-col cols="3">
                        <!-- <label class="col-form-label"> Makeem No </label>
                            <span class="text-danger">*</span> -->
                        <v-text-field
                          label="Makeem No "
                          dense
                          outlined
                          v-model="company_trade_license.makeem_no"
                        ></v-text-field>
                        <span
                          v-if="errors && errors.makeem_no"
                          class="text-danger mt-2"
                          >{{ errors.makeem_no[0] }}</span
                        >
                      </v-col>
                      <v-col cols="12">
                        <div class="text-right">
                          <v-btn
                            v-if="can('license_edit')"
                            small
                            :loading="loading"
                            color="primary"
                            @click="update_license"
                          >
                            Submit
                          </v-btn>
                        </div>
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tab-item>

          <v-tab-item>
            <Document v-if="can('document_access')" />
          </v-tab-item>

          <v-tab-item>
            <v-row v-if="can('password_access')">
              <v-col cols="3">
                <v-col cols="12">
                  <!-- <label class="col-form-label"
                          >Current Password
                          <span class="text-danger">*</span></label
                        > -->
                  <v-text-field
                    label="Current Password"
                    dense
                    outlined
                    :hide-details="!errors.current_password"
                    :append-icon="
                      current_password_show ? 'mdi-eye' : 'mdi-eye-off'
                    "
                    :type="current_password_show ? 'text' : 'password'"
                    v-model="payload.current_password"
                    class="input-group--focused"
                    @click:append="
                      current_password_show = !current_password_show
                    "
                    :error="errors.current_password"
                    :error-messages="
                      errors && errors.current_password
                        ? errors.current_password
                        : ''
                    "
                  ></v-text-field>
                </v-col>
                <v-col md="12" sm="12" cols="12" dense>
                  <!-- <label class="col-form-label"
                          >Password <span class="text-danger">*</span></label
                        > -->
                  <v-text-field
                    label="New Password"
                    dense
                    outlined
                    :hide-details="!errors.password"
                    :append-icon="show_password ? 'mdi-eye' : 'mdi-eye-off'"
                    :type="show_password ? 'text' : 'password'"
                    v-model="payload.password"
                    class="input-group--focused"
                    @click:append="show_password = !show_password"
                    :error="errors.password"
                    :error-messages="
                      errors && errors.password ? errors.password[0] : ''
                    "
                  ></v-text-field>
                </v-col>

                <v-col md="12" sm="12" cols="12" dense>
                  <!-- <label class="col-form-label"
                          >Confirm Password
                          <span class="text-danger">*</span></label
                        > -->
                  <v-text-field
                    label="Confirm New Password"
                    dense
                    outlined
                    :hide-details="!errors.password_confirmation"
                    :append-icon="
                      show_password_confirm ? 'mdi-eye' : 'mdi-eye-off'
                    "
                    :type="show_password_confirm ? 'text' : 'password'"
                    v-model="payload.password_confirmation"
                    class="input-group--focused"
                    @click:append="
                      show_password_confirm = !show_password_confirm
                    "
                    :error="errors.show_password_confirm"
                    :error-messages="
                      errors && errors.show_password_confirm
                        ? errors.show_password_confirm[0]
                        : ''
                    "
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <div class="text-right">
                    <v-btn
                      v-if="can('password_edit')"
                      dark
                      small
                      :loading="loading_password"
                      color="primary"
                      @click="update_setting"
                    >
                      Submit
                    </v-btn>
                  </div>
                </v-col>
              </v-col>
            </v-row>
          </v-tab-item>

          <v-tab-item>
            <Admin v-if="can('admin_access')" />
            <!-- <v-container>
                  <div style="text-align: center">
                    <v-avatar v-if="qrCompanyCodeDataURL" size="150" tile>
                      <img :src="qrCompanyCodeDataURL" alt="Avatar" />
                    </v-avatar>
                  </div>
                  <span>
                    <a :href="`${fullCompanyLink}`" target="_blank">
                      {{ fullCompanyLink }}
                    </a>
                  </span>
                </v-container> -->
          </v-tab-item>

          <v-tab-item>
            <Whatsapp v-if="can('whatsapp_access')" />
          </v-tab-item>

          <v-tab-item>
            <PerformanceRatingDescription
              v-if="can('performance_rating_description_access')"
            />
          </v-tab-item>
        </v-tabs>
      </v-card>
    </div>
    <Preloader v-else />
  </div>
</template>

<script>

export default {

  data: () => ({
    editForm: false,
    fullCompanyLink: null,
    qrCompanyCodeDataURL: null,
    show_password_confirm: false,
    current_password_show: false,
    show_password: false,
    loading_password: false,
    menuIssueDate: false,
    menuExpiryDate: false,
    IssueDate: null,
    vertical: false,
    id: "",
    loading: false,
    preloader: true,
    upload: {
      name: "",
    },

    company_payload: {
      name: "",
      logo: "",
      member_from: "",
      expiry: "",
      max_branches: "",
      max_employee: "",
      max_devices: "",
      mol_id: "",
      p_o_box_no: "",
    },

    company_trade_license: {
      license_no: "",
      license_type: "",
      emirate: "",
      makeem_no: "",
      manager: "",
      issue_date: "",
      expiry_date: "",
    },

    contact_payload: {
      name: "",
      number: "",
      position: "",
      whatsapp: "",
    },
    user_payload: {
      password: "",
      password_confirmation: "",
    },
    payload: {
      password: "",
      current_password: "",
      password_confirmation: "",
    },
    geographic_payload: {
      lat: "",
      lon: "",
      location: "",
    },
    e1: 1,
    errors: [],
    previewImage: null,
    data: {},
    response: "",
    snackbar: false,
  }),
  async created() {
    try {
      this.getDataFromApi();

      this.fullCompanyLink =
        this.$appUrl + "/register/visitor/walkin/" + this.$auth.user.company_id;

      this.generateCompanyQRCode(this.fullCompanyLink);
    } catch (e) {}
  },
  methods: {
    async generateCompanyQRCode(fullLink) {
      try {
        this.qrCompanyCodeDataURL = await this.$qrcode.generate(fullLink);
      } catch (error) {
        console.error("Error generating QR code:", error);
      }
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },

    update_setting() {
      this.$axios
        .post(
          `/company/${this.$auth?.user?.company?.id}/update/user`,
          this.payload
        )
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = "Setting updated successfully";
          }
        })
        .catch((e) => console.log(e));
    },

    getDataFromApi() {
      this.id = this.$auth.user.company_id;
      this.$axios
        .get(`company/${this.$auth.user.company_id}`)
        .then(({ data }) => {
          let r = data.record;
          this.company_payload = r;
          this.contact_payload = r.contact;
          this.user_payload = r.user;

          if (r.trade_license) {
            this.company_trade_license = r.trade_license;
          }

          let mf = this.formatted_date(r.member_from);
          let exp = this.formatted_date(r.expiry);
          this.company_payload.member_from = mf;
          this.company_payload.expiry = exp;

          this.geographic_payload = {
            lat: this.company_payload.lat,
            lon: this.company_payload.lon,
            location: this.company_payload.location,
          };
          this.preloader = false;
        });
    },

    formatted_date(v) {
      let [year, month, date] = v.split("/");
      return `${year}-${month}-${date}`;
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },

    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;
      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
    },
    cancelAttachment() {
      this.upload.name = "";
      this.previewImage = null;
    },

    update_company() {
      this.update_contact();
      this.update_geographic();

      let payload = new FormData();

      payload.append("name", this.company_payload.name);
      if (this.upload.name) {
        payload.append("logo", this.upload.name);
      }
      payload.append("location", this.company_payload.location);
      payload.append("member_from", this.company_payload.member_from);
      payload.append("expiry", this.company_payload.expiry);
      payload.append("max_employee", this.company_payload.max_employee);
      payload.append("max_branches", this.company_payload.max_branches);
      payload.append("max_devices", this.company_payload.max_devices);

      payload.append("mol_id", this.company_payload.mol_id);
      payload.append("p_o_box_no", this.company_payload.p_o_box_no);

      this.start_process(`/company/${this.id}/update`, payload, `Company`);
    },
    update_contact() {
      this.start_process(
        `/company/${this.id}/update/contact`,
        this.contact_payload,
        `Contact`
      );
    },

    update_license() {
      this.start_process(
        `/company/${this.id}/trade-license`,
        this.company_trade_license,
        `Trade License`
      );
    },
    update_geographic() {
      this.start_process(
        `/company/${this.id}/update/geographic`,
        this.geographic_payload,
        `Geographic Info`
      );
    },
    update_user() {
      this.start_process(
        `/company/${this.id}/update/user`,
        this.user_payload,
        `User`
      );
    },
    start_process(url, payload, model) {
      this.loading = true;

      this.$axios
        .post(url, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = "Record updated successfully";
            this.upload.name = "";
          }
        })
        .catch((e) => {
          this.loading = false;
          this.upload.name = "";
          console.log(e);
        });
    },
  },
};
</script>
