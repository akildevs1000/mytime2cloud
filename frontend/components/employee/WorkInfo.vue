<template>
  <div class="mt-15">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" color="background">
        {{ response }}
      </v-snackbar>
    </div>
    <div style="text-align: right;" class="pr-5">
      <v-icon
        @click="editItem"
        small
        class="grey"
        style="border-radius: 50%; padding: 5px;"
        color="secondary"
        >mdi-pencil</v-icon
      >
    </div>

    <KeyValueTable :data="table_data" :hideEditBtn="true" />
  </div>
</template>

<script>
import KeyValueTable from "./KeyValueTable.vue";

export default {
  components: { KeyValueTable },
  props: ["employeeId"],
  data() {
    return {
      response: "",
      snackbar: false,
      work: {},
      table_data: {}
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    getInfo() {
      this.$axios
        .get(
          `employee/${this.employeeId}`
        )
        .then(async ({ data }) => {
          
          this.table_data = {
            Role: await data.role.name,
            EID: data.employee_id,
            Name: data.display_name,
            Department: await data.department.name,
            "Sub Department": (await data.sub_department)
              ? data.sub_department.name
              : "---",
            Email: await data.user.email,
            "Whatsapp Number": data.whatsapp_number,
            "Joining Date": data.joining_date
          };

          this.work = data;
        });
    },
    editItem() {
      this.$router.push(`/employees/${this.employeeId}`);
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, c => c.toUpperCase());
      }
    },
    update_setting() {
      let payload = {
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.setting.employee_id,
        status: this.setting.status,
        overtime: this.setting.overtime,
        mobile_application: this.setting.mobile_application
      };
      console.log(payload);
      // return;
      this.$axios
        .post(`employee/update/setting`, payload)
        .then(({ data }) => {
          this.loading = false;
          console.log(data);

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Setting has been successfully updated";
            console.log("success");
          }
        })
        .catch(e => console.log(e));
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
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #fbfdff;
}
</style>
