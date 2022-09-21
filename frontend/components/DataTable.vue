<template>
  <v-data-table
    :headers="headers"
    :items="data"
    hide-default-footer
    :loading="loading"
    class="elevation-1"
  >
    <template v-slot:top>
      <v-toolbar dark class="primary"> {{ title }} </v-toolbar>

      <v-toolbar flat>
        <v-text-field
          @input="searchIt"
          v-model="search"
          label="Search"
          single-line
          hide-details
        ></v-text-field>
      </v-toolbar>
    </template>
    <template v-slot:item.action="{ item }">
      <v-icon color="secondary" small class="mr-2" @click="editItem(item)">
        mdi-pencil
      </v-icon>
      <v-icon color="error" small @click="deleteItem(item)">
        mdi-delete
      </v-icon>
    </template>
  </v-data-table>
</template>
<script>
export default {
  props: ["title", "headers", "endpoint"],
  data() {
    return {
      ids: [],
      data: [],
      search: "",
      loading: false,

      params: {
        perPage: 50
      }
    };
  },
  created() {
    this.getDataFromApi();
  },

  methods: {
    searchIt(e) {
      if (e.length == 0) {
        this.getDataFromApi();
      } else if (e.length > 2) {
        this.getDataFromApi(`${this.endpoint}/search/${e}`);
      }
    },
    getDataFromApi(url = this.endpoint) {
      this.loading = true;
      this.$axios.get(url, { params: this.params }).then(({ data }) => {
        console.log(data.data);
        this.data = data.data;
        this.loading = false;
      });
    },
    delteteSelectedRecords() {
      let just_ids = this.ids.map(e => e.id);
      confirm(
        "Are you sure you wish to delete selected records , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .post(`${this.endpoint}/delete/selected`, {
            ids: just_ids
          })
          .then(res => {
            if (!res.data.status) {
              this.errors = res.data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = res.data.status;
              this.ids = [];
              this.response.msg = "Selected records has been deleted";
            }
          })
          .catch(err => console.log(err));
    }
  }
};
</script>
