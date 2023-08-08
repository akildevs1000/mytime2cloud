<template>
  <v-container fluid>
    <v-row>
      <v-col
        v-for="(card, index) in cardData"
        :key="index"
        :cols="card.cols"
        :sm="card.sm"
        :md="card.md"
      >
        <v-card
          :color="card.color"
          dark
          dense
          style="border-radius: 15px !important"
        >
          <div class="text-right px-2">
            <v-icon small @click="goToThemeEditor">mdi-pencil</v-icon>
          </div>
          <div class="text-center pa-5">
            <h1>{{ card.value }}</h1>
            <p>{{ card.title }}</p>
          </div>
        </v-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12" xs="12" sm="12" md="4" lg="4" xl="4">
        <AttendancePIE />
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4" lg="4" xl="4">
        <Announcement />
      </v-col>
      <v-col cols="12" xs="12" sm="12" md="4" lg="4" xl="4">
        <DevicePIE />
      </v-col>
      <v-col cols="12">
        <DailyLog />
      </v-col>
      <v-col cols="12">
        <AttendanceLog />
        <Device />
        <ReportNotification />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Announcement from "../components/widgets/Announcement.vue";
import AttendanceLog from "../components/widgets/AttendanceLog.vue";
import Device from "../components/widgets/Device.vue";
import ReportNotification from "../components/widgets/ReportNotification.vue";
import AttendancePIE from "../components/widgets/AttendancePIE.vue";
import DevicePIE from "../components/widgets/DevicePIE.vue";
import DailyLog from "../components/widgets/DailyLog.vue";
export default {
  components: {
    DailyLog,
    Announcement,
    AttendanceLog,
    Device,
    ReportNotification,
    AttendancePIE,
    DevicePIE,
  },
  data() {
    return {
      editDialog: false,
      editedCard: {
        title: "New Card",
        value: "0",
        color: "#4A79DBED",
        icon: "mdi mdi-account",
        cols: "12",
        sm: "6",
        md: "3",
      },
      editIndex: null,

      cardData: [
        // {
        //   title: "Total Employees",
        //   value: 5,
        //   color: "#FF0000FF",
        //   icon: "mdi mdi-account",
        //   cols: "12",
        //   sm: "6",
        //   md: "3",
        // },
        // {
        //   title: "Present",
        //   value: 5,
        //   color: "#004BE4ED",
        //   icon: "mdi mdi-book",
        //   cols: "12",
        //   sm: "6",
        //   md: "3",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "green",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Employees",
        //   value: 5,
        //   color: "yellow",
        //   icon: "mdi mdi-account",
        // },
        // {
        //   title: "Total Present",
        //   value: 5,
        //   color: "orange",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "purple",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "brown",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "teal",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "pink",
        //   icon: "mdi mdi-book",
        // },
      ],
    };
  },
  created() {
    this.getRecord();
  },
  methods: {
    getRecord() {
      let payload = {
        page: "dashboard2",
        type: "card",
        company_id: this.$auth.user.company.id,
      };

      let options = {
        params: payload,
      };

      this.$axios
        .get("theme", options)
        .then(({ data }) => {
          this.cardData = data;
        })
        .catch((e) => console.log(e));
    },
    addCard() {
      this.cardData.push(this.editedCard);
    },

    goToThemeEditor() {
      // theme/8
      this.$router.push(`theme/${this.$auth.user.company.id}`);
    },
    deleteCard(index) {
      this.cardData.splice(index, 1);
    },
    reflectChange() {
      this.cardData[this.editIndex] = { ...this.editedCard };
    },
    saveEdit() {
      this.reflectChange();

      let payload = {
        page: "dashboard",
        type: "card",
        style: this.cardData,
        company_id: this.$auth.user.company.id,
      };

      this.$axios
        .post("theme", payload)
        .then(({ data }) => {
          alert("Data inserted");
          this.closeEdit();
        })
        .catch((e) => console.log(e));
    },
    closeEdit() {
      this.editDialog = false;
      this.editedCard = {
        title: "New Card",
        value: "0",
        color: "#4A79DBED",
        icon: "mdi mdi-account",
        cols: "12",
        sm: "6",
        md: "3",
      };
      this.editIndex = null;
    },
  },
};
</script>
