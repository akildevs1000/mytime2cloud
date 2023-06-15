<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>

    <v-container>
      <v-row>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("local address") }}</label>
            <input v-model="contactItem.local_address" class="form-control" />
            <span v-if="errors && errors.local_address" class="text-danger mt-2">{{ errors.local_address[0] }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps(" email") }}</label>
            <input v-model="contactItem.local_email" class="form-control" type="email" />
            <span v-if="errors && errors.local_email" class="text-danger mt-2">{{ errors.local_email[0] }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("phone number") }}</label>
            <input v-model="contactItem.phone_number" class="form-control" type="number" />
            <span v-if="errors && errors.phone_number" class="text-danger mt-2">{{ errors.phone_number[0] }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("whatsapp number") }}</label>
            <input v-model="contactItem.whatsapp_number" class="form-control" type="number" />
            <span v-if="errors && errors.whatsapp_number" class="text-danger mt-2">{{ errors.whatsapp_number[0] }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{
              caps("phone relative number")
            }}</label>
            <input v-model="contactItem.phone_relative_number" class="form-control" type="number" />
            <span v-if="errors && errors.phone_relative_number" class="text-danger mt-2">{{
              errors.phone_relative_number[0] }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("relation") }}</label>
            <input v-model="contactItem.relation" class="form-control" type="text" />
            <span v-if="errors && errors.relation" class="text-danger mt-2">{{
              errors.relation[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("local city") }}</label>
            <input v-model="contactItem.local_city" class="form-control" type="text" />
            <span v-if="errors && errors.local_city" class="text-danger mt-2">{{
              errors.local_city[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("local country") }}</label>
            <input v-model="contactItem.local_country" class="form-control" type="text" />
            <span v-if="errors && errors.local_country" class="text-danger mt-2">{{ errors.local_country[0] }}</span>
          </div>
        </v-col>

        <v-col cols="12">
          <a href="javascrip:void(0)" @click="add_other_contact_info = !add_other_contact_info">{{
            caps(
              `${add_other_contact_info ? "hide" : "show"
              } Home Country Details`
            )
          }}</a>
        </v-col>
      </v-row>

      <v-row v-if="add_other_contact_info">
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps(" address") }}</label>
            <input v-model="contactItem.home_address" class="form-control" type="text" />
            <span v-if="errors && errors.home_address" class="text-danger">{{
              errors.home_address[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("tel") }}</label>
            <input v-model="contactItem.home_tel" class="form-control" type="number" />
            <span v-if="errors && errors.home_tel" class="text-danger">{{
              errors.home_tel[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps(" mobile") }}</label>
            <input v-model="contactItem.home_mobile" class="form-control" type="number" />
            <span v-if="errors && errors.home_mobile" class="text-danger">{{
              errors.home_mobile[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps(" fax") }}</label>
            <input v-model="contactItem.home_fax" class="form-control" type="number" />
            <span v-if="errors && errors.home_fax" class="text-danger">{{
              errors.home_fax[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps(" city") }}</label>
            <input v-model="contactItem.home_city" class="form-control" type="text" />
            <span v-if="errors && errors.home_city" class="text-danger">{{
              errors.home_city[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps("state") }}</label>
            <input v-model="contactItem.home_state" class="form-control" type="text" />
            <span v-if="errors && errors.home_state" class="text-danger">{{
              errors.home_state[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps(" country") }}</label>
            <input v-model="contactItem.home_country" class="form-control" type="text" />
            <span v-if="errors && errors.home_country" class="text-danger">{{
              errors.home_country[0]
            }}</span>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="form-group">
            <label class="col-form-label">{{ caps(" email") }}</label>
            <input v-model="contactItem.home_email" class="form-control" type="email" />
            <span v-if="errors && errors.home_email" class="text-danger">{{
              errors.home_email[0]
            }}</span>
          </div>
        </v-col>
        <span v-if="errors && errors.length" class="error--text">{{
          errors
        }}</span>
      </v-row>

      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn class="primary" small @click="save_contact_info">Save</v-btn>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import KeyValueTable from "./KeyValueTable.vue";

export default {
  components: { KeyValueTable },
  props: ["employeeId"],
  data() {
    return {
      add_other_contact_info: false,
      contact_info: false,
      response: "",
      snackbar: false,
      errors: [],
      contactItem: {},
      table_data: {},
      table_data1: {},
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    getInfo() {
      this.$axios
        .get(
          `employee/${this.employeeId}?company_id=${this.$auth.user.company.id}`
        )
        .then(async ({ data }) => {
          this.table_data = {
            Email: data.local_email,
            Address: data.local_address,
            "Whatsapp Number": data.whatsapp_number,
            "Phone Number": data.phone_number,
            "Phone Relative Number": data.phone_relative_number,
            Relation: data.relation,
            City: data.local_city,
            Country: data.local_country,
          };
          this.table_data1 = {
            Email: data.home_email,
            Fax: data.home_fax,
            Telephone: data.home_tel,
            "Phone Number": data.home_mobile,
            Address: data.home_address,
            City: data.home_city,
            State: data.home_state,
            Country: data.home_country,
          };
          this.contactItem = data;
        });
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    close_contact_info() {
      this.contact_info = false;
      this.errors = [];
      setTimeout(() => { }, 300);
    },

    can(item) {
      return true;
    },

    save_contact_info() {
      let payload = {
        ...this.contactItem,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.employeeId,
      };
      this.$axios
        .post(`employee/update/contact`, payload)
        .then(({ data }) => {
          this.loading = false;
          this.$emit("eventFromchild");
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.getInfo();
            this.close_contact_info();


          }
        })
        .catch((e) => console.log(e));
    },
  },
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
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #fbfdff;
}
</style>
