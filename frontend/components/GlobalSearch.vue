<template>
  <div class=" ">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-container>
      <v-row>
        <v-col cols="5">
          <v-select
            label="Select"
            v-model="searchType"
            :items="[
              { name: 'Employee', value: 'employee' },
              { name: 'Visitor', value: 'visitor' },
            ]"
            dense
            placeholder="Select "
            outlined
            :hide-details="true"
            item-text="name"
            item-value="value"
          ></v-select>
        </v-col>
        <v-col cols="5">
          <v-text-field
            ref="globalSearchTextbox"
            label="Enter Name/ID/Phone/Email.. Etc.."
            dense
            small
            outlined
            type="text"
            v-model="searchValue"
          ></v-text-field>
        </v-col>
        <v-col cols="2"
          ><v-btn class="primary" small @click="globlaSearchProcess()"
            >Search</v-btn
          ></v-col
        >
      </v-row>
    </v-container>
  </div>
</template>

<script>
export default {
  data() {
    return {
      endpoint: "bankinfo",
      searchType: "",
      searchValue: "",
    };
  },
  mounted() {
    this.$refs.globalSearchTextbox.focus();
  },
  created() {},
  methods: {
    // getInfo() {
    //   this.$axios
    //     .get(`${this.endpoint}/${this.employeeId}`)
    //     .then(({ data }) => {
    //       //this.data = data;

    //       this.data = {
    //         bank_name: data.bank_name,
    //         address: data.address,
    //         account_no: data.account_no,
    //         account_title: data.account_title,
    //         iban: data.iban,
    //         other_text: data.other_text,
    //         other_value: data.other_value,
    //       };
    //     })
    //     .catch((err) => {
    //       console.log(err);
    //     });
    // },

    globlaSearchProcess() {
      let payload = {
        search_type: this.searchType,
        search_value: this.searchValue,
      };
      this.$axios
        .post(`global-search`, payload)
        .then(({ data }) => {
          this.loading = false;
        })
        .catch((e) => console.log(e));
    },
    // close_bank_info() {
    //   this.popup = false;
    //   this.errors = [];
    //   setTimeout(() => {}, 300);
    // },
  },
};
</script>
