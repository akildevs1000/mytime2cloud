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
    <v-text-field
      class="pb-4"
      :hide-details="!payload.subject"
      v-model="payload.subject"
      placeholder="Subject"
      outlined
      dense
      label="Subject"
    ></v-text-field>
    <span v-if="errors && errors.subject" class="error--text"
      >{{ errors.subject[0] }}
    </span>

    <div
      style="
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
        align-items: center;
        justify-content: center;
      "
    >
      <!-- Center each day in a circle and style accordingly -->
      <div
        class="white--text text-center"
        style="
          border-radius: 50%;
          height: 30px;
          width: 30px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 12px;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        "
        v-for="day in days"
        :key="day.id"
        fab
        dark
        :class="{
          primary: payload.days.includes(day.id),
          grey: !payload.days.includes(day.id),
        }"
        @click="toggleSelection(day.id)"
      >
        <span>{{ day.name.charAt(0) }}</span>
      </div>
    </div>

    <span v-if="errors && errors.days" class="error--text">{{
      errors.days[0]
    }}</span>

    <v-dialog
      ref="fromDialogRef"
      v-model="fromDialog"
      :return-value.sync="payload.from_time"
      persistent
      width="290px"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-text-field
          v-model="payload.from_time"
          label="From Time"
          append-icon="mdi-clock-time-four-outline"
          prepend-icon=""
          class="mt-5"
          outlined
          dense
          readonly
          v-bind="attrs"
          v-on="on"
          hide-details
        ></v-text-field>
      </template>
      <v-time-picker
        dense
        format="24hr"
        v-if="fromDialog"
        v-model="payload.from_time"
        full-width
        @click:minute="$refs.fromDialogRef.save(payload.from_time)"
      />
    </v-dialog>

    <span v-if="errors && errors.from_time" class="error--text">{{
      errors.from_time[0]
    }}</span>

    <v-dialog
      ref="toDialogRef"
      v-model="toDialog"
      :return-value.sync="payload.to_time"
      persistent
      width="290px"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-text-field
          v-model="payload.to_time"
          label="To Time"
          append-icon="mdi-clock-time-four-outline"
          prepend-icon=""
          class="mt-5"
          outlined
          dense
          readonly
          v-bind="attrs"
          v-on="on"
          hide-details
        ></v-text-field>
      </template>
      <v-time-picker
        dense
        format="24hr"
        v-if="toDialog"
        v-model="payload.to_time"
        full-width
        @click:minute="$refs.toDialogRef.save(payload.to_time)"
      />
    </v-dialog>

    <span v-if="errors && errors.to_time" class="error--text">{{
      errors.to_time[0]
    }}</span>

    <v-divider></v-divider>

    <v-row dense class="pt-3">
      <v-col cols="6" class="pa-1 ma-0">
        <v-switch v-model="email" label="Email"></v-switch>
      </v-col>
      <v-col cols="6" class="pa-1 align-end">
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
import {
  TiptapVuetify,
  Heading,
  Bold,
  Italic,
  Strike,
  Underline,
  Paragraph,
  BulletList,
  OrderedList,
  ListItem,
  Blockquote,
  History,
} from "tiptap-vuetify";

export default {
  props: ["dialogNew", "editItemPayload", "type"],
  components: { TiptapVuetify },

  data: () => ({
    fromDialog: false,
    toDialog: false,

    daysNumaric: [],
    managers: [],
    time_in_menu: false,
    menu: false,
    days: [
      { id: 1 + "", name: "M" },
      { id: 2 + "", name: "T" },
      { id: 3 + "", name: "W" },
      { id: 4 + "", name: "Th" },
      { id: 5 + "", name: "F" },
      { id: 6 + "", name: "Sa" },
      { id: 0 + "", name: "Su" },
    ],
    extensions: [
      History,
      Blockquote,
      Underline,
      Strike,
      Italic,
      ListItem,
      BulletList,
      OrderedList,
      [
        Heading,
        {
          options: {
            levels: [1, 2, 3],
          },
        },
      ],
      Bold,
      Paragraph,
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
      days: [],
      day: 1,
      reports: [],
      mediums: [],
      frequency: "Weekly",
      time: null,
      tos: [],
      ccs: [],
      bccs: [],
      date: new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
        .toISOString()
        .substr(0, 10),
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
    let reports = [
      "daily_summary.pdf",
      "daily_present.pdf",
      "daily_absent.pdf",
      "daily_missing.pdf",
      "daily_manual.pdf",
    ];
    this.payload.reports = reports;
    this.add();

    if (this.editItemPayload) {
      this.payload.branch_id = this.editItemPayload.branch_id;

      this.payload.day = this.editItemPayload.day;
      this.payload.frequency = this.editItemPayload.frequency;

      this.payload.reports = this.editItemPayload.reports;

      this.payload.time = this.editItemPayload.time;

      this.payload.from_time = this.editItemPayload.from_time;
      this.payload.to_time = this.editItemPayload.to_time;
      this.payload.days = this.editItemPayload.days;


      this.payload.date = this.editItemPayload.date;
      this.payload.company_id = this.editItemPayload.company_id;
      this.payload.branch_id = this.editItemPayload.branch_id;
      this.payload.subject = this.editItemPayload.subject;
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
    toggleSelection(dayId) {
      const index = this.payload.days.indexOf(dayId);
      if (index === -1) {
        this.payload.days.push(dayId);
      } else {
        this.payload.days.splice(index, 1);
      }
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

      this.payload.day = day;
      let reports = [];
      if (frequency == "Daily") {
        reports = [
          "daily_summary.pdf",
          "daily_present.pdf",
          "daily_absent.pdf",
          "daily_missing.pdf",
          "daily_manual.pdf",
        ];
      } else if (frequency == "Weekly") {
        reports = [
          "weekly_summary.pdf",
          "weekly_present.pdf",
          "weekly_absent.pdf",
          "weekly_missing.pdf",
          "weekly_manual.pdf",
        ];
      } else if (frequency == "Monthly") {
        reports = [
          "monthly_summary.pdf",
          "monthly_present.pdf",
          "monthly_absent.pdf",
          "monthly_missing.pdf",
          "monthly_manual.pdf",
        ];
      }

      this.payload.reports = reports;
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
    async setWhatsappMessage() {
      try {
        // Retrieve clientId from localStorage
        const clientId = localStorage.getItem("clientId");

        if (!clientId) {
          console.error("Client ID not found in localStorage.");
          return;
        }

        const endpoint = `https://wa.mytime2cloud.com/send-message`;
        const payload = {
          clientId,
          recipient: "971554501483",
          text: `Message to client ${clientId}`,
        };

        // Make the API request
        const { data } = await this.$axios.post(endpoint, payload);

        console.log("🚀 ~ setWhatsappMessage ~ Response Data:", data);
      } catch (error) {
        // Handle errors during API call
        console.error("Error in setWhatsappMessage:", error.message);
      }
    },

    store() {
      // this.setWhatsappMessage();
      // return;
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

      this.payload.type = this.type;

      if (this.editItemPayload) {
        this.$axios
          .put("/report_notification/" + this.editItemPayload.id, this.payload)
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
          .post("/report_notification", this.payload)
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
  },
};
</script>
