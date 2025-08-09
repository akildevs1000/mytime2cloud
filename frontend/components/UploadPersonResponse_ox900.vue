<template>
  <div>
    <!-- <v-dialog v-model="uploadPersonResponseDialog" max-width="800px"> -->

    <WidgetsClose left="790" @click="closeDialog()" />
    <v-card :loading="loadingToDevice">
      <v-card-title class="popup-background" dense flat>
        <span class="gey--text">Response </span>

        <v-spacer></v-spacer>
        <span style="font-size: 13px; padding-right: 50px">
          <span v-if="loadingToDevice"
            >Uploading to Device...Please wait...({{ deviceResponses.length }})
            Completed</span
          >
          <span v-else
            ><span style="color: green">Uploading completed</span>

            <v-btn
              v-if="rightEmployeesCount != uploadedResponseEmployeeCount + ''"
              class="primary"
              @click="uploadAgain()"
              x-small
              dense
              >Upload Only Missing employees</v-btn
            >
          </span>
        </span>
        <v-spacer></v-spacer>

        <span style="font-size: 14px"> {{ finalMessage }}</span>
      </v-card-title>
      <v-card-text>
        <div v-if="deviceResponses.length">
          <v-simple-table dense
            ><tbody>
              <tr>
                <td>#</td>
                <td>Name</td>
                <td>User Code</td>
                <td>Device Name</td>
                <td>Status</td>
              </tr>
              <tr v-for="(item, index) in deviceResponses" :key="index">
                <td>{{ ++index }}</td>
                <td>
                  {{ item.name }}
                </td>
                <td>
                  {{ item.userCode }}
                </td>
                <td>
                  {{
                    totalDevices.find((e) => e.device_id == item.device_id)
                      ?.name
                  }}
                </td>
                <td>
                  <div v-if="!loadingToDevice">
                    <div
                      v-if="
                        totalDevices &&
                        employeesListFromDevice[
                          totalDevices.find(
                            (e) => e.device_id == item.device_id
                          )?.id
                        ]?.includes(item.userCode + '')
                      "
                    >
                      <v-icon color="green">mdi-check</v-icon>
                      {{ processDeviceResponses(item) }}
                    </div>
                    <div v-else style="color: red">
                      {{ processDeviceResponses(item) }}
                    </div>
                  </div>
                  <div v-else>
                    <v-icon v-if="loadingToDevice" color="yellow"
                      >mdi-sync-circle</v-icon
                    >
                    <span v-else>
                      <!-- <v-icon color="red">mdi-close</v-icon> -->
                      Pending...
                      <!-- {{ item?.sdk_response?.message }} -->
                      <!-- {{ processDeviceResponses(cameraResponses) }} -->
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </v-simple-table>
        </div>
        <!-- <div v-if="cameraResponses.length">
            <h3>Camera Response</h3>
            <pre>{{ cameraResponses }}</pre>
          </div> -->
        <!-- <div v-if="cameraResponses2.length">
            <h3>Camera Response</h3>
            <pre>{{ cameraResponses2 }}</pre>
          </div> -->
      </v-card-text>
    </v-card>
    <!-- </v-dialog> -->
  </div>
</template>
<script>
export default {
  props: [
    "deviceResponses",
    "cameraResponses",
    "cameraResponses2",
    "employeesListFromDevice",
    "totalDevices",
    "loadingToDevice",
    "finalMessage",
    "rightEmployeesCount",
    "uploadedResponseEmployeeCount",
  ],
  data() {
    return {
      uploadPersonResponseDialog: false,
      deviceResponsesSorted: [],
    };
  },
  methods: {
    closeDialog() {
      this.$emit("closePopup");
    },

    uploadAgain() {
      this.$emit("uploadAgain");
    },
    processDeviceResponses(data) {
      console.log("data", data);

      let message = data.sdk_response.message;

      if (message === "没有对应的命令") {
        return "Unable to connect Device. Please try again";
      }
      if (message === 200) {
        return "Uploaded";
      } else if (message) return message;

      return "Unable to connect Device. Please try again";
      // let message = data.deviceResponse?.[0]?.sdk_response?.message;

      // return message;

      // if (message === "没有对应的命令") {
      //   return "Unable to connect Device. Please try again";
      // } else if (message) return message;

      // return "Unable to connect Device. Please try again";

      // if (data.deviceResponse?.[0]?.sdk_response?.status === 102) {
      //   return "Unable to connect Device. Please try again";
      // } else if (data.cameraResponse?.[0]?.status === 200) {
      //   return "Uploaded Successfully";
      // } else if (data.cameraResponse2?.[0]?.status === 200) {
      //   return "Uploaded Successfully";
      // } else if (data.deviceResponse?.[0]?.sdk_response?.status === 200) {
      //   return "Uploaded Successfully";
      // } else if (message) {
      //   if (message === 200) {
      //     return "Uploaded Successfully";
      //   } else if (message === "Duplicate face image.") {
      //     return "Duplicate Face Image";
      //   } else if (message === "没有对应的命令") {
      //     return "No corresponding command";
      //   } else {
      //     return message; // fallback
      //   }
      // } else if (data.deviceResponse?.[0]?.message) {
      //   return data.deviceResponse[0].message;
      // } else {
      //   return "Unable to connect Device. Please try again";
      // }
    },
  },
  created() {
    // try {
    //   if (this.deviceResponses)
    //     this.deviceResponsesSorted = [...this.deviceResponses].sort((a, b) => {
    //       return String(a.userCode).localeCompare(String(b.userCode));
    //     });
    // } catch (e) {
    //   console.log(e);
    // }
    // if (this.closeDialog) this.uploadPersonResponseDialog = false;
  },
};
</script>
