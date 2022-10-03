<template>
  <v-app>
    <v-navigation-drawer
      v-model="drawer"
      dark
      :mini-variant="miniVariant"
      :clipped="clipped"
      fixed
      app
      class="no_print"
      :color="sideBarcolor"
    >
      <v-list v-for="(i, idx) in items" :key="idx" style="padding: 5px 0 0 0px">
        <v-list-item
          :to="i.to"
          router
          v-if="!i.hasChildren"
          :class="!miniVariant || 'pl-2'"
        >
          <v-list-item-icon v-if="i.permission" class="ma-2">
            <v-icon>{{ i.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-title v-if="i.permission">
            {{ i.title }}&nbsp;
            <v-badge
              v-if="i.title == 'Orders' && order_count > 0"
              color="primary"
              :content="order_count"
              small
            />
          </v-list-item-title>
        </v-list-item>

        <v-list-item
          v-else
          :class="!miniVariant || 'pl-2'"
          @click="i.open_menu = !i.open_menu"
        >
          <v-list-item-icon v-if="i.permission" class="ma-2">
            <v-icon>{{ i.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-title v-if="i.permission">{{
            i.title
          }}</v-list-item-title>
          <v-icon v-if="i.permission" small>{{
            !i.open_menu ? "mdi-chevron-down" : "mdi-chevron-up"
          }}</v-icon>
        </v-list-item>
        <div v-if="i.open_menu && i.title == `Modules`">
          <div
            style="margin-left: 50px"
            v-for="(j, jdx) in modules.module_names"
            :key="jdx"
          >
            <v-list-item v-if="j.permission" style="min-height: 0" :to="j.to">
              <v-list-item-title>{{ j.title }}</v-list-item-title>

              <v-list-item-icon>
                <v-icon :to="j.to">{{ j.icon }}</v-icon>
              </v-list-item-icon>
            </v-list-item>
          </div>
        </div>

        <div v-else-if="i.open_menu && i.title != `Modules`">
          <div
            style="margin-left: 50px"
            v-for="(j, jdx) in i.hasChildren"
            :key="jdx"
          >
            <v-list-item v-if="j.permission" style="min-height: 0" :to="j.to">
              <v-list-item-title>{{ j.title }}</v-list-item-title>

              <v-list-item-icon>
                <v-icon :to="i.to">{{ j.icon }}</v-icon>
              </v-list-item-icon>
            </v-list-item>
          </div>
        </div>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar :color="changeColor" dark :clipped-left="clipped" fixed app>
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <v-btn icon @click.stop="miniVariant = !miniVariant">
        <v-icon>mdi-{{ `chevron-${miniVariant ? "right" : "left"}` }}</v-icon>
      </v-btn>
      <v-btn icon @click.stop="clipped = !clipped">
        <v-icon>mdi-application</v-icon>
      </v-btn>
      {{ title }}
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
          <label class="px-2" v-bind="attrs" v-on="on">
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
                <v-list-item-title class="black--text"
                  >Profile</v-list-item-title
                >
              </v-list-item-content>
            </v-list-item>

            <v-list-item @click="goToSetting()">
              <v-list-item-icon>
                <v-icon>mdi-cog</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="black--text"
                  >Setting</v-list-item-title
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
    >
      <v-row style="margin-top: 50px;">
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
  mounted() {},
  data() {
    return {
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
      admins: [
        ["Management", "mdi-account-multiple-outline"],
        ["Settings", "mdi-cog-outline"]
      ],
      cruds: [
        ["Create", "mdi-plus-outline"],
        ["Read", "mdi-file-outline"],
        ["Update", "mdi-update"],
        ["Delete", "mdi-delete"]
      ],

      items: [
        {
          icon: "mdi-home",
          title: "Dashboard",
          to: "/",
          permission: this.can("/")
        },

        {
          icon: "mdi-briefcase-outline",
          title: `Organization`,
          open_menu: false,
          permission: this.can("company_access"),
          hasChildren: [
            {
              icon: "mdi-domain",
              title: "Company",
              to: `/companies/details/${this.$auth?.user?.company?.id}`,
              permission: this.can("company_access")
            },
            {
              icon: "mdi-door",
              title: "Department",
              to: "/department",
              permission: this.can("department_access")
            },
            {
              icon: "mdi-door",
              title: "Sub Department",
              to: "/sub-department",
              permission: this.can("sub_department_access")
            },

            {
              icon: "mdi-door",
              title: "Designation",
              to: "/designation",
              permission: this.can("designation_access")
            },
            {
              icon: "mdi-bullhorn-variant-outline",
              title: "Announcement",
              to: "/announcement",
              permission: this.can("announcement_access")
            },
            {
              icon: "mdi-clipboard-edit-outline",
              title: "Policy",
              to: "/policy",
              permission: this.can("policy_access")
            }

            // {
            //   icon: "mdi-account",
            //   title: "Employees",
            //   to: "/employees",
            //   permission: this.can("employee_access"),
            // },
            // {
            //   icon: "mdi-bullhorn-variant-outline",
            //   title: "Announcement",
            //   to: "/announcement",
            //   permission: this.can("employee_access"),
            // },
            // {
            //   icon: "mdi-clipboard-edit-outline",
            //   title: "Policy",
            //   to: "/policy",
            //   permission: this.can("employee_access"),
            // },
            // employees
          ]
        },
        {
          icon: "mdi-door",
          title: "Devices",
          to: "/device",
          permission: this.can("device_access")
        },
        {
          icon: "mdi-account",
          title: "Employees",
          to: "/employees",
          permission: this.can("employee_access")
        },
        {
          icon: "mdi-account",
          title: "Roles",
          to: "/role",
          permission: this.can("role_access")
        },
        {
          icon: "mdi-lock",
          title: "Assign Permissions",
          to: "/assign_permission",
          permission: this.can("assign_permission_access")
        },
        {
          icon: "mdi-apps",
          title: "Modules",
          open_menu: false,
          permission: this.can("module_access"),
          hasChildren: [
            {
              icon: "mdi-chart-bubble",
              title: "Payroll",
              to: "/payroll_modules",
              permission: this.can("module_access")
            },
            {
              icon: "mdi-chart-bubble",
              title: "Attendance",
              to: "/attendance_modules",
              permission: this.can("module_access")
            },
            {
              icon: "mdi-chart-bubble",
              title: "HR Management",
              to: "/hr_modules",
              permission: this.can("module_access")
            }
          ]
        },
        {
          icon: "mdi-clipboard-text-clock",
          title: "Reports",
          to: "/attendance_report/monthly",
          permission: this.can("module_access")
        },
        // {
        //   icon: "mdi-clipboard-text-clock",
        //   title: "Reports",
        //   to: "/attendance_report",
        //   permission: this.can("module_access"),
        //   open_menu: false,
        //   hasChildren: [
        // {
        //   icon: "mdi-chart-bubble",
        //   title: "Daily",
        //   to: "/attendance_report/daily",
        //   permission: this.can("module_access")
        // },
        // {
        //   icon: "mdi-chart-bubble",
        //   title: "Weekly",
        //   to: "/attendance_report/weekly",
        //   permission: this.can("module_access")
        // },
        // {
        //   icon: "mdi-chart-bubble",
        //   title: "Monthly",
        //   to: "/attendance_report/monthly",
        //   permission: this.can("module_access")
        // },
        // {
        //   icon: "mdi-chart-bubble",
        //   title: "Yearly",
        //   to: "/attendance_report/yearly",
        //   permission: this.can("module_access")
        // }
        //   ]
        // },
        // {
        //   icon: "mdi-account",
        //   title: "User Management",
        //   open_menu: false,
        //   permission: this.can("user_access"),
        //   hasChildren: [
        //     // {
        //     //   icon: "mdi-account",
        //     //   title: "Users",
        //     //   to: "/user",
        //     //   permission: this.can("user_access"),
        //     // },
        //     {
        //       icon: "mdi-account",
        //       title: "Roles",
        //       to: "/role",
        //       permission: this.can("role_access"),
        //     },
        //     {
        //       icon: "mdi-lock",
        //       title: "Assign Permissions",
        //       to: "/assign_permission",
        //       permission: this.can("assign_permission_access"),
        //     },
        //     // {
        //     //   icon: "mdi-account",
        //     //   title: "Employee To Report",
        //     //   to: "/employee_to_report",
        //     //   permission: this.can("assign_permission_access"),
        //     // },
        //   ],
        // },
        {
          icon: "mdi-cog",
          title: "Setting",
          to: "/setting",
          permission: this.can("/")
        },
        {
          icon: "mdi-history",
          title: "Original Logs",
          to: "/logs",
          permission: this.can("/")
        }
      ],
      modules: {
        module_ids: [],
        module_names: []
      },
      clipped: true,

      miniVariant: false,
      title: "ideaHRMS",
      logout_btn: {
        icon: "mdi-logout",
        label: "Logout"
      }
    };
  },
  created() {
    this.getCompanyDetails();
  },
  computed: {
    changeColor() {
      return this.$store.state.color;
    },

    getUser() {
      return this.$auth.user && this.$auth.user.company.name;
    },

    getLogo() {
      return this.$auth.user && this.$auth.user.company.logo;
    }
  },
  methods: {
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
      return str.replace(/\b\w/g, c => c.toUpperCase());
    },
    goToSetting() {
      this.$router.push("/setting");
    },
    goToCompany() {
      this.$router.push(`/companies/details/${this.$auth.user?.company?.id}`);
    },
    getCompanyDetails() {
      let user = this.$auth.user;

      this.$axios.get(`company/${user?.company?.id}`).then(({ data }) => {
        let { modules } = data.record;

        if (modules !== null) {
          this.modules = {
            module_ids: modules.module_ids || [],
            module_names: modules.module_names.map(e => ({
              icon: "mdi-chart-bubble",
              title: this.caps(e),
              to: "/" + e + "_modules",
              permission: true
            }))
          };
        }
        console.log(
          "🚀 ~ file: default.vue ~ line 592 ~ this.$axios.get ~ modules",
          modules
        );
      });
    },
    can(per) {
      let user = this.$auth.user;
      return (
        (user && user.permissions.some(e => e.name == per || per == "/")) ||
        user.is_master
      );
    },
    async logout() {
      this.$axios.get(`/logout`).then(({ res }) => {
        this.$auth.logout();
      });
    }
  }
};
</script>
<style scoped>
/* .page-enter-active,
.page-leave-active {
  transition: opacity 0.5s;
}
.page-enter,
.page-leave-to {
  opacity: 0;
}

.layout-enter-active,
.layout-leave-active {
  transition: opacity 0.5s;
}
.layout-enter,
.layout-leave-to {
  opacity: 0;
}

.slide-bottom-enter-active,
.slide-bottom-leave-active {
  transition: opacity 0.25s ease-in-out, transform 0.25s ease-in-out;
}
.slide-bottom-enter,
.slide-bottom-leave-to {
  opacity: 0;
  transform: translate3d(0, 15px, 0);
}
.bounce-enter-active {
  transform-origin: top;
  animation: bounce-in 0.8s;
}
.bounce-leave-active {
  transform-origin: top;
  animation: bounce-out 0.5s;
}
@keyframes bounce-in {
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.25);
  }
  100% {
    transform: scale(1);
  }
}
@keyframes bounce-out {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.25);
  }
  100% {
    transform: scale(0);
  }
} */

.fixed-setting {
  position: fixed !important;
  top: 500px;
  z-index: 100000;
  transition: right 1000ms !important;
  right: -15px !important;
}

.v-btn__content {
  margin: 0 12px 0 0px !important;
  padding: 0 !important;
}

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
</style>
