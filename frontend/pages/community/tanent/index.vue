<template>
  <div v-if="can('employee_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="!loading">
      <v-dialog persistent v-model="memberDialogBox" width="900">
        <v-card>
          <v-toolbar dense class="popup_background" flat>
            {{ formAction }} Member

            <v-spacer></v-spacer>
            <span v-if="formAction !== 'View'">
              <v-icon
                class="ml-2 primary--text"
                color="primary"
                @click="addMemberItem"
              >
                mdi mdi-plus-circle</v-icon
              >
            </span>
          </v-toolbar>

          <v-card
            elevation="0"
            class="ma-2"
            v-for="(member, index) in members"
            :key="index"
          >
            <v-container>
              <v-row>
                <v-col cols="6"
                  ><strong>Member {{ index + 1 }} </strong>
                  <p style="display: none">
                    {{ (member.tanent_id = payload.id) }}
                  </p>
                </v-col>
                <v-col cols="6" class="text-right">
                  <v-icon
                    v-if="index > 0 && formAction !== 'View'"
                    right
                    color="red"
                    @click="removeMemberItem(index)"
                  >
                    mdi mdi-delete</v-icon
                  >
                </v-col>
                <v-col cols="6">
                  <v-text-field
                    label="Full Name"
                    :readonly="disabled"
                    v-model="member.full_name"
                    dense
                    class="text-center"
                    outlined
                    :hide-details="!errors.full_name"
                    :error="errors.full_name"
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
                    :error="errors.relation"
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
                    :error="errors.age"
                    :error-messages="errors && errors.age ? errors.age[0] : ''"
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
                    :error="errors.phone_number"
                    :error-messages="
                      errors && errors.phone_number
                        ? errors.phone_number[0]
                        : ''
                    "
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-card>
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
        <v-card>
          <v-toolbar class="popup_background" flat>
            {{ formAction }} Tanent

            <v-spacer></v-spacer>
            <span>
              <v-icon class="ml-2" @click="DialogBox = false" dark>
                mdi mdi-close-circle</v-icon
              >
            </span>
          </v-toolbar>
          <v-container>
            <v-row>
              <v-col cols="6">
                <v-text-field
                  label="Full Name"
                  :readonly="disabled"
                  v-model="payload.full_name"
                  dense
                  class="text-center"
                  outlined
                  :hide-details="!errors.full_name"
                  :error="errors.full_name"
                  :error-messages="
                    errors && errors.full_name ? errors.full_name[0] : ''
                  "
                ></v-text-field>
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
                  :error="errors.phone_number"
                  :error-messages="
                    errors && errors.phone_number ? errors.phone_number[0] : ''
                  "
                ></v-text-field>
              </v-col>

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
                      :readonly="disabled"
                      v-model="payload.start_date"
                      label="Start Date"
                      append-icon="mdi-calendar"
                      hide-details
                      outlined
                      dense
                      readonly
                      v-bind="attrs"
                      v-on="on"
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
                      :readonly="disabled"
                      v-model="payload.end_date"
                      label="End Date"
                      append-icon="mdi-calendar"
                      hide-details
                      outlined
                      dense
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    v-model="payload.end_date"
                    @input="menu2 = false"
                  ></v-date-picker>
                </v-menu>
              </v-col>
              <v-col cols="6">
                <v-file-input
                  v-if="!disabled"
                  v-model="payload.profile_picture"
                  dense
                  outlined
                  prepend-icon=""
                  append-icon="mdi-camera"
                  label="Upload Photo"
                  @change="previewImage"
                ></v-file-input>
                <v-card v-if="imagePreview" elevation="0">
                  <v-avatar size="200">
                    <v-img :src="imagePreview"></v-img>
                  </v-avatar>
                </v-card>
              </v-col>
              <v-col cols="6">
                <v-file-input
                  v-if="!disabled"
                  v-model="payload.attachment"
                  dense
                  outlined
                  prepend-icon=""
                  append-icon="mdi-file"
                  label="Upload Attachment (optional)"
                  @change="previewAttachment"
                ></v-file-input>
                <v-card
                  v-if="attachmentPreview"
                  outlined
                  style="width: 100%; height: 250px; overflow: scroll"
                >
                  <v-img style="width: 100%" :src="attachmentPreview"></v-img>
                </v-card>
              </v-col>
            </v-row>
          </v-container>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <div class="text-right">
              <v-btn small color="grey white--text" @click="DialogBox = false">
                Close
              </v-btn>

              <v-btn
                v-if="can('employee_create') && formAction == 'Create'"
                small
                :loading="loading"
                color="primary"
                @click="submit"
              >
                Submit
              </v-btn>
              <v-btn
                v-else-if="can('employee_create') && formAction == 'Edit'"
                small
                :loading="loading"
                color="primary"
                @click="update_data"
              >
                Update
              </v-btn>
            </div>
          </v-card-actions>
        </v-card>
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
              <span>
                <v-btn
                  dense
                  x-small
                  class="ma-0 px-0"
                  :ripple="false"
                  text
                  title="Add Company"
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
              </span>
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
                <v-icon color="primary" class="mx-1" @click="viewMember(item)"> mdi-eye </v-icon>
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

    members: [],

    payload: {
      full_name: "",
      phone_number: "",
      floor_id: "",
      room_id: "",
      start_date: "",
      end_date: "",
    },
    imagePreview: null,
    attachmentPreview: null,

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
        text: "Tanent Id",
        align: "left",
        sortable: true,
        key: "id",
        value: "id",
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

    floors: [],
    rooms: [],
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
    async getFloors() {
      let { data: floors } = await this.$axios.get(`floor`, {
        params: { company_id: this.$auth.user.company_id },
      });
      this.floors = floors.data;
    },
    async getRoomsByFloorId(floor_id) {
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
      this.$refs.attachment_input.value = null;
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
    },
    addMember(item) {
      this.disabled = false;
      this.formAction = "Create";
      this.memberDialogBox = true;
      this.payload = item;

      this.getExistingMembers(item.id);
    },
    viewMember(item) {
      this.disabled = true;
      this.formAction = "View";
      this.memberDialogBox = true;
      this.payload = item;

      this.getExistingMembers(item.id);
    },
    editItem({ attachment, profile_picture, floor, room, ...payload }) {
      this.formAction = "Edit";
      this.disabled = false;
      this.DialogBox = true;

      this.imagePreview = profile_picture;
      this.attachmentPreview = attachment;
      this.payload = payload;
    },
    viewItem({ attachment, profile_picture, ...payload }) {
      this.formAction = "View";
      this.disabled = true;
      this.DialogBox = true;

      this.imagePreview = profile_picture;
      this.attachmentPreview = attachment;
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

    previewAttachment(event) {
      const file = this.payload.attachment;

      if (file) {
        // Read the selected file and create a preview
        const reader = new FileReader();
        reader.onload = (e) => {
          this.attachmentPreview = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.attachmentPreview = null;
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
      let formData = new FormData();

      for (let x in obj) {
        formData.append(x, obj[x]);
      }
      if (this.payload.profile_picture) {
        formData.append("profile_picture", this.payload.profile_picture);
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
        .post(`/members/${this.payload.id}`, this.members)
        .then(({ data }) => {
          // this.encrypt(data.record.id);
          this.errors = [];
          this.snackbar = true;
          this.response = "Member(s) inserted successfully";
          this.getDataFromApi();
          this.DialogBox = false;
          this.dialog = true;
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
    submit() {
      this.$axios
        .post(this.endpoint, this.mapper(Object.assign(this.payload)))
        .then(({ data }) => {
          // this.encrypt(data.record.id);
          this.errors = [];
          this.snackbar = true;
          this.response = "Tanent inserted successfully";
          this.getDataFromApi();
          this.DialogBox = false;
          this.dialog = true;
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

      // }
    },

    update_data() {
      this.$axios
        .post(
          this.endpoint + "-update/" + this.payload.id,
          this.mapper(Object.assign(this.payload))
        )
        .then(({ data }) => {
          this.errors = [];
          this.snackbar = true;
          this.response = "Tanent updated successfully";
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

      // }
    },
    // processImage(file) {
    //   const canvas = this.$refs.cropper.getCroppedCanvas();
    //   this.cropedImage = canvas.toDataURL();

    //   canvas.toBlob((blob) => {
    //     return { value: blob, file };
    //   }, "image/jpeg");
    // },
  },
};
</script>
