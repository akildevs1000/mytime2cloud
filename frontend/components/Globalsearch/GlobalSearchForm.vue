<template>
  <div class=" ">
    <!-- <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div> -->
    <v-container>
      <v-row>
        <v-col cols="5" style="max-width: 300px">
          <v-select
            label="Select"
            v-model="searchType"
            @change="changeSearchType"
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
        <v-col cols="5" style="max-width: 300px">
          <v-text-field
            ref="globalSearchTextbox"
            label="Enter Name/ID/Phone...."
            dense
            small
            outlined
            type="text"
            v-model="searchValue"
            clearable
          ></v-text-field>
        </v-col>
        <v-col cols="2"
          ><v-btn class="primary" small @click="globlaSearchProcess()"
            >Search</v-btn
          ></v-col
        >
      </v-row>

      <v-row>
        <v-col cols="12">
          <GlobalSearchEmployeesResults
            v-if="searchType == 'employee' && searchValue != ''"
            :data="data"
            :totalRowsCount="totalRowsCount"
          />
          <GlobalSearchVisitorResults
            v-if="searchType == 'visitor' && searchValue != ''"
            :data="data"
            :totalRowsCount="totalRowsCount"
          />
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import GlobalSearchEmployeesResults from "./GlobalSearchEmployeesResults.vue";
import GlobalSearchVisitorResults from "./GlobalSearchVisitorResults.vue";

export default {
  components: {
    GlobalSearchEmployeesResults,
    GlobalSearchVisitorResults,
  },
  data() {
    return {
      endpoint: "bankinfo",
      searchType: "employee",
      searchValue: "",
      data: [],
      totalRowsCount: 0,
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
    changeSearchType() {
      this.data = [];
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },
    globlaSearchProcess() {
      if (this.searchValue == "") {
        this.data = [];
        return;
      }
      let payload = {
        search_type: this.searchType,
        search_value: this.searchValue,
        company_id: this.$auth.user.company_id,
      };

      this.loadinglinear = true;
      this.$axios
        .post(`global-search`, payload)
        .then(({ data }) => {
          if (data.employees && this.searchType == "employee") {
            this.data = data.employees.data;
            this.totalRowsCount = data.employees.total;
          }
          if (data.visitors && this.searchType == "visitor") {
            this.data = data.visitors.data;
            this.totalRowsCount = data.visitors.total;
          }

          if (this.totalRowsCount > 0)
            this.$emit("global-search-results-updated", data);

          this.loadinglinear = false;
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
