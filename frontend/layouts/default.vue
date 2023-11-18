<template>
  <v-app>
    <v-navigation-drawer
      expand-on-hover
      rail
      v-model="drawer"
      dark
      :clipped="clipped"
      fixed
      app
      :color="sideBarcolor"
      :width="150"
    >
      <br />
      <v-list
        v-for="(i, idx) in items"
        :key="idx"
        style="padding: 5px 0 0 0px"
        :title="i.title"
      >
        <v-list-item
          :to="i.module != 'dashboard' ? i.to : ''"
          @click="getTopMenuItems(i)"
          router
          v-if="!i.hasChildren"
          :class="!miniVariant || 'pl-2'"
          vertical
          style="display: inline-block"
          :title="i.title"
        >
          <v-list-item-icon class="ma-2" :title="i.title">
            <v-icon>{{ i.icon }}</v-icon>
          </v-list-item-icon>

          <v-list-item-title class="text-center p-2">
            {{ i.title }}&nbsp;
          </v-list-item-title>
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
              <v-list-item-title v-if="can(j.menu)" class="my-2">
                {{ j.title }}
              </v-list-item-title>
            </v-list-item>
          </div>
        </div>
      </v-list>
    </v-navigation-drawer>
    <v-app-bar
      :color="changeColor"
      dark
      :clipped-left="clipped"
      fixed
      app
      :style="$nuxt.$route.name == 'index' ? 'z-index: 100000' : ''"
    >
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <span class="text-overflow">
        <img title="My Time Cloud " :src="logo_src" style="width: 150px" />
      </span>
      <v-spacer></v-spacer>
      <span style="100%">
        <template
          v-if="
            getLoginType == 'company' ||
            getLoginType == 'branch' ||
            (getLoginType == 'employee' &&
              $auth.user.role.role_type.toLowerCase() != 'guard' &&
              $auth.user.role.role_type.toLowerCase() != 'host')
          "
        >
          <v-row align="center" justify="space-around" class="">
            <v-col v-for="(items, index) in company_top_menu" :key="index">
              <v-btn
                small
                text
                class="btn-text-size"
                :elevation="0"
                :color="
                  menuProperties[items.menu] &&
                  menuProperties[items.menu].selected
                "
                fill
                @click="setSubLeftMenuItems(items.menu, items.to)"
              >
                <span>{{ items.title }}</span>
              </v-btn>
            </v-col>
          </v-row>
        </template>
      </span>

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
          <v-btn icon color="red" v-bind="attrs" v-on="on">
            <v-avatar size="35" style="border: 1px solid #6946dd">
              <v-img :src="getLogo"></v-img>
            </v-avatar>
          </v-btn>
        </template>

        <v-list light nav dense>
          <v-list-item-group color="primary">
            <v-list-item
              v-if="this.$auth && this.$auth.user.user_type == 'company'"
              @click="goToCompany()"
            >
              <v-list-item-icon>
                <v-icon>mdi-account-multiple-outline</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text"
                  >Profile</v-list-item-title
                >
              </v-list-item-content>
            </v-list-item>
            <v-list-item
              v-if="$auth.user.user_type != 'company'"
              @click="changeLoginType"
            >
              <v-list-item-icon>
                <v-icon>mdi-account-multiple-outline</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text">
                  Login Into employee
                  <!-- {{
                    caps(getLoginType == "branch" ? "employee" : "branch")
                  }} -->
                </v-list-item-title>
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

      <v-btn
        v-if="getLoginType == 'company' || getLoginType == 'branch'"
        icon
        plan
        @click="goToSettings()"
        class="mr-3"
        ><v-icon class="violet--text" style="text-align: center"
          >mdi-settings</v-icon
        ></v-btn
      >

      <label class=" ">
        <v-badge
          v-if="pendingLeavesCount > 0"
          @click="navigateToLeavePage()"
          :color="pendingLeavesCount > 0 ? 'red' : 'white'"
          :content="pendingLeavesCount"
        >
          <v-icon class="violet--text" @click="navigateToLeavePage()"
            >mdi mdi-bell-ring</v-icon
          >
        </v-badge>
        <v-badge v-else @click="navigateToLeavePage()" content="0">
          <v-icon
            style="color: #e91919 !important"
            @click="navigateToLeavePage()"
            >mdi mdi-bell-ring</v-icon
          >
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

    <v-main
      class="main_bg"
      :style="miniVariant ? 'padding-left: 60px;' : 'padding-left: 140px;'"
    >
      <v-container style="max-width: 100%">
        <nuxt />
      </v-container>
    </v-main>

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
import company_menus from "../menus/company.json";
import employee_menus from "../menus/employee.json";
import branch_menus from "../menus/branch.json";
import guard_menus from "../menus/guard.json";
import host_menus from "../menus/host.json";

import company_top_menu from "../menus/company_modules_top.json";
import employee_top_menu from "../menus/employee_modules_top.json";

export default {
  data() {
    return {
      selectedBranchName: "All Branches",
      seelctedBranchId: "",
      branch_id: "",
      menuProperties: {
        dashboard: {
          elevation: 0,
          selected: "",
        },
        settings: {
          elevation: 0,
          selected: "",
        },
        employees: {
          elevation: 0,
          selected: "",
        },
        attendance: {
          elevation: 0,
          selected: "",
        },
        payroll: {
          elevation: 0,
          selected: "",
        },
        access_control: {
          elevation: 0,
          selected: "",
        },
        visitors: {
          elevation: 0,
          selected: "",
        },
        reports: {
          elevation: 0,
          selected: "",
        },

        profile_topmenu: {
          elevation: 0,
          selected: "",
        },
        attendance_topmenu: {
          elevation: 0,
          selected: "",
        },
        requests_topmenu: {
          elevation: 0,
          selected: "",
        },
        announcements_topmenu: {
          elevation: 0,
          selected: "",
        },
        payslips_topmenu: {
          elevation: 0,
          selected: "",
        },
        schedule_topmenu: {
          elevation: 0,
          selected: "",
        },
      },

      topMenu_Selected: "dashboard",
      company_menus,
      employee_menus,
      branch_menus,
      guard_menus,
      host_menus,
      company_top_menu,
      employee_top_menu,
      pendingLeavesCount: 0,
      snackNotificationText: "",
      snackNotification: false,
      snackNotificationColor: "black",
      socketConnectionStatus: 0,

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
      logo_src: "",
      logo_src2: "",
      items: [],
      modules: {
        module_ids: [],
        module_names: [],
      },
      clipped: true,

      miniVariant: true,
      title: "MyTime2Cloud",
      socket: null,
      logout_btn: {
        icon: "mdi-logout",
        label: "Logout",
      },
      viewing_page_name: "",
    };
  },
  created() {
    this.$store.commit("loginType", this.$auth.user.user_type);
    this.getCompanyDetails();
    this.setMenus();
    this.setSubLeftMenuItems("dashboard", "/dashboard2", false);
    this.logo_src = require("@/static/logo22.png");
  },

  mounted() {
    let menu_name = this.$route.name;
    let bgColor = "violet";
    let loadSelectedMenu = "";

    menu_name = menu_name.replaceAll("-", "/");

    if (this.getLoginType === "company" || this.getLoginType === "branch") {
      loadSelectedMenu = this.company_menus.filter(
        (item) => item.to === "/" + menu_name && item.submenu == null
      );

      if (loadSelectedMenu[0]) {
        menu_name = loadSelectedMenu[0].module;

        if (this.menuProperties.hasOwnProperty(menu_name)) {
          for (const key in this.menuProperties) {
            this.menuProperties[key].elevation = 0;
            this.menuProperties[key].selected = "";
          }

          this.menuProperties[menu_name].elevation = 0;
          this.menuProperties[menu_name].selected = bgColor;
        }
        //Color is changed and Now display sub menu - click - load left sub menu items

        this.items = this.company_menus.filter(
          (item) => item.module === loadSelectedMenu[0].module
        );
      }
    }
  },
  watch: {},
  computed: {
    changeColor() {
      return "#ecf0f4"; //this.$store.state.color;
    },

    getUser() {
      const user = this.$auth.user;
      const userType = user.user_type;

      if (userType === "master") {
        return user.name;
      } else if (userType === "company") {
        return user.company.name;
      }

      const employee = user.employee;
      if (employee) {
        return employee.display_name || employee.first_name;
      }

      return null; // Or some default value indicating no user found
    },

    getLogo() {
      let logosrc = "";
      if (this.$auth.user && this.$auth.user.user_type == "master") {
        logosrc = "/no-image.PNG";
      } else if (this.$auth.user && this.$auth.user.user_type == "employee") {
        logosrc =
          this.$auth.user.employee.profile_picture || "/no-profile-image.jpg";
      } else if (this.$auth.user && this.$auth.user.user_type == "branch") {
        logosrc = this.$auth.user.branch_logo || "/no-profile-image.jpg";
      }

      if (logosrc == "") {
        logosrc = this.$auth.user
          ? this.$auth.user.company.logo
          : "/no-profile-image.jpg";
      }

      return logosrc;
    },
    getLoginType() {
      return this.$store.state.loginType;
    },

    hasDepartments() {
      return this.$auth.user && this.$auth.user.assignedDepartments.length > 0;
    },
  },
  methods: {
    getBranchName() {
      return this.$auth.user.branch_name;
    },
    getTopMenuItems(i) {
      if (i.module == "dashboard") {
        this.setSubLeftMenuItems(i.submenu, i.to);
      }
    },
    goToSettings() {
      this.setSubLeftMenuItems("settings", "/branches");
    },
    setSubLeftMenuItems(menu_name, page, redirect = true) {
      this.topMenu_Selected = menu_name;

      let bgColor = "violet";
      this.setMenus();

      // Check if menu_name exists in menuProperties
      if (this.menuProperties.hasOwnProperty(menu_name)) {
        for (const key in this.menuProperties) {
          this.menuProperties[key].elevation = 0;
          this.menuProperties[key].selected = "";
        }

        this.menuProperties[menu_name].elevation = 0;
        this.menuProperties[menu_name].selected = bgColor;
      }
      if (redirect) this.$router.push(page);
    },

    setMenus() {
      if (this.$auth.user.role.role_type == 0) {
        {
          alert("Invalid User Type");
          this.logout();
        }

        return "";
      }
      let roleType = this.$auth.user.role.role_type.toLowerCase();

      if (this.getLoginType === "company" || this.getLoginType === "branch") {
        // this.items = this.company_menus;
        this.items = this.company_menus.filter(
          (item) => item.module === this.topMenu_Selected
        );
        return;
      } else if (this.getLoginType === "employee") {
        if (/guard/.test(roleType)) {
          this.items = this.guard_menus;
          return;
        } else if (/host/.test(roleType)) {
          this.items = this.host_menus;
          return;
        } else {
          this.items = this.company_menus.filter(
            (item) => item.module === this.topMenu_Selected
          );
          return;
        }
      }
    },

    changeLoginType() {
      try {
        // if (this.getLoginType == "branch")
        {
          // this.$store.commit("loginType", "employee");
          // this.setMenus();
          let email = this.$store.state.email;
          let password = this.$store.state.password;

          email = this.$crypto.encrypt(email);
          password = this.$crypto.encrypt(password);

          email = encodeURIComponent(email);
          password = encodeURIComponent(password);

          if (email && password) {
            window.location.href =
              process.env.EMPLOYEE_APP_URL +
              "/loginwithtoken?email=" +
              email +
              "&password=" +
              password;

            return "";
          } else {
          }
          // this.$router.push("/employees/profile");
        }
        // else {
        //   this.$store.commit("loginType", "branch");
        //   this.setMenus();
        //   this.$router.push("/dashboard2");
        // }
      } catch (e) {
        console.log(e);
      }
    },
    navigateToLeavePage() {
      this.$router.push("/leaves");
    },

    filterBranch(branch) {
      this.$emit("openalert", "");

      if (branch) {
        this.selectedBranchName = branch.branch_name;
        this.seelctedBranchId = branch.id;
      } else {
        this.selectedBranchName = "All Branches";
        this.seelctedBranchId = "";
      }
    },
    collapseSubItems() {
      this.company_menus.map((item) => (item.active = false));
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
      this.$router.push(`/companies/${this.$auth.user?.company_id}`);
    },
    getCompanyDetails() {
      this.$axios
        .get(`company/${this.$auth.user?.company_id}`)
        .then(({ data }) => {
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
      return this.$pagePermission.can(per, this);
    },

    logout() {
      this.$axios.get(`/logout`).then(({ res }) => {
        this.$auth.logout();
      });
    },
  },
};
</script>
<style scoped>
.btn-text-size {
  font-size: 15px !important;
}
.leftMenuWidth {
  width: 140px !important;
}
.main_bg {
  padding-left: 140px;
}
.v-list-item {
  text-align: center;
  width: 100%;
}
</style>
<!-- Extra overriting classes-->
<style scoped>
.violet {
  background-color: #6946dd;
}
.bold {
  color: bold;
}
.text--violet {
  color: #6946dd;
}
.v-list-item--active i {
  color: #6946dd;
}

.theme--dark.v-list-item:not(.v-list-item--active):not(.v-list-item--disabled) {
  color: #9aa9b9;
}
.theme--dark.v-list-item:not(.v-list-item--active):not(.v-list-item--disabled)
  i {
  color: #9aa9b9;
}

header,
header button,
header a,
header i {
  color: black !important;
}

.theme--dark.v-bottom-navigation .v-btn:not(.v-btn--active) {
  color: black !important;
}
.theme--dark.v-bottom-navigation .v-btn--active {
  background: rgb(105, 70, 221);
  color: #fff !important;
}
</style>

<style>
.violet {
  background-color: #6946dd;
}
.bold {
  font-weight: bold;
}
.text--violet {
  color: #6946dd;
}
.view-profile-table-lineheight {
  line-height: 40px;
  width: 100%;
}
.view-profile-table-lineheight tr {
  border-bottom: 1px solid #ddd;
}
.view-profile-table-lineheight td {
  padding-right: 5px;
  padding-left: 5px;
}

.whitebackground--text {
  background-color: #ecf0f4;
}
/* New Theme  popup_background*/
.v-application .popup_background {
  background-color: #ecf0f4 !important;
}
.popup_background {
  background-color: #ecf0f4 !important;
  color: black !important;
  font-weight: 400 !important;

  font-size: 1.25rem !important;
}
.popup_title,
.v-dialog > .v-card > .v-card__title {
  color: black !important;
  font-weight: 400 !important;

  font-size: 1.25rem !important;
}
.popup_background i {
  color: black !important;
}
.popup_background button {
  color: black !important;
}

.popup_background .v-tabs-bar {
  background-color: #ecf0f4 !important;
  color: black;
  font-weight: 400;

  font-size: 1.25rem;
}
.popup_background .v-tabs-bar i {
  color: black !important;
}
.popup_background .v-toolbar__title {
  color: black !important;
}
.popup_background .v-icon {
  color: black !important;
}

/* .theme--dark.v-toolbar.v-sheet {
  background-color: #cfd8dc !important;
} */
/* .v-card {
  background-color: #cfd8dc;
}
.v-card header {
  background-color: #cfd8dc;
}
.v-card .v-card__title {
  color: black;
}
.v-card i {
  color: black;
}

.v-card .v-toolbar__title {
  color: black;
} */

.input-small-fieldset fieldset {
  height: 30px;
}
.input-small-fieldset input {
  margin-top: -15px;
}
.input-small-fieldset .v-input__append-inner {
  margin-top: -2px;
}
.black--text {
  color: black;
}
.black {
  color: black;
}

.iconsize {
  width: 20px;
}

.secondary-value {
  font-size: 10px;
}

.form-control:focus {
  box-shadow: none !important;

  /* height: 32px !important; */
}

.iconsize30 {
  width: 30px;
  height: auto;
}

.basic-table-design {
  tr {
    width: 100%;
    border-bottom: 1px solid #ddd;
  }
}
/* .v-application .primary--text {
  color: #6946dd !important;
  caret-color: #6946dd !important;
} */

.slidegroup1 .v-slide-group {
  height: 34px !important;
}
</style>

<style>
/*! CSS Used from: https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css */
*,
:after,
:before {
  box-sizing: border-box;
}
button {
  border-radius: 0;
}
button:focus:not(:focus-visible) {
  outline: 0;
}
button {
  margin: 0;
  font-family: inherit;
  font-size: inherit;
  line-height: inherit;
}
button {
  text-transform: none;
}
[type="button"],
button {
  -webkit-appearance: button;
}
[type="button"]:not(:disabled),
button:not(:disabled) {
  cursor: pointer;
}
.btn {
  display: inline-block;
  color: #4f4f4f;
  text-align: center;
  text-decoration: none;
  vertical-align: middle;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  background-color: transparent;
  border: 0.125rem solid transparent;
  padding: 0.375rem 0.75rem;
  border-radius: 0.25rem;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out,
    border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
@media (prefers-reduced-motion: reduce) {
  .btn {
    transition: none;
  }
}
.btn:hover {
  color: #4f4f4f;
}
.btn:disabled {
  pointer-events: none;
  opacity: 0.65;
}
button:focus {
  outline: 0;
}
.btn {
  text-transform: uppercase;
  vertical-align: bottom;
  border: 0;
  box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.2), 0 2px 10px 0 rgba(0, 0, 0, 0.1);
  font-weight: 500;
  padding: 0.625rem 1.5rem 0.5rem;
  font-size: 0.75rem;
  line-height: 1.5;
}
.btn:active,
.btn:active:focus,
.btn:focus,
.btn:hover {
  box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.2), 0 4px 20px 0 rgba(0, 0, 0, 0.1);
}
.btn:disabled {
  box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.2), 0 2px 10px 0 rgba(0, 0, 0, 0.1);
  border: 0;
}
.btn:focus {
  outline: 0;
  box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.2), 0 4px 20px 0 rgba(0, 0, 0, 0.1);
}
.btn-block {
  display: block;
  width: 100%;
}
.btn-block + .btn-block {
  margin-top: 0.5rem;
}
/*! CSS Used from: https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/4.4.95/css/materialdesignicons.min.css */
.mdi:before {
  display: inline-block;
  font: normal normal normal 24px/1 "Material Design Icons";
  font-size: inherit;
  text-rendering: auto;
  line-height: inherit;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.mdi-chevron-right::before {
  content: "\F142";
}
/*! CSS Used from: https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css */
.mdi:before {
  display: inline-block;
  font: normal normal normal 24px/1 "Material Design Icons";
  font-size: inherit;
  text-rendering: auto;
  line-height: inherit;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.mdi-chevron-right::before {
  content: "\F0142";
}
/*! CSS Used from: Embedded */
*,
:after,
:before {
  background-repeat: no-repeat;
  box-sizing: inherit;
}
:after,
:before {
  text-decoration: inherit;
  vertical-align: inherit;
}
* {
  margin: 0;
  padding: 0;
}
button {
  font: inherit;
}
button {
  overflow: visible;
}
button {
  text-transform: none;
}
[type="button"],
button {
  color: inherit;
  cursor: pointer;
}
button,
html [type="button"] {
  -webkit-appearance: button;
}
button {
  background-color: transparent;
  border-style: none;
}
.v-icon.v-icon {
  font-feature-settings: "liga";
  align-items: center;
  display: inline-flex;
  font-size: 24px;
  justify-content: center;
  letter-spacing: normal;
  line-height: 1;
  position: relative;
  text-indent: 0;
  transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1), visibility 0s;
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  vertical-align: middle;
}
.v-icon.v-icon:after {
  background-color: currentColor;
  border-radius: 50%;
  content: "";
  display: inline-block;
  height: 100%;
  left: 0;
  opacity: 0;
  pointer-events: none;
  position: absolute;
  top: 0;
  transform: scale(1.3);
  transition: opacity 0.2s cubic-bezier(0.4, 0, 0.6, 1);
  width: 100%;
}

.apexcharts-menu-icon {
  padding-top: 10px;
}
</style>
<style>
.v-application .error--text {
  color: red;
}
.error--text,
.text-danger {
  font-size: 13px;
  color: red !important;
}
.v-messages {
  min-height: 0px !important;
}

.text-break-dot {
  text-overflow: ellipsis;
  width: 200px;
  overflow: hidden;
  white-space: nowrap;
  height: 25px;
}
.breakthewords {
  display: -webkit-box;
  font-size: 12px !important;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.branchlogo {
  width: 50px;
}

.table_active {
  background: #e1e1e1;
  border-left: 1px solid green;
  font-weight: bold;
}
.annnouncment_table .text-left {
  padding: 0px !important;
}
.announ_priority {
  color: rgb(63, 81, 181);
  width: 100%;
  text-align: center;
  padding-bottom: 10px;
  font-weight: bold;
  font-size: 19px;
}
.slidegroup1 .v-slide-group {
  height: 34px !important;
}

.icon-blue {
  color: #755fc9 !important;
}
</style>

<!-- <link rel="stylesheet" href="../static/css/textbox-label-style.css" /> -->
