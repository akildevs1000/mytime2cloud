<template>
  <div v-if="can(`dashboard_access`)">
    <div v-if="!loading">
      <v-dialog v-model="dialogGeneralreport" :fullscreen="false" max-width="1200px">
        <iframe v-if="iframeDisplay" :src="iframeUrl" frameborder="0" style="width: 100%; height: 600px"></iframe>
      </v-dialog>
      <v-row>
        <v-col md="12">
          <!-- <v-alert
          v-if="first_login && first_login_auth"
          outlined
          class="error lighten-1"
          dark
          dense
          prominent
        >
          <v-icon style="margin-top: -3px" size="22">
            mdi-alert-circle-outline</v-icon
          >
          <label class="text-white"
            >For security reasons you need to change your password after first
            login.</label
          >
          <nuxt-link class="white--text" to="/setting">
            <u>click here</u>
          </nuxt-link>
        </v-alert> -->
        </v-col>
        <v-col v-for="(i, index) in items" :key="index" xs="12" sm="12" cols="12" md="4" lg="4" xl="2">
          <div class="card p-2" :class="i.color" style="min-height: 168px">
            <div class="card-statistic-3">
              <div class="card-icon card-icon-large">
                <i :class="i.icon"></i>
              </div>
              <div class="card-content">
                <h4 class="card-title text-capitalize">{{ i.title }}</h4>
                <span class="data-1"> {{ i.value }}</span>
                <p class="mb-0 text-sm">
                  <span style="cursor: pointer" class="handcursor font-11" @click="showDialogGeneralreport(i.link)">
                    <span class="mr-2">
                      <v-icon dark small>mdi-arrow-right</v-icon>
                    </span>
                    <span class="text-nowrap regular-font">View General Report</span>
                  </span>
                </p>
                <p class="mb-0 text-sm">
                  <span style="cursor: pointer" class="handcursor font-11"
                    @click="showDialogGeneralreport(i.multi_in_out)">
                    <span class="mr-2">
                      <v-icon dark small>mdi-arrow-right</v-icon>
                    </span>
                    <span class="text-nowrap regular-font">View Multi In/Out Report</span>
                  </span>
                </p>
              </div>
            </div>
          </div>
        </v-col>
        <v-col xs="12" sm="12" cols="12" md="4" lg="4" xl="2">
          <div class="card p-2 l-bg-purple-dark" style="min-height: 168px">
            <div class="card-statistic-3">
              <div class="card-icon card-icon-large">
                <i class="fas mdi mdi-account-tie"></i>
              </div>
              <div class="card-content text-center">
                <h4 class="card-title text-capitalize">Total Employees</h4>
                <span class="data-1" style="font-size: 50px">
                  {{ total_employees_count_display }}</span>
              </div>
            </div>
          </div>
        </v-col>
        <v-col xs="12" sm="12" cols="12" md="4" lg="4" xl="2">
          <div class="card p-2" style="min-height: 168px; background-color: rgb(193 14 14 / 6%)">
            <div class="card-statistic-3" style="padding: 0px">
              <div class="card-content">
                <div class="card-icon card-icon-large">
                  <i class="mdi mdi-chart-arc d-lg-none d-xl-none"></i>
                </div>
                <PIE2 :items="items2" />
              </div>
            </div>
          </div>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" xs="12" sm="12" md="6" lg="6" xl="4">
          <DailyLogs />
        </v-col>
        <v-col cols="12" xs="12" sm="12" md="6" lg="6" xl="4">
          <PIE :items="items" />
        </v-col>

        <v-col cols="12" xs="12" sm="12" md="12" lg="12" xl="4">
          <v-card style="height: 500px">
            <v-toolbar color="primary" dark flat>
              <v-toolbar-title>Announcements</v-toolbar-title>
            </v-toolbar>
            <v-list style="min-height: 430px">
              <v-list-item v-for="(announcement, index) in announcements" :key="index">
                <v-list-item-content>
                  <v-list-item-title>{{
                    announcement.title
                  }}</v-list-item-title>
                  <v-list-item-subtitle>{{ getExcerpt(announcement.description, 30) }}&nbsp;
                    <v-chip x-small color="background" dark @click="openDialog(announcement)">Read More
                      <v-icon x-small>mdi-chevron-right</v-icon></v-chip>
                  </v-list-item-subtitle>
                  <v-list-item-subtitle>When:
                    <b class="primary--text" v-if="getCurrentDate == announcement.start_date">{{ announcement.start_date
                    }}</b>
                    <span v-else>{{ announcement.start_date }}</span>
                    -
                    <b class="primary--text" v-if="getCurrentDate == announcement.end_date">{{ announcement.end_date
                    }}</b>
                    <span v-else>{{ announcement.end_date }}</span>
                  </v-list-item-subtitle>
                  <v-divider></v-divider>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-card class="mx-auto" max-width="500">
              <div class="text-center">
                <v-dialog v-model="dialog" width="600">
                  <v-card>
                    <v-card-title class="text-h5 primary white--text">
                      Announcement Detail
                      <v-spacer></v-spacer>
                      <v-icon color="background" dark @click="dialog = false">mdi-close</v-icon>
                    </v-card-title>

                    <v-card-text class="mt-3">
                      <table style="
                          font-family: arial, sans-serif;
                          border-collapse: collapse;
                          width: 100%;
                        ">
                        <tr>
                          <th style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            Title
                          </th>
                          <td style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            {{ dialogData.title }}
                          </td>
                        </tr>
                        <tr>
                          <th style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            Description
                          </th>
                          <td style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            {{ dialogData.description }}
                          </td>
                        </tr>
                        <tr>
                          <th style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            Departments
                          </th>
                          <td style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            <v-chip class="primary mx-1" x-small v-for="(
                                department, dIndex
                              ) in dialogData.departments" :key="dIndex">{{ department.name }}</v-chip>
                          </td>
                        </tr>
                        <tr>
                          <th style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            Employees
                          </th>
                          <td style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            <v-chip class="primary mx-1" x-small v-for="(employee, eIndex) in dialogData.employees"
                              :key="eIndex">{{ employee.display_name }}</v-chip>
                          </td>
                        </tr>
                        <tr>
                          <th style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            When
                          </th>
                          <td style="
                              border: 1px solid #dddddd;
                              text-align: left;
                              padding: 8px;
                            ">
                            <b class="primary--text" v-if="getCurrentDate == dialogData.start_date">{{
                              dialogData.start_date }}</b>
                            <span v-else>{{ dialogData.start_date }}</span>
                            -
                            <b class="primary--text" v-if="getCurrentDate == dialogData.end_date">{{ dialogData.end_date
                            }}</b>
                            <span v-else>{{ dialogData.end_date }}</span>
                          </td>
                        </tr>
                      </table>
                    </v-card-text>
                  </v-card>
                </v-dialog>
              </div>
            </v-card>
          </v-card>
        </v-col>
        <!-- <v-col cols="12" md="4" xl="4">
          <v-card flat class="w-100">
          <PIE :items="items" />
          </v-card>
        </v-col> -->
      </v-row>

      <v-row class="mt-4">
        <v-col md="12" cols="12" sm="12">
          <v-card elevation="0">
            <ComboChart />
          </v-card>
        </v-col>
      </v-row>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data() {
    return {
      dialog: false,
      dialogData: {},
      testing: [
        { header: "Today" },
        {
          avatar: "https://cdn.vuetifyjs.com/images/lists/1.jpg",
          title: "Brunch this weekend?",
          subtitle: `<span class="font-weight-bold">Ali Connors</span> &mdash; I'll be in your neighborhood doing errands this weekend. Do you want to hang out?`,
        },
        { divider: true, inset: true },
        {
          avatar: "https://cdn.vuetifyjs.com/images/lists/2.jpg",
          title: 'Summer BBQ <span class="grey--text text--lighten-1">4</span>',
          subtitle: `<span class="font-weight-bold">to Alex, Scott, Jennifer</span> &mdash; Wish I could come, but I'm out of town this weekend.`,
        },
        { divider: true, inset: true },
        {
          avatar: "https://cdn.vuetifyjs.com/images/lists/3.jpg",
          title: "Oui oui",
          subtitle:
            '<span class="font-weight-bold">Sandra Adams</span> &mdash; Do you have Paris recommendations? Have you ever been?',
        },
      ],

      cards: [
        {
          id: 1,
          title: "Card 1",
          description: "This is the description of Card 1",
          image: "path/to/image1.jpg",
        },
        {
          id: 2,
          title: "Card 2",
          description: "This is the description of Card 2",
          image: "path/to/image2.jpg",
        },
        {
          id: 3,
          title: "Card 3",
          description: "This is the description of Card 3",
          image: "path/to/image3.jpg",
        },
      ],
      currentValue: 0,
      targetValue: 100, // The target value for the animation
      animationDuration: 4000, // Animation duration in milliseconds
      total_employees_count_api: "",
      total_employees_count_display: "0",
      first_login_auth: 1,
      loading: true,
      dialogGeneralreport: false,
      generalreportIframeSrc: "",
      iframeDisplay: false,
      logs: [],
      iframeUrl: "",
      items2: [],
      online_device_count: 0,
      offline_device_count: 0,
      total_items: [],
      items_by_cities: [],
      test_headers: [
        {
          text: "Customer",
          align: "left",
          sortable: false,
          value: "company_name",
        },
        {
          text: "Order Total",
          align: "left",
          sortable: false,
          value: "order_total",
        },
      ],
      orders: "",
      products: "",
      customers: "",
      daily_orders: "",
      weekly_orders: "",
      monthly_orders: "",
      items: [],
      chartData: [
        ["Month", "On Time", "Absence", "Lates"],
        ["Apr", 33, 4, 7],
        ["Mar", 17, 6, 3],
        ["Feb", 41, 9, 1],
      ],
      chartOptions: {
        chart: {
          title: "Company Performance",
          subtitle: "Sales, Expenses, and Profit: 2014-2017",
        },
      },
      BublechartOptions: {
        colorAxis: { colors: ["yellow", "red"] },
      },
      options: {},
      series: [44, 55, 41, 17, 15],
      selected: [2],
      announcements: [],
    };
  },
  created() {
    this.updateCartcart2();
    this.initialize();
    this.get_announcements();
    this.first_login_auth = this.$auth.user.first_login;

    // this.verifyLeaveNotifications();

    // this.generalreportIframeSrc=  this.$axios.defaults.baseURL +
    //     "daily?company_id=8&status=SA&daily_date=2023-05-31&department_id=-1&report_type=Daily",
  },
  mounted() {
    // this.verifyLeaveNotifications();

    // setInterval(() => {
    //   console.log("socketConnectionStatus", this.socketConnectionStatus);
    //   if (this.socketConnectionStatus != 1) {
    //     //socket connection is closed
    //     this.verifyLeaveNotifications();
    //   }
    // }, 1000 * 60);
    //this.updateCartcart2();

    //this.animateNumberEmployeesCount();
  },
  computed: {
    first_login() {
      return this.$store.state.first_login;
    },
    getCurrentDate() {
      // Get the current date
      const date = new Date();

      // Get the year, month, and day from the date object
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");

      return `${year}-${month}-${day}`;
    },
  },
  filters: {
    get_decimal_value: function (value) {
      if (!value) return "";
      return (Math.round(value * 100) / 100).toFixed(2);
    },
    get_comma_seperator: function (x) {
      if (!x) return "";
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
  },
  methods: {
    // verifyLeaveNotifications() {
    //   // 0	CONNECTING	Socket has been created.The connection is not yet open.
    //   // 1	OPEN	The connection is open and ready to communicate.
    //   // 2	CLOSING	The connection is in the process of closing.
    //   // 3	CLOSED

    //   let company_id = this.$auth.user.company.id;
    //   console.log("1", process.env.ADMIN_LEAVE_NOTIFICATION_SOCKET_ENDPOINT);
    //   console.log("2", process.env.SOCKET_ENDPOINT);

    //   // if (!process.env.ADMIN_LEAVE_NOTIFICATION_SOCKET_ENDPOINT) return false; //process.env.SOCKET_ENDPOINT
    //   this.socket = new WebSocket(process.env.ADMIN_LEAVE_NOTIFICATION_SOCKET_ENDPOINT);

    //   this.socket.onopen = function () {
    //     this.socketConnectionStatus = this.socket.readyState;

    //     const data = {
    //       company_id: company_id,
    //     };
    //     this.socket.send(JSON.stringify(data)); // this works
    //   };
    //   this.socket.onclose = function () {
    //     this.socketConnectionStatus = 0;
    //   };
    //   this.socket.onmessage = ({ data }) => {
    //     data = JSON.parse(data);
    //     console.log("Socket", data);
    //     if (data.status && data.new_leaves_data[0]) {
    //       let element = data.new_leaves_data[0];
    //       //data.new_leaves_data.data.forEach(element => {
    //       console.log("Notification Content", element);

    //       this.snackNotification = true;
    //       this.snackNotificationText =
    //         "New Leave Notification - From : " +
    //         element.first_name +
    //         " " +
    //         element.last_name;
    //       console.log(this.snackNotificationText);
    //     }
    //     this.pendingLeavesCount = data.total_pending_count;
    //   };
    // },
    openDialog(announcement) {
      this.dialogData = announcement;
      this.dialog = true;
    },
    getExcerpt(text, maxLength) {
      if (text.length <= maxLength) {
        return text;
      } else {
        return text.substring(0, maxLength) + "...";
      }
    },
    animateNumberEmployeesCount() {
      const startTimestamp = Date.now();
      const startValue = this.currentValue;

      const animate = () => {
        const elapsed = Date.now() - startTimestamp;

        if (elapsed >= this.animationDuration) {
          // Animation finished, set the final value
          this.total_employees_count_display = this.total_employees_count_api;
        } else {
          // Calculate the animated value based on time and start/target values
          const progress = elapsed / this.animationDuration;
          this.total_employees_count_display = Math.round(
            startValue +
            (this.total_employees_count_api - startValue) * progress
          );

          // Request next animation frame
          requestAnimationFrame(animate);
        }
      };

      // Start the animation
      requestAnimationFrame(animate);
    },

    updateLink(url) {
      if (
        this.$axios.defaults.baseURL !=
        "https://stagingbackend.ideahrms.com/api"
      ) {
        url = url.replace(
          "https://stagingbackend.ideahrms.com/api",
          this.$axios.defaults.baseURL
        );
      }

      return url;
    },
    closeDialogGeneralreport() {
      this.iframeDisplay = false;
      this.dialogGeneralreport = false;
      //this.iframeUrl = "#";
    },
    showDialogGeneralreport(iframeUrl) {
      this.iframeDisplay = false;
      this.iframeUrl = this.updateLink(iframeUrl);
      this.dialogGeneralreport = true;

      this.iframeDisplay = true;
    },
    can(per) {
      let { is_master, permissions: p } =
        this.$auth.user || this.$auth.user.permissions;

      if (p.some((e) => e == per) || is_master) return true;

      // if (this.$auth.user.user_type == "employee") {
      //   this.$router.push(`/employee_dashboard`);
      //   return;
      // }

      this.$router.push(`/attendance_report`);
    },
    updateCartcart2() {
      this.$axios
        .get(`getdevicesstatuscount/${this.$auth.user.company.id}`)
        .then((res) => {
          //this.logs = res.data;
          this.online_device_count = res.data.online;
          this.offline_device_count = res.data.offline;

          this.items2 = [
            {
              title: "Active",
              value: res.data.online,
            },
            {
              title: "Offline",
              value: res.data.offline,
            },
          ];
        });
    },
    initialize() {
      let options = {
        company_id: this.$auth.user.company.id,
      };
      this.$axios.get(`count`, { params: options }).then(({ data }) => {
        this.items = data;
        this.total_employees_count_api = data[0].total_employees_count;
        if (this.items.length > 0) {
          this.loading = false;
        }
      });
    },
    get_announcements() {
      let options = {
        per_page: 1000,
        company_id: this.$auth.user.company.id,
      };
      this.$axios
        .get(`announcement_list`, { params: options })
        .then(({ data }) => {
          this.announcements = data.data;
        });
    },
  },
};
</script>
<style scoped src="@/assets/dashboard.css"></style>
