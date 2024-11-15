<template>
  <div>
    <v-dialog v-model="uploadPersonResponseDialog" max-width="800px">
      <WidgetsClose left="790" @click="uploadPersonResponseDialog = false" />
      <v-card>
        <v-alert dense flat color="grey lighten-3">
          <span class="gey--text">Response</span>
        </v-alert>
        <v-card-text>
          <div
            v-if="
              jsonResponse &&
              jsonResponse.deviceResponse &&
              jsonResponse.deviceResponse.length
            "
          >
            <!-- <h3>Device Response</h3> -->
            <v-simple-table dense
              ><tbody>
                <tr>
                  <td>Name</td>
                  <td>User Code</td>
                  <td>Device Id</td>
                  <td>Status</td>
                </tr>
                <tr
                  v-for="(item, index) in jsonResponse.deviceResponse"
                  :key="index"
                >
                  <td>
                    {{ item.name }}
                  </td>
                  <td>
                    {{ item.userCode }}
                  </td>
                  <td>
                    {{ item.device_id }}
                  </td>
                  <td>
                    <v-icon v-if="item.status == 200" color="green"
                      >mdi-check</v-icon
                    >
                    <v-icon v-else color="red">mdi-close</v-icon>
                  </td>
                </tr>
              </tbody>
            </v-simple-table>
          </div>
          <div
            v-if="
              jsonResponse &&
              jsonResponse.cameraResponse &&
              jsonResponse.cameraResponse.length
            "
          >
            <h3>Camera Response</h3>
            <pre>{{ jsonResponse.cameraResponse }}</pre>
          </div>
          <div
            v-if="
              jsonResponse &&
              jsonResponse.cameraResponse2 &&
              jsonResponse.cameraResponse2.length
            "
          >
            <h3>Camera Response</h3>
            <pre>{{ jsonResponse.cameraResponse2 }}</pre>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
  props: ["jsonResponse"],
  data() {
    return {
      uploadPersonResponseDialog: false,

      // let cameraResponse = response.data.cameraResponse2[0];
      // console.log(cameraResponse);
      // if (
      //   cameraResponse == "Unable to Conenct Device" ||
      //   cameraResponse == ""
      // ) {
      //   this.snackbar.show = true;
      //   this.response =
      //     person.name +
      //     " - Unable to Conenct Device. Try again after some time.";
      // } else {
      //   cameraResponse = JSON.parse(cameraResponse);
      //   if (cameraResponse.errors) {
      //     console.log(cameraResponse.errors[0].detail);
      //     this.snackbar.show = true;
      //     this.response =
      //       person.name + " - " + cameraResponse.errors[0].detail;
      //   } else {
      //     this.snackbar.show = true;
      //     if (cameraResponse.person_name) {
      //       this.response = cameraResponse.person_name + " Uploaded";
      //     }
      //   }
      // }
    };
  },
};
</script>
