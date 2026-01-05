<template>
  <div v-if="can('master')">
    <div v-if="!preloader">
      <v-data-table
        dense
        :headers="[
          { text: `name`, value: `name` },
          { text: `email`, value: `user.email` },
          { text: `location`, value: `location` },
          { text: `Employee Count`, value: `employees_count` },
          {
            text: `Action`,
            align: `center`,
            sortable: false,
            value: `options`,
          },
        ]"
        :items="data"
        :loading="loading"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [100, 500, 1000],
        }"
        class="elevation-1 pa-3"
      >
        <template v-slot:top>
          <v-toolbar flat dense class="mb-5">
            <v-toolbar-title>Companies</v-toolbar-title>

            <v-spacer></v-spacer>

            <v-text-field
              v-model="search"
              @input="searchIt(search)"
              style="max-width: 250px"
              height="30px"
              class="custom-text-field-height pt-7"
              color="black"
              outlined
              dense
              prepend-inner-icon="mdi-magnify"
              placeholder="Search"
            ></v-text-field>
            <v-btn
              small
              v-if="can('master')"
              dark
              class="ml-3 primary"
              to="/master/companies/create"
            >
              Add Company
            </v-btn>
          </v-toolbar>
        </template>
        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <div class="text-center">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </div>
            </template>
            <v-list width="120" dense>
              <v-list-item>
                <v-list-item-title>
                  <v-icon
                    v-if="can(`master`)"
                    @click="editItem(item)"
                    color="secondary"
                    small
                    >mdi-pencil</v-icon
                  >
                  Edit
                </v-list-item-title>
              </v-list-item>

              <v-list-item>
                <v-list-item-title>
                  <v-icon
                    v-if="can(`master`)"
                    @click="goDetails(item.id)"
                    color="secondary"
                    small
                    >mdi-eye</v-icon
                  >
                  View
                </v-list-item-title>
              </v-list-item>

              <v-list-item>
                <v-list-item-title>
                  <v-icon
                    title="Delete Company and Employyes data?"
                    v-if="can(`master`)"
                    @click="deleteItem(item)"
                    color="red"
                    small
                    >mdi-delete</v-icon
                  >
                  Delete
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </div>
    <Preloader v-else />
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  layout({ $auth }) {
    let { user_type } = $auth.user;
    if (user_type == "master") {
      return "master";
    } else if (user_type == "employee") {
      return "employee";
    } else if (user_type == "master") {
      return "default";
    }
  },

  data: () => ({
    options: {},
    endpoint: "company",
    search: "",
    preloader: true,
    loading: false,
    data: [],
    total: 0,
    next_page_url: "",
    prev_page_url: "",
    current_page: 1,
    per_page: 50,
  }),
  async created() {
    this.getDataFromApi();
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    goDetails(id) {
      this.$router.push(`/master/companies/details/${id}`);
      // this.$router.push(`/master/companies/${item.id}`);
    },

    searchIt(e) {
      // Clear the previous timer
      clearTimeout(this.debounceTimer);

      // Set a new timer
      this.debounceTimer = setTimeout(() => {
        if (e.length === 0) {
          this.getDataFromApi();
        } else if (e.length > 1) {
          this.getDataFromApi(`${this.endpoint}/search/${e}`);
        }
      }, 500); // 500ms delay
    },
    getDataFromApi(url = this.endpoint) {
      let options = {
        params: {
          per_page: this.per_page,
        },
      };

      this.$axios.get(`${url}`, options).then(({ data }) => {
        let { total, next_page_url, prev_page_url, current_page } = data;

        this.data = data.data;
        this.total = total;
        this.next_page_url = next_page_url;
        this.prev_page_url = prev_page_url;
        this.current_page = current_page;
        this.preloader = false;
      });
    },
    editItem(item) {
      this.$router.push(`/master/companies/${item.id}`);
    },
    deleteItem(item) {
      confirm("Are you sure you want to delete this item?") &&
        this.$axios.delete(this.endpoint + "/" + item.id).then((res) => {
          const index = this.data.indexOf(item);
          this.data.splice(index, 1);
        });
    },
  },
};
</script>
