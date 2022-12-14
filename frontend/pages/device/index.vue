<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-row class="mt-5 mb-5">
      <v-col cols="6">
        <h3>Device</h3>
        <div>Dashboard / Device</div>
      </v-col>
      <v-col cols="6"> </v-col>
    </v-row>

    <v-row>
      <v-col xs="12" sm="12" md="3" cols="12">
        <v-select
          class="form-control"
          @change="getDataFromApi(`device`)"
          v-model="pagination.per_page"
          :items="[10, 25, 50, 100]"
          placeholder="Per Page Records"
          solo
          hide-details
          flat
        ></v-select>
      </v-col>
      <v-col xs="12" sm="12" md="3" cols="12">
        <v-text-field
          class="form-control py-0 custom-text-box floating shadow-none"
          placeholder="Search..."
          solo
          flat
          @input="searchIt"
          v-model="search"
          hide-details
        ></v-text-field>
      </v-col>
    </v-row>
    <v-card class="mb-5 rounded-md mt-3" elevation="0">
      <v-toolbar class="rounded-md" color="background" dense flat dark>
        <span> {{ Model }} List</span>
      </v-toolbar>
      <table>
        <tr>
          <th v-for="(item, index) in headers" :key="index">
            <span v-html="item.text"></span>
          </th>
        </tr>
        <v-progress-linear
          v-if="loading"
          :active="loading"
          :indeterminate="loading"
          absolute
          color="primary"
        ></v-progress-linear>
        <tr v-for="(item, index) in data" :key="index">
          <td class="ps-3">
            <b>{{ ++index }}</b>
          </td>
          <td>{{ caps(item.name) }}</td>
          <td>{{ caps(item.short_name) }}</td>
          <td>{{ caps(item.location) }}</td>
          <td>{{ caps(item.device_id) }}</td>
          <td>{{ caps(item.device_type) }}</td>
          <td>
            <v-chip
              small
              class="p-2 mx-1"
              :color="item.status.name == 'active' ? 'primary' : 'error'"
            >
              {{ item.status.name == "active" ? "online" : "offline" }}
            </v-chip>

            <v-chip
              small
              class="p-2 mx-1"
              color="primary"
              @click="open_door(item.device_id)"
            >
              Open Door
            </v-chip>

            <v-chip
              small
              class="p-2 mx-1"
              color="primary"
              @click="open_door_always(item.device_id)"
            >
              Always Open Door
            </v-chip>

            <v-chip
              small
              class="p-2 mx-1"
              color="error"
              @click="close_door(item.device_id)"
            >
              Close Door
            </v-chip>
          </td>

          <td>
            <v-chip
              small
              class="p-2 mx-1"
              @click="sync_date_time(item.device_id)"
              :color="'primary'"
            >
              {{
                item.sync_date_time == "---"
                  ? "click to sync"
                  : item.sync_date_time
              }}
            </v-chip>
          </td>
        </tr>
      </table>
    </v-card>
    <v-row>
      <v-col md="12" class="float-right">
        <div class="float-right">
          <v-pagination
            v-model="pagination.current"
            :length="pagination.total"
            @input="onPageChange"
            :total-visible="12"
          ></v-pagination>
        </div>
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  data: () => ({
    Model: "Device",
    pagination: {
      current: 1,
      total: 0,
      per_page: 10
    },
    options: {},
    endpoint: "device",
    search: "",
    snackbar: false,
    dialog: false,
    data: [],
    loading: false,
    total: 0,
    headers: [
      { text: "&nbsp #" },
      { text: "Name" },
      { text: "Short Name" },
      { text: "Location" },
      { text: "Device Id" },
      { text: "Type" },
      { text: "Status" },
      { text: "Time Sync" }
    ],
    editedIndex: -1,
    response: "",
    errors: []
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New device" : "Edit device";
    }
  },

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    }
  },
  created() {
    this.loading = true;
    this.getDataFromApi();
  },

  methods: {
    sync_date_time(device_id) {
      let dt = new Date();

      let year = dt.getFullYear();
      let month = dt.getMonth() + 1;
      let day = dt.getDate();

      let hours = dt.getHours();
      hours = hours < 10 ? "0" + hours : hours;

      let minutes = dt.getMinutes();
      minutes = minutes < 10 ? "0" + minutes : minutes;

      let seconds = dt.getSeconds();
      seconds = seconds < 10 ? "0" + seconds : seconds;

      let sync_able_date_time = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

      this.$axios
        .get(`${process.env.SDK_ENDPOINT}/${device_id}/SyncDateTime`, options)
        .then(({ data }) => {
          this.snackbar = true;
          if (data.status == 200) {
            this.response = data.message;
            this.getDataFromApi();
            return;
          }
          this.response =
            "The device is not connected to the server or is not registered";
          return;
        });

      return;

      let options = {
        params: {
          sync_able_date_time: sync_able_date_time
        }
      };

      this.$axios
        .get(`sync_device_date_time/${device_id}`, options)
        .then(({ data }) => {
          console.log(data, sync_able_date_time);
          this.snackbar = true;
          this.response = data.message;
          this.getDataFromApi();
        });
    },
    open_door(device_id) {
      let options = {
        params: { device_id }
      };
      this.$axios.get(`open_door`, options).then(({ data }) => {
        console.log(data);
      });
    },
    open_door_always(device_id) {
      let options = {
        params: { device_id }
      };
      this.$axios.get(`open_door_always`, options).then(({ data }) => {
        console.log(data);
      });
    },
    close_door(device_id) {
      let options = {
        params: { device_id }
      };
      this.$axios.get(`close_door`, options).then(({ data }) => {
        console.log(data);
      });
    },
    can(permission) {
      let user = this.$auth;
      return;
      return (
        (user && user.permissions.some(e => e.permission == permission)) ||
        user.master
      );
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, c => c.toUpperCase());
      }
    },
    onPageChange() {
      this.getDataFromApi();
    },
    getDataFromApi(url = this.endpoint) {
      this.loading = true;
      let page = this.pagination.current;
      let options = {
        params: {
          per_page: this.pagination.per_page,
          company_id: this.$auth.user.company.id
        }
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.pagination.current = data.current_page;
        this.pagination.total = data.last_page;
        this.loading = false;
      });
    },

    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    }
  }
};
</script>

<style scoped>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  text-align: left;
  padding: 7px;
}

tr:nth-child(even) {
  background-color: #e9e9e9;
}
</style>
