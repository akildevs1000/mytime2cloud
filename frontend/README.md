:hide-details="!errors.field_name"
:error-messages="errors && errors.field_name && errors.field_name[0]"

## Versions 1.0.0

Installation Guide:

- clone repo
- npm i
- set env variables
  - LOCAL_IP=192.168.2.174
  - LOCAL_PORT=3000
  - BACKEND_URL=http://localhost:8000/api
  - SOCKET_ENDPOINT=web socket endpoint
- npm run dev (dev environment)
- npm run build && npm run start (production environment)
- pm2 start "npm run start" --name "Nuxt"
- pm2 start "dotnet FCardProtocolAPI.dll" --name "sdk production"

npm install cropper

vue.js version2 crop image file

npm install --save vue-notification

<v-dialog v-model="dialogVisible" max-width="500px">
  <v-card flat dense class="white--text">
    <v-card-title class="background">
      <span class="headline">Filter</span>
    </v-card-title>
    <v-progress-linear
      v-if="loadinglinear"
      indeterminate
      color="primary"
    ></v-progress-linear>

    <br />

    <v-card-text>
      <v-autocomplete
        outlined
        dense
        @change="getDataFromApi()"
        x-small
        item-value="id"
        item-text="name"
        v-model="department_filter_id"
        :items="[{ name: `All Departments`, id: `` }, ...departments]"
        placeholder="Department"
      ></v-autocomplete>
    </v-card-text>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn dark color="background" @click="dialogVisible = false"
        >Close</v-btn
      >
    </v-card-actions>

  </v-card>
</v-dialog>

<v-icon @click="dialogVisible = true" class="mx-1 white--text"

> mdi mdi-filter</v-icon

<!-- dialogVisible: false, -->
