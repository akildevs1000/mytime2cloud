<template>
  <v-app>
    <v-navigation-drawer v-model="drawer" dark :mini-variant="miniVariant" :clipped="clipped" fixed app
      :color="sideBarcolor" :style="miniVariant ? 'width: 60px' : ''" @transitionend="collapseSubItems">
      <br />
      <v-list v-for="(i, idx) in items" :key="idx" style="padding: 5px 0 0 0px">
        <v-list-item :to="i.to" router v-if="!i.hasChildren" :class="!miniVariant || 'pl-2'">
          <v-list-item-icon class="ma-2">
            <v-icon>{{ i.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-title> {{ i.title }}&nbsp; </v-list-item-title>
        </v-list-item>
        <v-list-item v-else :class="!miniVariant || 'pl-2'" @click="i.open_menu = !i.open_menu">
          <v-list-item-icon class="ma-2">
            <v-icon>{{ i.icon }}</v-icon>
            <v-icon v-if="miniVariant" small>{{ !i.open_menu ? "mdi-chevron-down" : "mdi-chevron-up" }}
            </v-icon>
          </v-list-item-icon>

          <v-list-item-title>{{ i.title }} </v-list-item-title>
          <v-icon small>{{ !i.open_menu ? "mdi-chevron-down" : "mdi-chevron-up" }}
          </v-icon>
        </v-list-item>
        <div v-if="i.open_menu">
          <div style="margin-left: 54px" v-for="(j, jdx) in i.hasChildren" :key="jdx">
            <!-- v-show="!miniVariant" -->
            <v-list-item style="min-height: 0" :to="j.to" class="submenutitle">
              <v-list-item-title v-if="!miniVariant">{{ j.title }}
              </v-list-item-title>

              <v-list-item-icon :style="miniVariant ? 'margin-left: -54px;' : ''">
                <v-icon :to="j.to" :style="miniVariant ? 'margin-left: 12px;' : ''">
                  {{ j.icon }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
          </div>
        </div>
      </v-list>
    </v-navigation-drawer>

    <!-- style="
    margin-left: -49px;
" -->

    <v-app-bar :color="changeColor" dark :clipped-left="clipped" fixed app
      :style="$nuxt.$route.name == 'index' ? 'z-index: 100000' : ''">
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <v-btn icon @click.stop="miniVariant = !miniVariant">
        <v-icon>mdi-{{ `chevron-${miniVariant ? "right" : "left"}` }}</v-icon>
      </v-btn>
      <v-btn icon @click.stop="clipped = !clipped">
        <v-icon>mdi-application</v-icon>
      </v-btn>
      <span class="text-overflow">{{ title }}</span>
      <v-spacer></v-spacer>

      <v-menu nudge-bottom="50" transition="scale-transition" origin="center center" bottom left min-width="200"
        nudge-left="20">
        <template v-slot:activator="{ on, attrs }">

          <label class="px-2 text-overflow" v-bind="attrs" v-on="on">
            {{ getUser }}
          </label>

          <v-btn icon color="yellow" v-bind="attrs" v-on="on">
            <v-avatar>
              <img :src="getLogo || '/no-image.PNG'" />
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
                <v-list-item-title class="black--text">Profile</v-list-item-title>
              </v-list-item-content>
            </v-list-item>

            <v-list-item @click="goToSetting()">
              <v-list-item-icon>
                <v-icon>mdi-cog</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text">Setting</v-list-item-title>
              </v-list-item-content>
            </v-list-item>

            <v-list-item @click="logout">
              <v-list-item-icon>
                <v-icon>mdi-logout</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text">Logout</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-menu>
      <label class="px-2  ">
        <!-- <v-icon v-if="pendingLeavesCount == 0">mdi mdi-bell</v-icon>
        <span v-else>
          <v-icon @click="snackNotificationText != '' && snackNotification == true" color="success">mdi
            mdi-bell-ring </v-icon>
          <v-chip title="Pending Count" bold color="white" style="color:black" to="/leaves"><strong>{{
            pendingLeavesCount }}</strong></v-chip>
        </span> -->

        <v-badge v-if="pendingLeavesCount > 0" @click="navigateToLeavePage()"
          :color="pendingLeavesCount > 0 ? 'red' : 'black'" :content="pendingLeavesCount">
          <v-icon @click="navigateToLeavePage()">mdi
            mdi-bell-ring</v-icon>
        </v-badge>
        <v-badge v-else @click="navigateToLeavePage()" color="black" content="0">
          <v-icon @click="navigateToLeavePage()">mdi
            mdi-bell-ring</v-icon>
        </v-badge>

      </label>
      <v-snackbar top="top" v-model="snackNotification" location="right" :timeout="5000" :color="snackNotificationColor">
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
    <v-btn height="50" width="20" dark :color="changeColor" class="fixed-setting"
      @click.stop="rightDrawer = !rightDrawer">
      <v-icon class="spin" dark size="25">mdi-cog</v-icon>
    </v-btn>
    <!-- setting -->
    <v-navigation-drawer v-model="rightDrawer" :clipped="true" :right="right" fixed style="z-index: 1000">
      <v-row style="margin-top: 50px">
        <v-col>
          <v-card class="pa-2" elevation="0">
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Theme</Strong>
              </div>
              <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="theme" id="light" autocomplete="off"
                  @click="changeTheme('light')" />
                <label class="btn" :class="'btn-outline-dark'" for="light">Light</label>
                <input type="radio" class="btn-check" name="theme" id="dark" autocomplete="off"
                  @click="changeTheme('dark')" />
                <label class="btn btn-outline-dark" for="dark">Dark</label>
              </div>
            </v-col>
            <v-divider></v-divider>
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Top Bar</Strong>
              </div>
              <div class="d-flex">
                <v-btn class="mx-2 stg-color-icon" fab dark x-small color="primary"
                  @click="changeTopBarColor('primary')"></v-btn>
                <v-btn class="mx-2 stg-color-icon" fab dark x-small color="error"
                  @click="changeTopBarColor('error')"></v-btn>
                <v-btn class="mx-2 stg-color-icon" fab dark x-small color="indigo"
                  @click="changeTopBarColor('indigo')"></v-btn>
                <v-btn class="mx-2 stg-color-icon" fab dark x-small color="background"
                  @click="changeTopBarColor('background')"></v-btn>
              </div>
            </v-col>
            <v-divider></v-divider>
            <v-col cols="12">
              <div class="mb-3">
                <Strong>Side Bar</Strong>
              </div>
              <div class="d-flex">
                <v-btn class="mx-2 stg-color-icon" fab dark x-small color="primary"
                  @click="changeSideBarColor('primary')"></v-btn>
                <v-btn class="mx-2 stg-color-icon" fab dark x-small color="error"
                  @click="changeSideBarColor('error')"></v-btn>
                <v-btn class="mx-2 stg-color-icon" fab dark x-small color="indigo"
                  @click="changeSideBarColor('indigo')"></v-btn>
                <v-btn class="mx-2 stg-color-icon" fab dark x-small color="background"
                  @click="changeSideBarColor('background')">
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
          icon: "mdi-home",
          title: "Dashboard",
          to: "/",
          menu: "dashboard_access",
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
              icon: "mdi-cash",
              title: "Salary",
              to: "/payroll/salary",
              menu: "payroll_generate_access",
            },
            {
              icon: "mdi mdi-calculator",
              title: "Payroll Settings",
              to: "/payroll/create",
              menu: "payroll_generate_access",
            },
          ],
        },

        {
          icon: "mdi-calendar-today",
          title: `Reports`,
          open_menu: false,
          menu: "payroll_access",
          hasChildren: [
            {
              icon: "mdi-chart-box-outline",
              title: "Attendance Report",
              to: "/attendance_report",
              menu: "payroll_access",
            },

            {
              icon: "mdi-clock-outline",
              title: "Shifts",
              to: "/shift",
              menu: "shift_access",
            },
            {
              icon: "mdi mdi-calendar-clock",
              title: "Schedule List",
              to: "/schedule",
              menu: "schedule_access",
            },
            {
              icon: "mdi-account-tie",
              title: "Employee Schedule",
              to: "/employee_schedule",
              menu: "employee_schedule_access",
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
              icon: "mdi mdi-clock-time-four-outline",
              title: "Timezones",
              to: "/timezone",
              menu: "timezone",
            },
            {
              icon: "mdi mdi-credit-card-clock-outline",
              title: "TImezone Mapped List",
              to: "/timezonemapping/list",
              menu: "timezone_mapping_list",
            },
            {
              icon: "mdi mdi-camera-account",
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
              icon: "mdi mdi-card-account-details",
              title: "Profile",
              to: `/companies/${this.$auth.user?.company?.id}`,
              menu: "setting_access",
              class: "submenutitle",
            },

            {
              icon: "mdi mdi-account-check-outline",
              title: "Roles",
              to: "/role",
              menu: "role_access",
            },
            {
              icon: "mdi mdi-account-details",
              title: "Assign Permissions",
              to: "/assign_permission",
              menu: "assign_permission_access",
            },
            {
              icon: "mdi-cellphone-text",
              title: "Devices List",
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

        {
          icon: "mdi-briefcase-outline",
          title: `Organization`,
          open_menu: false,
          menu: "company_access",
          hasChildren: [
            {
              icon: "mdi-lan",
              title: "Departments",
              to: "/department",
              menu: "department_access",
            },

            // {
            //   icon: "mdi-account-details ",
            //   title: "Designations",
            //   to: "/designation",
            //   menu: "designation_access",
            // },
          ],
        },
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
              icon: "mdi mdi-calendar-account",
              title: "Leave Applications",
              to: "/leaves",
              menu: "leave_application_access",
            },
            {
              icon: "mdi mdi-calendar",
              title: "Leave Types",
              to: "/leavetype",
              menu: "leave_type_access",
            },
            {
              icon: "mdi mdi-calendar-text",
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


    this.verifyLeaveNotifications();

    setInterval(() => {
      console.log('socketConnectionStatus', this.socketConnectionStatus);
      if (this.socketConnectionStatus != 1) { //socket connection is closed
        this.verifyLeaveNotifications();
      }
    }, 1000 * 60);

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

  mounted() { },
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
    verifyLeaveNotifications() {



      // // 0	CONNECTING	Socket has been created.The connection is not yet open.
      // // 1	OPEN	The connection is open and ready to communicate.
      // // 2	CLOSING	The connection is in the process of closing.
      // // 3	CLOSED
      // console.log("User: ", this.$auth);
      // let company_id = this.$auth.user.company.id;
      // console.log(process.env.ADMIN_LEAVE_NOTIFICATION_SOCKET_ENDPOINT);
      // if (!process.env.ADMIN_LEAVE_NOTIFICATION_SOCKET_ENDPOINT) return false;
      // let ws = new WebSocket(process.env.ADMIN_LEAVE_NOTIFICATION_SOCKET_ENDPOINT);

      // ws.onopen = function () {

      //   this.socketConnectionStatus = ws.readyState;

      //   const data = {
      //     company_id: company_id,

      //   };
      //   ws.send(JSON.stringify(data)); // this works

      // };
      // ws.onclose = function () {

      //   this.socketConnectionStatus = 0;

      // };
      // ws.onmessage = ({ data }) => {



      //   data = JSON.parse(data);
      //   console.log('Socket', data);
      //   if (data.status && data.new_leaves_data[0]) {

      //     let element = data.new_leaves_data[0];
      //     //data.new_leaves_data.data.forEach(element => {
      //     console.log('Notification Content', element);

      //     this.snackNotification = true;
      //     this.snackNotificationText = "New Leave Notification - From : " + element.first_name + " " + element.last_name;
      //     console.log(this.snackNotificationText);


      //   }
      //   this.pendingLeavesCount = data.total_pending_count;
      // };


    },
    verifyLeaveNotifications_old() {

      if (!this.$auth.user?.company?.id) return false;
      let options = {
        params: {
          company_id: this.$auth.user?.company?.id || 0
        }
      };


      this.$axios
        .get(`employee_leaves_new`, options)
        .then(({ data }) => {
          if (data.status) {


            data.new_leaves_data.data.forEach(element => {
              this.snackNotification = true;

              this.snackNotificationText = "New Leave Notification : From : " + element.employee.first_name + " " + element.employee.last_name;;
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
<style>
table {
  font-family: Roboto !important;
}

.fixed-setting {
  position: fixed !important;
  top: 500px;
  z-index: 100000;
  transition: right 1000ms !important;
  right: -15px !important;
}

/* .v-btn__content {
  margin: 0 12px 0 0px !important;
  padding: 0 !important;
} */

.setting-drawer-open {
  right: 250px !important;
}

.setting-drawer-close {
  right: -15px !important;
}

.spin {
  -webkit-animation: spin 4s linear infinite;
  -moz-animation: spin 4s linear infinite;
  animation: spin 4s linear infinite;

  margin: 0 12px 0 0px !important;
  padding: 0 !important;
}

@-moz-keyframes spin {
  100% {
    -moz-transform: rotate(360deg);
  }
}

@-webkit-keyframes spin {
  100% {
    -webkit-transform: rotate(360deg);
  }
}

@keyframes spin {
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}

.stg-color-icon {
  width: 30px !important;
  height: 30px !important;
}

@media (min-width: 1264px) {
  .container {
    max-width: 100%;
  }
}

.submenutitle {
  padding-left: 5px;
  margin-left: -15px;
}

table.employee-table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  /* border: 1px solid #dddddd; */
  text-align: left;
  padding: 8px;
}

table.employee-table tr:nth-child(even) {
  background-color: #e9e9e9;
}

table.employee-table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  /* border: 1px solid #dddddd; */
  text-align: left;
  padding: 8px;
}

table.employee-table tr:nth-child(even) {
  background-color: #e9e9e9;
}

.toolbaritems-button-design {
  padding-top: 8px !important;
  padding-right: 0px !important;
  /* margin: auto;
  border-radius: 5px; */
}

.toolbaritems-button-design .v-btn {
  height: 32px !important;
}

.timezone-displaylist {
  height: 225px !important;
  background: #fff;
  border-bottom-left-radius: 6px !important;
  border-bottom-right-radius: 6px !important;
  overflow: auto;
}

.timezone-displaylistview {
  padding-left: 10px !important;
  padding-bottom: 5px !important;
  padding-top: 0px !important;
  cursor: pointer !important;

  border-bottom: 1px solid #ddd;
}

.photo-displaylist {
  height: 225px !important;
  background: #fff !important;
  border-bottom-left-radius: 6px !important;
  border-bottom-right-radius: 6px !important;
  overflow: auto;
}

.photo-displaylistview {
  padding-left: 10px !important;
  padding-bottom: 5px !important;
  padding-top: 0px !important;
  cursor: pointer;

  border-bottom: 1px solid #ddd;
}

.timezoneedit-displaylist {
  height: 225px !important;
  background: #fff;
  border-bottom-left-radius: 6px !important;
  border-bottom-right-radius: 6px !important;
  overflow: auto;
}

.timezoneedit-displaylistview {
  padding-left: 10px !important;
  padding-bottom: 5px !important;
  padding-top: 0px !important;

  border-bottom: 1px solid #ddd;
}

.v-small-dialog__menu-content {
  margin-left: 3%;
  margin-top: 3%;
}

.employeepage-seach-textfield:focus {
  border: 0px !important;
  box-shadow: none !important;
}

.sortable {
  font-weight: bold;
  color: black !important;
}

.v-data-table-header .desc i {
  color: red !important;
}

.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}


.container {
  max-width: 100% !important;
}

.text-overflow {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;

}

.text-overflow-parent {

  max-width: 100px;

}


.text-overflow-child {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  max-width: 100px;
}

.vertical-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 10px;
  width: 100%;
}

/* .center-parent {
  background: gray;
  height: 300px;
  width: 300px;
  position: relative;
} */

.center-child {
  position: absolute;
  top: 50%;
  left: 30%;
  margin: -50px 0 0 -50px;
}

tbody tr:nth-of-type(odd) {
  background-color: rgba(0, 0, 0, 0.05);
}

.v-picker--date {
  height: 420px !important;
}

.table-search-header {
  padding: 0px;
  font-size: 10px;
  padding-top: 10px;
}

.table-search-header .theme--light.v-input input {
  display: inline;
  padding-top: 0px;
}

.table-search-header .v-text-field--outlined fieldset,
.table-search-header .v-text-field--outlined .v-text-field__slot {
  height: 32px !important;
  width: 100% !important;
}

.table-search-header .v-input__icon--clear {
  margin-right: 0px !important
}

.table-search-header .v-text-field--outlined>.v-input__control>.v-input__slot {
  min-height: 32px !important;
}

.filter-select-hidden-text .v-input__append-inner {
  width: 100%important;
}

.table-search-header td {
  border: 0px !important;
  padding-top: 10px;

}

.table-search-header .v-text-field__details {
  display: none !important;
}

.table-search-header .v-input__icon--clear {
  margin-top: -17px;
  margin-right: 31px;
}

.filter-select-hidden-text input {
  display: none !important;
}

.filter-select-hidden-text .v-input__append-inner {
  margin: 0px !important;
  margin-left: -54px !important;
}

.filter-select-hidden-text .mdi-close {
  font-size: 8px !important;
}

.filter-select-hidden-text .v-select__selection {
  font-size: 13px !important;
}

.announcement-dropdown .v-select__selections {
  height: 33px;
  overflow: hidden;
}

.dialog-close-button {
  margin-top: -35px px;

  margin-right: -26px;
}

@media (max-width: 500px) {
  .employeepage-seach-textfield {
    max-width: 68px;
  }

  .xs-padding-0 {
    padding-right: 0px !important;
  }

  .xs-margin-5 {
    margin-top: 5px !important;
  }

  .v-data-table>.v-data-table__wrapper .v-data-table__mobile-table-row {
    margin: 10px;
    border: 1px solid #ededed;
    display: block;
  }
}
</style>
