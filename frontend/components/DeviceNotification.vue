<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-autocomplete
      class="pb-0"
      v-model="payload.branch_id"
      :items="branchesList"
      dense
      placeholder="Select Branch"
      outlined
      item-value="id"
      item-text="branch_name"
      label="Branch"
    >
    </v-autocomplete>

    <span
      v-if="errors && errors.branch_id && errors.branch_id[0]"
      class="error--text"
      >{{ errors.branch_id[0] }}</span
    >

    <!-- <v-divider></v-divider>
    <v-text-field
      class="pb-4"
      :hide-details="!payload.subject"
      v-model="payload.subject"
      placeholder="Title"
      outlined
      dense
      label="Title"
    ></v-text-field>
    <span v-if="errors && errors.subject" class="error--text"
      >{{ errors.subject[0] }}
    </span> -->
    <v-row dense class="pt-3">
      <!-- <label class="col-form-label pt-3 pr-3"><b>Medium </b></label> -->

      <v-col cols="6" class="pa-1 ma-0">
        <!-- <v-checkbox
          dense
          v-model="payload.mediums"
          label="Email"
          value="Email"
        ></v-checkbox> -->
        <v-switch v-model="email" label="Email"></v-switch>
      </v-col>
      <v-col cols="6" class="pa-1 align-end">
        <!-- <v-checkbox
          dense
          v-model="payload.mediums"
          label="Whatsapp"
          value="Whatsapp"
        ></v-checkbox> -->
        <v-switch v-model="whatsapp" label="Whatsapp"></v-switch>
      </v-col>
      <v-col cols="12" class="pa-0 ma-0">
        <span v-if="errors && errors.mediums" class="error--text">{{
          errors.mediums[0]
        }}</span>
      </v-col>
    </v-row>
    <v-divider></v-divider>
    <v-row class="pt-3">
      <v-col md="6"><b>Add Manager(s)</b></v-col>
    </v-row>

    <div v-for="(item, index) in managers" :key="index">
      <v-text-field
        dense
        outlined
        v-model="item.name"
        label="Name"
      ></v-text-field>

      <v-text-field
        dense
        outlined
        type="email"
        v-model="item.email"
        label="Email"
      ></v-text-field>

      <v-text-field
        dense
        outlined
        v-model="item.whatsapp_number"
        label="Whatsapp Number"
      ></v-text-field>

      <v-row>
        <v-col md="6" class="pa-0"> <v-divider></v-divider></v-col>

        <v-col md="6" class="pa-0 text-end" style="margin-top: -10px">
          <v-icon @click="removeItem(index)" title="Delete"
            >mdi-trash-can-outline</v-icon
          >
          <v-icon
            v-if="index == managers.length - 1"
            title="Add - Maximum 3 managers"
            color="black"
            :disabled="managers.length >= 3"
            @click="add"
            >mdi-plus-circle</v-icon
          >
        </v-col>
      </v-row>

      <v-col md="12"
        ><span v-if="errors && errors.managers" class="error--text">{{
          errors.managers[0]
        }}</span>
      </v-col>
    </div>

    <v-card-actions class="mt-5">
      <v-spacer></v-spacer>

      <v-btn :disabled="!managers.length" class="primary" small @click="store">
        {{ editItemPayload ? "Update" : "Save" }}</v-btn
      >
    </v-card-actions>
  </div>
</template>

<script>
import TimePickerCommon from "../components/Snippets/TimePickerCommon.vue";

export default {
  props: ["dialogNew", "editItemPayload"],
  components: { TimePickerCommon },

  data: () => ({
    daysNumaric: [],
    managers: [],
    time_in_menu: false,
    menu: false,
    days: [
      { id: 1 + "", name: "Monday" },
      { id: 2 + "", name: "Tuesday" },
      { id: 3 + "", name: "Wednesday" },
      { id: 4 + "", name: "Thursday" },
      { id: 5 + "", name: "Friday" },
      { id: 6 + "", name: "Saturday" },
      { id: 0 + "", name: "Sunday" },
    ],

    // starting editor's content
    content: `
        <h1>Yay Headlines!</h1>
        <p>All these <strong>cool tags</strong> are working now.</p>
          `,
    color: "primary",
    e1: 1,
    menu2: false,
    preloader: false,
    loading: false,
    response: false,
    id: "",
    snackbar: false,
    to: "",

    number: "",
    cc: "",
    bcc: "",
    email: "",
    whatsapp: "",
    payload: {
      mediums: [],

      company_id: 0,
    },

    errors: [],
    branchesList: [],
    branch_id: "",
  }),

  created() {
    for (let i = 1; i <= 31; i++) {
      this.daysNumaric.push({ id: i + "", name: i + "" });
    }
    console.log(this.editItemPayload);
    this.preloader = false;

    this.$axios
      .get(`branches_list`, {
        params: {
          per_page: 1000,
          company_id: this.$auth.user.company_id,
        },
      })
      .then(({ data }) => {
        this.branchesList = data;
        this.branch_id = this.$auth.user.branch_id || "";
      });
    this.payload.company_id = this.$auth?.user?.company?.id;
    // let reports = [
    //   "daily_summary.pdf",
    //   "daily_present.pdf",
    //   "daily_absent.pdf",
    //   "daily_missing.pdf",
    //   "daily_manual.pdf",
    // ];
    // this.payload.reports = reports;
    this.add();

    if (this.editItemPayload) {
      this.payload.branch_id = this.editItemPayload.branch_id;

      //this.payload.day = this.editItemPayload.day;
      //this.payload.frequency = this.editItemPayload.frequency;

      //this.payload.reports = this.editItemPayload.reports;

      // this.payload.time = this.editItemPayload.time;

      // this.payload.date = this.editItemPayload.date;
      this.payload.company_id = this.editItemPayload.company_id;
      this.payload.branch_id = this.editItemPayload.branch_id;
      // this.payload.subject = this.editItemPayload.subject;
      this.managers = this.editItemPayload.managers;
      this.email = this.editItemPayload.mediums.includes("Email")
        ? "Email"
        : "";

      this.whatsapp = this.editItemPayload.mediums.includes("Whatsapp")
        ? "Whatsapp"
        : "";

      if (this.managers.length == 0) {
        this.add();
      }
    }
  },
  methods: {
    set_date_save(from_menu, field) {
      from_menu.save(field);
    },
    add() {
      if (this.managers.length >= 3) {
        this.snackbar = true;
        this.response = "Maximum 3 managers";
        return false;
      }
      this.managers.push({
        name: "",
        email: "",
        whatsapp_number: "",
      });
    },
    removeItem(index) {
      this.managers.splice(index, 1);
    },

    close() {
      this.$emit("close-dialog");
    },
    setDay() {
      let { frequency, day, date } = this.payload;

      // if (frequency == "Monthly") {
      //   day = new Date(date).getDate();
      // }

      // this.payload.day = day;
      // let reports = [];
      // if (frequency == "Daily") {
      //   reports = [
      //     "daily_summary.pdf",
      //     "daily_present.pdf",
      //     "daily_absent.pdf",
      //     "daily_missing.pdf",
      //     "daily_manual.pdf",
      //   ];
      // } else if (frequency == "Weekly") {
      //   reports = [
      //     "weekly_summary.pdf",
      //     "weekly_present.pdf",
      //     "weekly_absent.pdf",
      //     "weekly_missing.pdf",
      //     "weekly_manual.pdf",
      //   ];
      // } else if (frequency == "Monthly") {
      //   reports = [
      //     "monthly_summary.pdf",
      //     "monthly_present.pdf",
      //     "monthly_absent.pdf",
      //     "monthly_missing.pdf",
      //     "monthly_manual.pdf",
      //   ];
      // }

      // this.payload.reports = reports;
    },
    onScroll() {
      this.scrollInvoked++;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    add_number() {
      if (this.number && this.number.length > 10) {
        this.payload.numbers.push(this.number);
        this.number = "";
      }
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

    deleteNumber(i) {
      this.payload.numbers.splice(i, 1);
    },

    deleteCC(i) {
      this.payload.ccs.splice(i, 1);
    },

    deleteBCC(i) {
      this.payload.bccs.splice(i, 1);
    },

    store() {
      this.payload.managers = this.managers.filter(
        (e) => e.email != "" && e.name != ""
      );

      this.payload.mediums = [];
      if (this.email) {
        this.payload.mediums.push("Email");
      }
      if (this.whatsapp) {
        this.payload.mediums.push("Whatsapp");
      }

      this.managers.forEach((element) => {
        element.company_id = this.$auth.user.company_id;
        element.branch_id = this.payload.branch_id;
      });

      if (this.editItemPayload) {
        this.$axios
          .put("/device_notifications/" + this.editItemPayload.id, this.payload)
          .then(({ data }) => {
            this.loading = false;
            this.$emit("getDataFromApi");
            if (!data.status) {
              this.errors = data.errors;
              return;
            }

            this.snackbar = data.status;
            this.response = data.message;

            setTimeout(() => {
              this.$emit("closePopup", data);
            }, 500);
          })
          .catch((e) => console.log(e));
      } else {
        this.$axios
          .post("/device_notifications", this.payload)
          .then(({ data }) => {
            this.loading = false;

            this.$emit("getDataFromApi");
            if (!data.status) {
              this.errors = data.errors;
              return;
            }

            this.snackbar = data.status;
            this.response = data.message;

            setTimeout(() => {
              this.$emit("closePopup", data);
            }, 500);
          })
          .catch((e) => console.log(e));
      }
    },
    test_endpoint() {
      // /test/whatsapp
      this.$axios.get("/test/whatsapp").then((res) => {});
    },
    test() {
      var axios = require("axios");
      // var data = JSON.stringify({
      //   messaging_product: "whatsapp",
      //   recipient_type: "individual",
      //   to: "923108559858",
      //   type: "text",
      //   text: {
      //     preview_url: false,
      //     body: "contect"
      //   }
      //   // type: "text",
      //   // text: {
      //   //   // the text object
      //   //   preview_url: false,
      //   //   body: "sdfsdf"
      //   // }
      // });

      var data = JSON.stringify({
        messaging_product: "whatsapp",
        to: "923108559858",
        type: "template",
        template: {
          name: "automated_reports",
          language: {
            code: "en",
          },
        },
      });

      var config = {
        method: "post",
        url: "https://graph.facebook.com/v14.0/102482416002121/messages",
        headers: {
          "Content-Type": "application/json",
          Authorization:
            "Bearer EAAP9IfKKSo0BAGDS96w2XuYjjpXIqxZBAOcwzlFWecCxODjNO3ruEcbnZCkmHSWNAGNf1Q9wC2uwe5XnyxteTOYAO3l9wgy4iu9L6wwYgtZBZAygXV3Tc4euoYANOZCFlvMAsnNz7vNQEYUYdL56l9poliM3eS6ZCZBV4dMzJhKEQKDbUTZB2ZBvEVl2mlHvSj8dCWgITF8e9GFkTXO8isMsx",
        },
        data: data,
      };

      axios(config)
        .then(function (response) {})
        .catch(function (error) {
          console.log(error);
        });
    },
  },
};
</script>
<!-- <style>
.tiptap-vuetify-editor__content {
  min-height: 400px !important;
}

.ProseMirror .ProseMirror-focused {
  height: 400px !important;
}

.tiptap-icon .v-icon {
  color: white !important;
}

.tiptap-icon .v-btn--icon {
  color: white !important;
}
</style> -->
