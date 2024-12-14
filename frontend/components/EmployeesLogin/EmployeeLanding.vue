<template>
  <v-dialog v-model="dialog" width="1300">
    <WidgetsClose left="1290" @click="dialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon :color="`black`" :size="`small`">mdi-cellphone-text</v-icon>
        {{ iconText }}
      </span>
    </template>
    <style scoped>
      .custom-scrollbar {
        scrollbar-width: thin; /* For Firefox */
        scrollbar-color: #7f7f7f #f5f5f5; /* Track and thumb colors for Firefox */
      }

      .custom-scrollbar::-webkit-scrollbar {
        width: 8px; /* Width of the scrollbar */
      }

      .custom-scrollbar::-webkit-scrollbar-track {
        background: #f5f5f5; /* Background of the scrollbar track */
        border-radius: 4px; /* Rounded corners */
      }

      .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #7f7f7f; /* Scrollbar thumb color */
        border-radius: 4px; /* Rounded corners for thumb */
        border: 2px solid #f5f5f5; /* Adds spacing between thumb and track */
      }
    </style>
    <v-card >
      <v-tabs v-model="activeTab" background-color="grey lighten-3">
        <div class="mt-3 mx-3">Employee Profile</div>
        <v-spacer></v-spacer>
        <v-tab>Info</v-tab>
        <v-tab>Attendance</v-tab>
        <v-tab>Leaves</v-tab>
        <v-tab>Schedule</v-tab>
        <v-tab>Devices</v-tab>
      </v-tabs>
      <v-tabs-items v-model="activeTab" sty>
        <v-tab-item>
          <v-container fluid>
            <v-row>
              <v-col cols="3" >
                <v-card class="pa-1" color="grey lighten-3">
                  <!-- Profile Section -->
                  <v-container class="text-center">
                    <!-- <style scoped>
                      .my-avatar:hover {
                        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2),
                          0 4px 6px rgba(0, 0, 0, 0.1);
                      }
                    </style> -->
                    <v-avatar
                      class="my-avatar"
                      size="125"
                      style="
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1),
                          0 1px 3px rgba(0, 0, 0, 0.08); /* Subtle shadow */
                        transition: box-shadow 0.3s ease; /* Smooth transition on hover */
                        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2),
                          0 4px 6px rgba(0, 0, 0, 0.1);
                      "
                    >
                      <img
                        src="https://backend.mytime2cloud.com/media/employee/profile_picture/1706346188.jpg"
                        alt="Profile Picture"
                      />
                    </v-avatar>
                    <h5 class="mt-2">MUSAFFIR</h5>
                    <div class="caption">Employee ID: 400</div>
                    <p class="caption">DOJ: 1<sup>st</sup> January 2024</p>
                    <!-- <v-btn icon color="red" outlined>
                      <v-icon>mdi-file-pdf-box</v-icon>
                    </v-btn> -->
                  </v-container>
                  <div class="white">
                    <div class="body-2 pa-2 d-flex justify-space-between">
                      <div>Contact</div>
                      <div>0529048024</div>
                    </div>
                    <v-divider />
                    <div class="body-2 pa-2 d-flex justify-space-between">
                      <div>Email</div>
                      <div>example@example.com</div>
                    </div>
                    <v-divider />
                    <div class="body-2 pa-2 d-flex justify-space-between">
                      <div>Dep</div>
                      <div>Sales</div>
                    </div>
                    <v-divider />
                    <div class="body-2 pa-2 d-flex justify-space-between">
                      <div>Desg</div>
                      <div>Supervisor</div>
                    </div>
                    <v-divider />
                    <div class="body-2 pa-2 d-flex justify-space-between">
                      <div>Role</div>
                      <div>Manager</div>
                    </div>
                    <v-divider />
                    <div class="body-2 pa-2 d-flex justify-space-between">
                      <div>Mobile Login</div>
                      <div>Yes</div>
                    </div>
                    <v-divider />
                    <div class="body-2 pa-2 d-flex justify-space-between">
                      <div>Web Login</div>
                      <div>No</div>
                    </div>
                    <v-divider />
                    <div class="body-2 pa-2 d-flex justify-space-between">
                      <div>Last Login</div>
                      <div>30 Aug 2024, 10:46</div>
                    </div>
                  </div>
                  <div class="grey lighten-3 mt-5">
                    <WidgetsCalendar />
                  </div>
                </v-card>
              </v-col>
              <v-divider vertical></v-divider>
              <v-col
                cols="9"
                class="custom-scrollbar"
              >
                <v-row>
                  <v-col>
                    <WidgetsTimeSheetCard />

                    <!-- <v-row>
                      <v-col v-for="(stat, index) in stats" :key="index">
                        <WidgetsCard :key="currentTabId" :options="stat" />
                      </v-col>
                    </v-row> -->
                  </v-col>

                  <v-col>
                    <WidgetsProgressCard />
                  </v-col>
                  <v-col>
                    <WidgetsTimeline />
                  </v-col>
                </v-row>
                <v-row>
                  <v-col>
                    <EmployeeReport />
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-container>
        </v-tab-item>
        <v-tab-item>
          <v-row>
            <v-col>
              <EmployeeReport />
            </v-col>
          </v-row>
        </v-tab-item>

        <v-tab-item>
          <v-row>
            <v-col>
              <v-container fluid>
                <WidgetsCalendar />
              </v-container>
            </v-col>
          </v-row>
        </v-tab-item>

        <v-tab-item>
          <v-row>
            <v-col cols="12">
              <WidgetsCalendar />
            </v-col>
          </v-row>
        </v-tab-item>

        <v-tab-item>
          <v-row>
            <v-col>
              <WidgetsPlayGround />
            </v-col>
          </v-row>
        </v-tab-item>
      </v-tabs-items>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  data() {
    return {
      dialog: false,
      activeTab: 0,
      stats: [
        {
          label: "P",
          value: "10",
          icon: "mdi-check",
          color: "green",
          col: "4",
        },
        {
          label: "A",
          value: "10",
          icon: "mdi-close",
          color: "red",
          col: "4",
        },
        {
          label: "L",
          value: "10",
          icon: "mdi-calendar",
          color: "orange",
          col: "4",
        },
      ],
    };
  },
};
</script>
