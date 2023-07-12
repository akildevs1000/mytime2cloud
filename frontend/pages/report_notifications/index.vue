<template>
  <div v-if="can('setting_company_access')">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <div v-if="!preloader">
      <v-row class="">
        <!-- <v-col cols="3">
          <h4>Report Notification</h4>
           <div>Dashboard / Report Notification</div>
        </v-col> -->
        <div class="text-center">
          <v-dialog v-model="dialog" width="500">
            <v-card>
              <v-card-title dense class=" primary  white--text background">
                Send Test message to Whatsapp
                <v-spacer></v-spacer>
                <v-icon @click="dialog = false" outlined dark color="white">
                  mdi mdi-close-circle
                </v-icon>
              </v-card-title>
              <v-card-text class="mt-4">
                <v-text-field dense outlined placeholder="number" v-model="number">

                </v-text-field>
                <v-textarea dense outlined placeholder="message" v-model="message">
                  Hello
                </v-textarea>
              </v-card-text>

              <!-- <v-divider></v-divider> -->

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn class="primary" @click="send">
                  Send
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </div>
        <v-row>
          <v-col>
            <v-card class="mb-5" elevation="0">
              <v-toolbar class="rounded-md" color="background" dense flat dark>
                <v-toolbar-title><span> Notifications List</span></v-toolbar-title>
                <v-tooltip top color="primary">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dense class="ma-0 px-0" x-small :ripple="false" text v-bind="attrs" v-on="on">
                      <v-icon color="white" class="ml-2" @click="getDataFromApi()" dark>mdi mdi-reload</v-icon>
                    </v-btn>
                  </template>
                  <span>Reload</span>
                </v-tooltip>



                <v-spacer></v-spacer>
                <v-toolbar-items>
                  <v-col class="toolbaritems-button-design1">


                    <v-btn @click="dialog = true" small color="primary" class="primary mr-2 mb-2 toolbar-button-design1">
                      <v-icon small>mdi mdi-whatsapp</v-icon> Whatsapp Test
                    </v-btn>
                    <v-btn color="primary" small class="primary mr-2 mb-2 toolbar-button-design1"
                      to="/report_notifications/create">
                      <v-icon small>mdi mdi-email</v-icon> Add Report Notification
                    </v-btn>
                  </v-col>
                </v-toolbar-items>
              </v-toolbar>


              <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
                {{ snackText }}

                <template v-slot:action="{ attrs }">
                  <v-btn v-bind="attrs" text @click="snack = false">
                    Close
                  </v-btn>
                </template>
              </v-snackbar>
              <v-data-table dense :headers="headers_table" :items="data" model-value="data.id" :loading="loading"
                :footer-props="{
                  itemsPerPageOptions: [10, 50, 100, 500, 1000],
                }" class="elevation-1">

                <template v-slot:item.subject="{ item }">
                  <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                    @save="getDataFromApi()" @open="datatable_open">
                    {{ item.subject }}
                    <template v-slot:input>
                      <v-text-field @input="getDataFromApi('', 'serach_email_subject', $event)"
                        v-model="datatable_search_textbox" label="Search Subject"></v-text-field>
                    </template>
                  </v-edit-dialog>

                </template>
                <template v-slot:item.frequency="{ item }">
                  <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                    @save="getDataFromApi()" @open="datatable_open">
                    {{ item.frequency }}
                    <template v-slot:input>
                      <v-text-field @input="getDataFromApi('', 'serach_frequency', $event)"
                        v-model="datatable_search_textbox" label="Search Frequency"></v-text-field>
                    </template>
                  </v-edit-dialog>
                </template>
                <template v-slot:item.time="{ item }">
                  <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                    @save="getDataFromApi()" @open="datatable_open">
                    {{ item.time }}
                    <template v-slot:input>
                      <v-text-field @input="getDataFromApi('', 'serach_time', $event)" v-model="datatable_search_textbox"
                        label="Search Time"></v-text-field>
                    </template>
                  </v-edit-dialog>
                </template>
                <template v-slot:item.medium="{ item }">
                  <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                    @save="getDataFromApi()" @open="datatable_open">
                    <v-chip v-for="(medium, i) in item.mediums" :key="i" class="  ma-1" small color="primary">{{
                      medium
                    }}</v-chip>
                    <template v-slot:input>
                      <v-text-field @input="getDataFromApi('', 'serach_medium', $event)"
                        v-model="datatable_search_textbox" label="Search full word.. whatsapp,email"></v-text-field>
                    </template>
                  </v-edit-dialog>
                </template>
                <template v-slot:item.reports="{ item }">
                  <v-chip v-for="(report, i) in item.reports" :key="i" small color="primary" class="ma-1">{{
                    report
                  }}</v-chip>
                </template>
                <template v-slot:item.recipients="{ item }">
                  <v-edit-dialog large save-text="Reset" cancel-text="Ok" style="margin-left: 4%;"
                    @save="getDataFromApi()" @open="datatable_open">
                    <v-chip v-for="(to, i) in item.tos" :key="i" small color="primary" class="ma-1">{{ to
                    }}</v-chip>
                    <v-chip v-for="(cc, i) in item.ccs" :key="i" small color="primary" class="ma-1">{{ cc
                    }}
                      (Cc)</v-chip>

                    <v-chip v-for="(bcc, i) in item.bccs" :key="i" small color="primary" class="ma-1">{{
                      bcc }}
                      (Bcc)</v-chip>
                    <template v-slot:input>
                      <v-text-field @input="getDataFromApi('', 'serach_email_recipients', $event)"
                        v-model="datatable_search_textbox" label="Search full email"></v-text-field>
                    </template>
                  </v-edit-dialog>
                </template>
                <template v-slot:item.actions="{ item }">
                  <v-menu bottom left>
                    <template v-slot:activator="{ on, attrs }">
                      <div class="text-center">
                        <v-btn dark-2 icon v-bind="attrs" v-on="on">
                          <v-icon>mdi-dots-vertical</v-icon>
                        </v-btn>
                      </div>
                    </template>
                    <v-list width="120" dense>
                      <v-list-item @click="editItem(item)">
                        <v-list-item-title style="cursor: pointer">
                          <v-icon color="secondary" small>
                            mdi-pencil
                          </v-icon>
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

          </v-col>
        </v-row>
        <!-- <v-col cols="12">
          <v-card elevation="0" class="px-5 pb-5">
            <v-card-title>
              <label class="col-form-label"><b>Report Notification List </b></label>
              <v-spacer></v-spacer>
              <v-btn color="background" dark @click="dialog = true">
                <v-icon>mdi-phone</v-icon> Whatsapp Test
              </v-btn>
              &nbsp;
              <v-btn color="background" dark to="/report_notifications/create">
                <v-icon>mdi-plus</v-icon> Add Report Notification
              </v-btn>
            </v-card-title>
            <v-card-title>
              <table style="width: 100%">
                <tr>
                  <td style="width: 130px">
                    <label class="col-form-label"><b>Title</b></label>
                  </td>
                  <td style="max-width: 100px">
                    <label class="col-form-label">Frequency</label>
                  </td>
                  <td style="width: 80px">
                    <label class="col-form-label"><b>Time</b></label>
                  </td>
                  <td style="width: 160px">
                    <label class="col-form-label"><b>Medium</b></label>
                  </td>
                  <td style="width: 500px">
                    <label class="col-form-label"><b>Reports</b></label>
                  </td>

                  <td style="width: 600px">
                    <label class="col-form-label"><b>Recepients</b></label>
                  </td>
                  <td>
                    <div class="text-center">
                      <label class="col-form-label"> <b>Action</b></label>
                    </div>
                  </td>
                </tr>
                <tr v-for="(item, index) in data" :key="index">
                  <td style="max-width: 10px">
                    <label class="col-form-label">{{ item.subject }}</label>
                  </td>
                  <td style="max-width: 10px">
                    <label class="col-form-label">{{ item.frequency }}</label>
                  </td>
                  <td>
                    <label class="col-form-label">{{ item.time }}</label>
                  </td>
                  <td style="max-width: 100px">
                    <div>
                      <v-chip v-for="(medium, i) in item.mediums" :key="i" class="background ma-1" dark small>{{ medium
                      }}</v-chip>
                    </div>
                  </td>
                  <td>
                    <div>
                      <v-chip v-for="(report, i) in item.reports" :key="i" class="background ma-1" dark small>{{ report
                      }}</v-chip>
                    </div>
                  </td>

                  <td style="max-width: 100px">
                    <div>
                      <v-chip v-for="(to, i) in item.tos" :key="i" class="background ma-1" dark small>{{ to }}</v-chip>
                      <v-chip v-for="(cc, i) in item.ccs" :key="i" class="background ma-1" dark small>{{ cc }}
                        (Cc)</v-chip>

                      <v-chip v-for="(bcc, i) in item.bccs" :key="i" class="background ma-1" dark small>{{ bcc }}
                        (Bcc)</v-chip>
                    </div>
                  </td>
                  <td>
                    <v-menu bottom left>
                      <template v-slot:activator="{ on, attrs }">
                        <div class="text-center">
                          <v-btn dark-2 icon v-bind="attrs" v-on="on">
                            <v-icon>mdi-dots-vertical</v-icon>
                          </v-btn>
                        </div>
                      </template>
                      <v-list width="120" dense>
                        <v-list-item @click="editItem(item)">
                          <v-list-item-title style="cursor: pointer">
                            <v-icon color="secondary" small>
                              mdi-pencil
                            </v-icon>
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
                  </td>
                </tr>
              </table>
            </v-card-title>
          </v-card>
        </v-col> -->
      </v-row>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    datatable_search_textbox: '',
    filter_employeeid: '',
    snack: false,
    snackColor: '',
    snackText: '',

    dialog: false,
    color: "primary",
    endpoint: "report_notification",
    e1: 1,
    menu2: false,
    preloader: false,
    loading: false,
    response: false,
    id: "",
    snackbar: false,
    to: "",
    cc: "",
    bcc: "",
    number: "",
    message: "",
    payload: {
      report_types: [],
      mediums: [],
      frequency: null,
      time: null,
      tos: [],
      ccs: [],
      bccs: [],
    },
    data: [],
    options: {},
    errors: [],
    headers_table: [

      { text: "Subject", align: "left", sortable: true, key: 'title', value: "subject" },
      { text: "Frequency", align: "left", sortable: true, key: 'frequency', value: "frequency" },
      { text: "Time", align: "left", sortable: true, key: 'time', value: "time" },
      { text: "Medium", align: "left", sortable: false, key: 'medium', value: "medium" },
      { text: "Reports", align: "left", sortable: false, key: 'reports', value: "reports" },
      { text: "Recipients", align: "left", sortable: false, key: 'recipients', value: "recipients" },
      { text: "Actions", align: "left", sortable: false, key: 'action', value: "actions" },
    ]
  }),

  created() {
    this.preloader = false;
    this.id = this.$auth?.user?.company?.id;
    this.getDataFromApi();
  },
  methods: {
    datatable_save() {
    },
    datatable_cancel() {
      this.datatable_search_textbox = '';
    },
    datatable_open() {
      this.datatable_search_textbox = '';
    },
    datatable_close() {
      this.loading = false;
    },
    send() {
      // https://ezwhat.com/api/send.php?number=923108559858&type=text&message=test%20message&instance_id=64466B01B7926&access_token=a27e1f9ca2347bb766f332b8863ebe9f
      this.$axios.get(`https://ezwhat.com/api/send.php?number=${this.number}&type=text&message=${this.message}&instance_id=64466B01B7926&access_token=a27e1f9ca2347bb766f332b8863ebe9f`)
        .then(({ data }) => console.log(data));
    },
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },

    editItem(item) {
      this.$router.push("/report_notifications/" + item.id);
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

    add_to() {
      this.payload.tos.push(this.to);
      this.to = "";
    },
    add_cc() {
      this.payload.ccs.push(this.cc);
      this.cc = "";
    },
    add_bcc() {
      this.payload.bccs.push(this.bcc);
      this.bcc = "";
    },
    deleteTO(i) {
      this.payload.tos.splice(i, 1);
    },

    deleteCC(i) {
      this.payload.ccs.splice(i, 1);
    },

    deleteBCC(i) {
      this.payload.bccs.splice(i, 1);
    },
    getDataFromApi(url = this.endpoint, filter_column = '', filter_value = '') {

      if ((filter_column == 'serach_medium' || filter_column == 'serach_email_recipients') && filter_value != '' && filter_value.length <= 5) {

        this.snack = true;
        this.snackColor = 'error';
        this.snackText = 'Minimum 5 Characters to filter ';
        this.loading = false;
        return false;
      }
      this.loading = true;
      if (url == '') {
        url = this.endpoint;
      }
      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
          role_type: "employee",
        },
      };

      if (filter_column != '')
        options.params[filter_column] = filter_value;

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {

        if (filter_column != '' && data.data.length == 0) {
          this.snack = true;
          this.snackColor = 'error';
          this.snackText = 'No Results Found';
          this.loading = false;
          return false;
        }
        this.data = data.data;

        this.total = data.total;
        this.loading = false;
      });
    },
  },
};
</script>
<!-- <style scoped>
td,
th {
  border: 1px solid #dddddd;
  padding-left: 5px;
}

/* tr:nth-child(even) {
  background-color: #dddddd;
} */
</style>
<style scoped>
/* @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;500&display=swap'); */

* {
  box-sizing: border-box;
}

body>div {
  min-height: 100vh;
  display: flex;
  font-family: "Roboto", sans-serif;
}

.table_responsive {
  max-width: 900px;
  border: 1px solid #00bcd4;
  background-color: #efefef33;
  padding: 15px;
  overflow: auto;
  margin: auto;
  border-radius: 4px;
}

table {
  width: 100%;
  font-size: 13px;
  color: #444;
  white-space: nowrap;
  border-collapse: collapse;
}

table>thead {
  background-color: #00bcd4;
  color: #fff;
}

table>thead th {
  padding: 15px;
}

table th,
table td {
  border: 1px solid #00000017;
  padding: 10px 15px;
}

table>tbody>tr>td>img {
  display: inline-block;
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 50%;
  border: 4px solid #fff;
  box-shadow: 0 2px 6px #0003;
}

.action_btn {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.action_btn>a {
  text-decoration: none;
  color: #444;
  background: #fff;
  border: 1px solid;
  display: inline-block;
  padding: 7px 20px;
  font-weight: bold;
  border-radius: 3px;
  transition: 0.3s ease-in-out;
}

.action_btn>a:nth-child(1) {
  border-color: #26a69a;
}

.action_btn>a:nth-child(2) {
  border-color: orange;
}

.action_btn>a:hover {
  box-shadow: 0 3px 8px #0003;
}

table>tbody>tr {
  background-color: #fff;
  transition: 0.3s ease-in-out;
}

table>tbody>tr:nth-child(even) {
  background-color: rgb(238, 238, 238);
}

table>tbody>tr:hover {
  filter: drop-shadow(0px 2px 6px #0002);
}
</style>
