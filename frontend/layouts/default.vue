<template>
  <v-app>
    <v-navigation-drawer
      v-model="drawer"
      dark
      :mini-variant="miniVariant"
      :clipped="clipped"
      fixed
      app
      :color="sideBarcolor"
      :style="miniVariant ? 'width: 60px' : ''"
      @transitionend="collapseSubItems"
    >
      <br />
      <v-list v-for="(i, idx) in items" :key="idx" style="padding: 5px 0 0 0px">
        <v-list-item
          :to="i.to"
          router
          v-if="!i.hasChildren"
          :class="!miniVariant || 'pl-2'"
        >
          <v-list-item-icon class="ma-2">
            <v-icon>{{ i.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-title> {{ i.title }}&nbsp; </v-list-item-title>
        </v-list-item>
        <v-list-item
          v-else
          :class="!miniVariant || 'pl-2'"
          @click="i.open_menu = !i.open_menu"
        >
          <v-list-item-icon class="mx-2">
            <v-icon>{{ i.icon }}</v-icon>
            <v-icon v-if="miniVariant" small
              >{{ !i.open_menu ? "mdi-chevron-down" : "mdi-chevron-up" }}
            </v-icon>
          </v-list-item-icon>

          <v-list-item-title>{{ i.title }} </v-list-item-title>
          <v-icon small
            >{{ !i.open_menu ? "mdi-chevron-down" : "mdi-chevron-up" }}
          </v-icon>
        </v-list-item>
        <div v-if="i.open_menu">
          <div v-for="(j, jdx) in i.hasChildren" :key="jdx">
            <v-tooltip
              style="margin-left: 25px"
              v-if="miniVariant"
              right
              color="primary"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-list-item
                  v-bind="attrs"
                  v-on="on"
                  style="min-height: 0"
                  :to="j.to"
                  class="submenutitle"
                >
                  <v-list-item-title class="my-2">
                    {{ j.title }}
                  </v-list-item-title>

                  <v-list-item-icon
                    :style="miniVariant ? 'margin-left: -54px;' : ''"
                  >
                    <v-icon
                      :to="j.to"
                      :style="miniVariant ? 'margin-left: 12px;' : ''"
                    >
                      {{ j.icon }}
                    </v-icon>
                  </v-list-item-icon>
                </v-list-item>
              </template>
              <span>{{ j.title }}</span>
            </v-tooltip>

            <v-list-item
              v-else
              style="min-height: 0; margin-left: 50px"
              :to="j.to"
              class="submenutitle"
            >
              <v-list-item-title class="my-2">
                {{ j.title }}
              </v-list-item-title>

              <!-- <v-list-item-icon
                :style="miniVariant ? 'margin-left: -54px;' : ''"
              >
                <v-icon
                  :to="j.to"
                  :style="miniVariant ? 'margin-left: 12px;' : ''"
                >
                  {{ j.icon }}
                </v-icon>
              </v-list-item-icon> -->
            </v-list-item>

            <!-- v-show="!miniVariant" -->
          </div>
        </div>
      </v-list>
    </v-navigation-drawer>

    <!-- style="
    margin-left: -49px;
" -->

    <v-app-bar
      :color="changeColor"
      dark
      :clipped-left="clipped"
      fixed
      app
      :style="$nuxt.$route.name == 'index' ? 'z-index: 100000' : ''"
    >
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <v-btn icon @click.stop="miniVariant = !miniVariant">
        <v-icon>mdi-{{ `chevron-${miniVariant ? "right" : "left"}` }}</v-icon>
      </v-btn>
      <v-btn icon @click.stop="clipped = !clipped">
        <v-icon>mdi-application</v-icon>
      </v-btn>
      <span class="text-overflow">{{ title }}</span>
      <v-spacer></v-spacer>

      <v-menu
        nudge-bottom="50"
        transition="scale-transition"
        origin="center center"
        bottom
        left
        min-width="200"
        nudge-left="20"
      >
        <template v-slot:activator="{ on, attrs }">
          <label class="px-2 text-overflow" v-bind="attrs" v-on="on">
            <!-- {{ getUser }} -->
          </label>

          <v-btn icon color="yellow" v-bind="attrs" v-on="on">
            <v-avatar size="35">
              <v-img
                :src="`https://ui-avatars.com/api/?name=${getUser}`"
              ></v-img>
              <!-- <img :src="getLogo || '/no-image.PNG'" /> -->
            </v-avatar>
          </v-btn>
        </template>

        <v-list light nav dense>
          <v-list-item-group color="primary">
            <v-list-item @click="goToCompany()">
              <v-list-item-icon>
                <v-icon>mdi-account-multiple-outline</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text"
                  >Profile</v-list-item-title
                >
              </v-list-item-content>
            </v-list-item>

            <v-list-item @click="logout">
              <v-list-item-icon>
                <v-icon>mdi-logout</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text"
                  >Logout</v-list-item-title
                >
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-menu>
      <label class=" ">
        <v-badge
          v-if="pendingLeavesCount > 0"
          @click="navigateToLeavePage()"
          :color="pendingLeavesCount > 0 ? 'red' : 'black'"
          :content="pendingLeavesCount"
        >
          <v-icon @click="navigateToLeavePage()">mdi mdi-bell-ring</v-icon>
        </v-badge>
        <v-badge
          v-else
          @click="navigateToLeavePage()"
          color="black"
          content="0"
        >
          <v-icon @click="navigateToLeavePage()">mdi mdi-bell-ring</v-icon>
        </v-badge>
      </label>
      <v-snackbar
        top="top"
        v-model="snackNotification"
        location="right"
        :timeout="5000"
        :color="snackNotificationColor"
      >
        {{ snackNotificationText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snackNotification = false">
            Close
          </v-btn>
        </template>
      </v-snackbar>
    </v-app-bar>

    <v-main class="main_bg">
      <v-container>
        <nuxt />
      </v-container>
    </v-main>
    <v-btn
      height="50"
      width="20"
      dark
      :color="changeColor"
      class="fixed-setting"
      @click.stop="rightDrawer = !rightDrawer"
    >
      <v-icon class="spin" dark size="25">mdi-cog</v-icon>
    </v-btn>
    <!-- setting -->
    <v-navigation-drawer
      v-model="rightDrawer"
      :clipped="true"
      :right="right"
      fixed
      style="z-index: 1000"
    >
      <v-row style="margin-top: 50px">
        <v-col>
          <v-card class="pa-2" elevation="0">
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Theme</Strong>
              </div>
              <div
                class="btn-group"
                role="group"
                aria-label="Basic radio toggle button group"
              >
                <input
                  type="radio"
                  class="btn-check"
                  name="theme"
                  id="light"
                  autocomplete="off"
                  @click="changeTheme('light')"
                />
                <label class="btn" :class="'btn-outline-dark'" for="light"
                  >Light</label
                >
                <input
                  type="radio"
                  class="btn-check"
                  name="theme"
                  id="dark"
                  autocomplete="off"
                  @click="changeTheme('dark')"
                />
                <label class="btn btn-outline-dark" for="dark">Dark</label>
              </div>
            </v-col>
            <v-divider></v-divider>
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Top Bar</Strong>
              </div>
              <div class="d-flex">
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="primary"
                  @click="changeTopBarColor('primary')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="error"
                  @click="changeTopBarColor('error')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="indigo"
                  @click="changeTopBarColor('indigo')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="background"
                  @click="changeTopBarColor('background')"
                ></v-btn>
              </div>
            </v-col>
            <v-divider></v-divider>
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Side Bar</Strong>
              </div>
              <div class="d-flex">
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="primary"
                  @click="changeSideBarColor('primary')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="error"
                  @click="changeSideBarColor('error')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="indigo"
                  @click="changeSideBarColor('indigo')"
                ></v-btn>
                <v-btn
                  class="mx-2 stg-color-icon"
                  fab
                  dark
                  x-small
                  color="background"
                  @click="changeSideBarColor('background')"
                >
                </v-btn>
              </div>
            </v-col>
          </v-card>
        </v-col>
      </v-row>
    </v-navigation-drawer>
  </v-app>
</template>

<script>
export default {
  data() {
    return {
      pendingLeavesCount: 0,
      snackNotificationText: "",
      snackNotification: false,
      snackNotificationColor: "black",
      socketConnectionStatus: 0,
      miniVariant: false,
      right: true,
      rightDrawer: false,
      color: "",
      sideBarcolor: "background",
      year: new Date().getFullYear(),
      dropdown_menus: [{ title: "setting" }, { title: "logout" }],
      clipped: false,
      open_menu: [],
      drawer: true,
      fixed: false,
      order_count: "",
      menus: [
        {
          icon: "mdi-apps",
          title: `Dashboards`,
          open_menu: false,
          menu: "dashboard_access",
          hasChildren: [
            {
              icon: "mdi-appss",
              title: "Multi Inout",
              to: "/dashboard1",
              menu: "dashboard_access",
            },
            {
              icon: "mdi-appss",
              title: "General",
              to: "/dashboard2",
              menu: "dashboard_access",
            },
            // {
            //   icon: "mdi-appss",
            //   title: "Dashboard (OLD)",
            //   to: "/",
            //   menu: "dashboard_access",
            // },
          ],
        },

        {
          icon: "mdi-account-tie",
          title: "Employees",
          to: "/employees",
          menu: "employee_access",
        },

        {
          icon: "mdi-cash-multiple",
          title: `Payroll`,
          open_menu: false,
          menu: "payroll_access",
          hasChildren: [
            // {
            //   icon: "mdi-cash-multiple ",
            //   title: "Generate Month",
            //   to: "/payroll/month",
            //   menu:("payroll_generate_month_access")
            // },

            // {
            //   icon: "mdi-cash-multiple ",
            //   title: "Generate Payroll",
            //   to: "/employees",
            //   menu:("employee_schedule_access")
            // },
            {
              icon: "mdi-cashss",
              title: "Salary",
              to: "/payroll/salary",
              menu: "payroll_access",
            },
            {
              icon: "mdi mdi-calculatorss",
              title: "Payroll Settings",
              to: "/payroll/create",
              menu: "payroll_generate_access",
            },
          ],
        },
        {
          icon: "mdi-account-tie",
          title: `Visitor App`,
          open_menu: false,
          menu: "access",
          hasChildren: [
            {
              icon: "mdi-homess",
              title: "Dashboard",
              to: "/visitor-dashboard",
              menu: true,
            },
            {
              icon: "mdi-chart-box-outliness",
              title: "Visitors Report",
              to: "/visitors_report",
              menu: true,
            },
            {
              icon: "mdi-accountss",
              title: "Visitors",
              to: "/visitor",
              menu: true,
            },
            {
              icon: "mdi-account-tiess",
              title: "Host",
              to: "/host",
              menu: true,
            },
            {
              icon: "mdi-bullhornss",
              title: "Purpose",
              to: "/purpose",
              menu: true,
            },
            {
              icon: "mdi-select-groupss",
              title: "Zones",
              to: "/zone",
              menu: true,
            },
            {
              icon: "mdi-chart-box-outliness",
              title: "Visitors Logs",
              to: "/visitor_logs",
              menu: true,
            },
          ],
        },
        {
          icon: "mdi-calendar-today",
          title: `Access Control`,
          open_menu: false,
          menu: "access",
          hasChildren: [
            {
              icon: "mdi mdi-clock-time-four-outliness",
              title: "Timezones",
              to: "/timezone",
              menu: "timezone",
            },
            {
              icon: "mdi mdi-credit-card-clock-outliness",
              title: "TImezone Mapped List",
              to: "/timezonemapping/list",
              menu: "timezone_mapping_list",
            },
            {
              icon: "mdi mdi-camera-accountss",
              title: "Employee Photo Upload",
              to: "/employee_photo_upload",
              menu: "employee_photo_upload",
            },
          ],
        },
        {
          icon: "mdi-clipboard-text-clock",
          title: "Attendances Logs",
          to: "/devicelogs",
          menu: "logs_access",
        },
        {
          icon: "mdi-calendar-today",
          title: `Attendance Reports`,
          open_menu: false,
          menu: "attendance_report_access",
          hasChildren: [
            {
              icon: "mdi-chart-box-outliness",
              title: "General Reports",
              to: "/attendance_report/general",
              menu: "attendance_report_access",
            },
            {
              icon: "mdi-chart-box-outliness",
              title: "Multi InOut Reports",
              to: "/attendance_report/multi_inout",
              menu: "attendance_report_access",
            },
          ],
        },
        {
          icon: "mdi-calendar-today",
          title: `Attendance Setup`,
          open_menu: false,
          menu: "attendance_report_access",
          hasChildren: [
            {
              icon: "mdi-clock-outliness",
              title: "Shifts",
              to: "/shift",
              menu: "shift_access",
            },
            {
              icon: "mdi mdi-calendar-clockss",
              title: "Schedule List",
              to: "/schedule",
              menu: "schedule_access",
            },
            {
              icon: "mdi-account-tiess",
              title: "Employee Schedule",
              to: "/employee_schedule",
              menu: "employee_schedule_access",
            },
          ],
        },
        {
          icon: "mdi-email",
          title: "Notification",
          to: "/report_notifications",
          menu: "notifications_access",
        },

        {
          icon: "mdi-cog",
          title: `Settings`,
          open_menu: false,
          menu: "company_access",
          hasChildren: [
            {
              icon: "mdi-themess",
              title: "Card Builder",
              to: `/theme/${this.$auth.user?.company?.id}`,
              menu: "notifications_access",
            },
            {
              icon: "mdi mdi-card-account-detailsss",
              title: "Company",
              to: `/companies/${this.$auth.user?.company?.id}`,
              menu: "setting_access",
              class: "submenutitle",
            },
            {
              icon: "mdi-lanss",
              title: "Departments",
              to: "/department",
              menu: "department_access",
            },
            {
              icon: "mdi mdi-account-check-outliness",
              title: "Roles",
              to: "/role",
              menu: "role_access",
            },
            {
              icon: "mdi mdi-account-detailsss",
              title: "Assign Permissions",
              to: "/assign_permission",
              menu: "assign_permission_access",
            },
            {
              icon: "mdi-cellphone-textss",
              title: "Department Mapping",
              to: "/department-mapping/",
              menu: "assign_permission_access",
            },
            {
              icon: "mdi-cellphone-textss",
              title: "Devices",
              to: "/device",
              menu: "device_access",
            },
            // {
            //   icon: "mdi-badge-account-outline",
            //   title: "Upload Users",
            //   to: "/device_management",
            //   menu: "device_management",
            // },

            // {
            //   icon: "mdi mdi-clock-plus-outline",
            //   title: "Create New",
            //   to: "/timezonemapping/new",
            //   menu: "timezone_mapping_list",
            // },
          ],
        },

        // {
        //   icon: "mdi-briefcase-outline",
        //   title: `Organization`,
        //   open_menu: false,
        //   menu: "company_access",
        //   hasChildren: [
        //     {
        //       icon: "mdi-lan",
        //       title: "Departments",
        //       to: "/department",
        //       menu: "department_access",
        //     },

        //     // {
        //     //   icon: "mdi-account-details ",
        //     //   title: "Designations",
        //     //   to: "/designation",
        //     //   menu: "designation_access",
        //     // },
        //   ],
        // },
        {
          icon: "mdi mdi-calendar-star-four-points",
          title: "Holidays",
          to: "/holidays",
          menu: "holiday_access",
        },
        {
          icon: "mdi-briefcase-outline",
          title: `Leaves Management`,
          open_menu: false,
          menu: "leave_access",
          hasChildren: [
            {
              icon: "mdi mdi-calendar-accountss",
              title: "Leave Applications",
              to: "/leaves",
              menu: "leave_application_access",
            },
            {
              icon: "mdi mdi-calendarss",
              title: "Leave Types",
              to: "/leavetype",
              menu: "leave_type_accessss",
            },
            {
              icon: "mdi mdi-calendar-textss",
              title: "Leave Groups",
              to: "/leavegroups",
              menu: "leave_group_access",
            },

            // {
            //   icon: "mdi-lan",
            //   title: "Leaves",
            //   to: "/leaves",
            //   menu: "leaves_access",
            // },

            // {
            //   icon: "mdi-account-details ",
            //   title: "Settings",
            //   to: "/settings",
            //   menu: "leaves_access",
            // },
          ],
        },
        {
          icon: "mdi-bell",
          title: `Announcements`,
          open_menu: false,
          to: "/announcement",
        },
      ],
      items: [],
      modules: {
        module_ids: [],
        module_names: [],
      },
      clipped: true,

      miniVariant: false,
      title: "ideaHRMS",
      socket: null,
      logout_btn: {
        icon: "mdi-logout",
        label: "Logout",
      },
    };
  },
  created() {
    let das = {
      icon: "mdi-home",
      title: "Dashboard",
      to: "/",
      menu: "dashboard_access",
    };
    let user = this.$auth.user;
    let permissions = user.permissions;

    if (user && user.is_master) {
      this.items = this.menus;
      // this.items.unshift(das);
      return;
    }

    this.menus.forEach((ele) => {
      if (permissions.includes(ele.menu)) {
        this.items.push(ele);
      }
    });

    this.getCompanyDetails();
  },

  mounted() {
    this.verifyLeaveNotificationsApi();

    setInterval(() => {
      if (this.socketConnectionStatus != 1) {
        //socket connection is closed
        this.verifyLeaveNotificationsSocket();
      }
    }, 1000 * 60);
  },
  watch: {
    // socketConnectionStatus(val) {
    //   console.log('watch ', val);
    // },
  },
  computed: {
    changeColor() {
      return this.$store.state.color;
    },

    getUser() {
      if (this.$auth.user && this.$auth.user.user_type == "master") {
        return this.$auth.user.name;
      }
      return this.$auth.user &&
        this.$auth.user.employee &&
        this.$auth.user.company
        ? this.$auth.user.employee.display_name
        : this.$auth.user.company.name;
    },

    getLogo() {
      if (this.$auth.user && this.$auth.user.user_type == "master") {
        return "/no-image.PNG";
      }
      return this.$auth.user && this.$auth.user.company.logo;
    },
  },
  methods: {
    navigateToLeavePage() {
      this.$router.push("/leaves");
    },
    verifyLeaveNotificationsSocket() {
      // 0	CONNECTING	Socket has been created.The connection is not yet open.
      // 1	OPEN	The connection is open and ready to communicate.
      // 2	CLOSING	The connection is in the process of closing.
      // 3	CLOSED

      let company_id = this.$auth.user.company.id;

      if (!process.env.ADMIN_LEAVE_NOTIFICATION_SOCKET_ENDPOINT) return false;
      this.socket = new WebSocket(
        process.env.ADMIN_LEAVE_NOTIFICATION_SOCKET_ENDPOINT
      );

      this.socket.onopen = function () {
        this.socketConnectionStatus = this.socket.readyState;

        const data = {
          company_id: company_id,
        };
        this.socket.send(JSON.stringify(data)); // this works
      };
      this.socket.onclose = function () {
        this.socketConnectionStatus = 0;
      };
      this.socket.onmessage = ({ data }) => {
        data = JSON.parse(data);
        if (data.status && data.new_leaves_data[0]) {
          let element = data.new_leaves_data[0];
          //data.new_leaves_data.data.forEach(element => {

          this.snackNotification = true;
          this.snackNotificationText =
            "New Leave Notification - From : " +
            element.first_name +
            " " +
            element.last_name;
        }
        this.pendingLeavesCount = data.total_pending_count;
      };
    },
    verifyLeaveNotificationsApi() {
      if (!this.$auth.user?.company?.id) return false;
      let options = {
        params: {
          company_id: this.$auth.user?.company?.id || 0,
        },
      };

      this.$axios.get(`employee_leaves_new`, options).then(({ data }) => {
        if (data.status) {
          data.new_leaves_data.data.forEach((element) => {
            this.snackNotification = true;

            this.snackNotificationText =
              "New Leave Notification : From : " +
              element.employee.first_name +
              " " +
              element.employee.last_name;
          });

          this.pendingLeavesCount = data.total_pending_count;
        }
      });
    },
    collapseSubItems() {
      this.menus.map((item) => (item.active = false));
    },
    changeTopBarColor(color) {
      this.color = color;
      this.$store.commit("change_color", color);
    },

    changeTheme(color) {
      // alert(color);
    },

    changeSideBarColor(color) {
      this.sideBarcolor = color;
    },

    caps(str) {
      return str.replace(/\b\w/g, (c) => c.toUpperCase());
    },
    goToSetting() {
      this.$router.push("/setting");
    },
    goToLeaves() {
      this.$router.push("/leaves");
    },
    goToCompany() {
      let u = this.$auth.user.user_type;
      // if(u){
      // this.$router.push(`/empl/${this.$auth.user?.company?.id}`);
      // }
      this.$router.push(`/companies/${this.$auth.user?.company?.id}`);
    },
    getCompanyDetails() {
      let user = this.$auth.user;

      this.$axios.get(`company/${user?.company?.id}`).then(({ data }) => {
        let { modules } = data.record;

        if (modules !== null) {
          this.modules = {
            module_ids: modules.module_ids || [],
            module_names: modules.module_names.map((e) => ({
              icon: "mdi-chart-bubble",
              title: this.caps(e),
              to: "/" + e + "_modules",
              permission: true,
            })),
          };
        }
      });
    },
    can(per) {
      let user = this.$auth.user;
      return (
        (user && user.permissions.some((e) => e == per || per == "/")) ||
        user.is_master
      );
    },

    logout() {
      this.$axios.get(`/logout`).then(({ res }) => {
        this.$auth.logout();
      });
    },
  },
};
</script>
