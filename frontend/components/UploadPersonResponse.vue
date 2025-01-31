<template>
  <div>
    <v-dialog v-model="uploadPersonResponseDialog" max-width="800px">
      <WidgetsClose left="790" @click="uploadPersonResponseDialog = false" />
      <v-card>
        <v-alert dense flat color="grey lighten-3">
          <span class="gey--text">Response</span>
        </v-alert>
        <v-card-text>
          <div v-if="deviceResponses.length">
            <v-simple-table dense
              ><tbody>
                <tr>
                  <td>Name</td>
                  <td>User Code</td>
                  <td>Device Id</td>
                  <td>Status</td>
                </tr>
                <tr v-for="(item, index) in deviceResponses" :key="index">
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
                    <v-icon
                      v-if="
                        item?.sdk_response?.status == 200 || item?.status == 200
                      "
                      color="green"
                      >mdi-check</v-icon
                    >
                    <span v-else>
                      <v-icon color="red">mdi-close</v-icon>
                      {{ item?.sdk_response?.message }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </v-simple-table>
          </div>
          <div v-if="cameraResponses.length">
            <h3>Camera Response</h3>
            <pre>{{ cameraResponses }}</pre>
          </div>
          <!-- <div v-if="cameraResponses2.length">
            <h3>Camera Response</h3>
            <pre>{{ cameraResponses2 }}</pre>
          </div> -->
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
  props: ["deviceResponses", "cameraResponses", "cameraResponses2"],
  data() {
    return {
      uploadPersonResponseDialog: false,
    };
  },
};
</script>
