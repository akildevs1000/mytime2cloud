<template>
  <span>
    <style scoped>
      .center-both {
        height: 31vh;
        /* Adjust the height as needed */
        display: flex;
        align-items: center;
        justify-content: center;
      }

      @media (max-width: 500px) {
        .bordertop {
          border-top: 1px solid #ddd;
          padding-bottom: 5px;
          border-left: 0px;
        }
      }

      @media (max-width: 1500px) {
        .laptop-padding {
          padding-left: 30px;
        }
      }
    </style>

    <div class="bordertop">
      <v-row>
        <v-col md="8" sm="8" xs="8">
          <h4>Today Attendance</h4>
        </v-col>
        <v-col md="2" sm="2" xs="2" class="text-end">
          <!-- <v-icon color="green" @click="sendToWhatsapp()">mdi-whatsapp</v-icon> -->
        </v-col>
        <v-col md="2" sm="2" xs="2" class="text-end">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list width="120" dense>
              <v-list-item @click="viewLogs()">
                <v-list-item-title style="cursor: pointer">
                  View Logs
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </v-col>
      </v-row>
      <v-container fluid>
        <v-row align="center" v-for="(item, index) in data" :key="index">
          <v-col cols="2">
            <v-avatar :color="item.bgColor">
              <v-icon>{{ item.icon }}</v-icon>
            </v-avatar>
          </v-col>
          <v-col cols="6" :class="`text-h3 ${item.color}--text text-center`">
            {{ item.value }}
          </v-col>
          <v-col cols="4" class="text-right">{{ item.text }}</v-col>
        </v-row>
      </v-container>
    </div>
  </span>
</template>
<script>
export default {
  props: ["branch_id"],
  data: () => ({
    loading: false,
    dataLength: 0,
    response: "",
    data: null,
  }),
  watch: {
    branch_id() {
      this.$store.commit("dashboard/attendance_count", null);
      this.getDataFromApi();
    },
  },
  created() {
    setTimeout(() => {
      this.getDataFromApi();
    }, 1000 * 2);
  },

  methods: {
    goToReports() {
      this.$router.push("/attendance_report");
    },

    viewLogs() {
      this.$router.push("/attendance_report");
    },
    getDataFromApi() {
      this.$axios
        .get("dashbaord_short_view_count", {
          params: {
            company_id: this.$auth.user.company_id,
            branch_id: this.branch_id > 0 ? this.branch_id : null,
          },
        })
        .then(({ data }) => {
          this.data = data;
          this.$store.commit("dashboard/attendance_count", data);
        });
    },
  },
};
</script>
