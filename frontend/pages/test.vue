<template>
  <!-- <div style="background-color:white;" class="p-4"> -->
  <div class="p-4">
    <v-row>
      <v-col md="2">
        <v-card elevation="5">
          <v-card-title primary-title class="justify-center">
            <div>
              <v-img
                lazy-src="https://picsum.photos/id/11/10/6"
                max-height="150"
                max-width="150"
                src="http://localhost:8000/upload/1664442185.png"
                style="margin: 0 auto;"
              ></v-img>
              <v-list-item class="text-center">
                <v-list-item-content>
                  <v-list-item-title class="mb-1 text-capitalize">
                    akil security & alarm systems
                  </v-list-item-title>
                  <v-list-item-subtitle>akil@test.com </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </div>
            <v-divider></v-divider>
          </v-card-title>

          <v-list-item two-line>
            <v-list-item-content>
              <div class="d-flex">
                <v-list-item-title>Member From : </v-list-item-title>
                <v-list-item-subtitle>31 Aug 2020</v-list-item-subtitle>
              </div>
              <small
                style="border-style:dashed; border-width: 1px; border-color: #ECF0F4;"
              ></small>
            </v-list-item-content>
          </v-list-item>
        </v-card>
      </v-col>
      <v-col md="4">
        <v-row>
          <v-col md="12">
            <div>
              <v-expansion-panels v-model="panel" multiple>
                <v-expansion-panel>
                  <v-expansion-panel-header>Company </v-expansion-panel-header>
                  <v-expansion-panel-content>
                    <v-list-item
                      style=" border: 1px solid #ECF0F4;   border-width: 3px;"
                    >
                      <v-list-item-content>
                        <v-row class="mt-2 p-0">
                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Member From</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            31 Aug 1982
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Expiry Date</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            20 Oct 2012
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Max Branches</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            5
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;">
                                Max Employees</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            100
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Max Devices</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            40
                          </v-col>
                        </v-row>
                      </v-list-item-content>
                    </v-list-item>
                  </v-expansion-panel-content>
                </v-expansion-panel>

                <v-expansion-panel>
                  <v-expansion-panel-header>Contact </v-expansion-panel-header>
                  <v-expansion-panel-content>
                    <v-list-item
                      style=" border: 1px solid #ECF0F4;   border-width: 3px;"
                    >
                      <v-list-item-content>
                        <v-row class="mt-2 p-0">
                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Member From</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            31 Aug 1982
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Expiry Date</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            20 Oct 2012
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Max Branches</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            5
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;">
                                Max Employees</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            100
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Max Devices</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            40
                          </v-col>
                        </v-row>
                      </v-list-item-content>
                    </v-list-item>
                  </v-expansion-panel-content>
                </v-expansion-panel>

                <v-expansion-panel>
                  <v-expansion-panel-header>License</v-expansion-panel-header>
                  <v-expansion-panel-content>
                    <v-list-item
                      style=" border: 1px solid #ECF0F4;   border-width: 3px;"
                    >
                      <v-list-item-content>
                        <v-row class="mt-2 p-0">
                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Member From</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            31 Aug 1982
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Expiry Date</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            20 Oct 2012
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Max Branches</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            5
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;">
                                Max Employees</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            100
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Max Devices</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            40
                          </v-col>
                        </v-row>
                      </v-list-item-content>
                    </v-list-item>
                  </v-expansion-panel-content>
                </v-expansion-panel>

                <v-expansion-panel>
                  <v-expansion-panel-header>Location</v-expansion-panel-header>
                  <v-expansion-panel-content>
                    <v-list-item
                      style=" border: 1px solid #ECF0F4;   border-width: 3px;"
                    >
                      <v-list-item-content>
                        <v-row class="mt-2 p-0">
                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Member From</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            31 Aug 1982
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Expiry Date</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            20 Oct 2012
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Max Branches</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            5
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;">
                                Max Employees</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            100
                          </v-col>

                          <v-col cols="5" class="py-1">
                            <v-list-item-title class="text-h7 mb-1">
                              <strong style="color: #7F7F7F;"
                                >Max Devices</strong
                              >
                            </v-list-item-title>
                          </v-col>
                          <v-col cols="6" class="py-1" style="color: #7F7F7F;">
                            40
                          </v-col>
                        </v-row>
                      </v-list-item-content>
                    </v-list-item>
                  </v-expansion-panel-content>
                </v-expansion-panel>
              </v-expansion-panels>
            </div>
          </v-col>
        </v-row>
      </v-col>
      <v-col md="6">
        <v-card elevation="5">
          <Calender />
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import Calender from "../components/Calender.vue";
export default {
  data: () => ({
    panel: [0, 1, 2, 3],
    items: [
      { text: "Real-Time", icon: "mdi-clock" },
      { text: "Audience", icon: "mdi-account" },
      { text: "Conversions", icon: "mdi-flag" }
    ]
  }),
  components: { Calender }
};
</script>
