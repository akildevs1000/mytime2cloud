<template>
  <v-card flat v-if="qualification" class="d-flex flex-column">
    <v-card-title
      >Qualification<v-spacer></v-spacer>
      <div v-if="can(!editForm ? 'employee_qualification_edit' : 'employee_qualification_view')">
        <v-icon small color="primary" @click="editForm = !editForm"
          >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
        >
      </div>
    </v-card-title>
    <v-simple-table
      v-if="can('employee_qualification_view')"
      dense
      flat
      class="my-simple-table"
    >
      <tbody>
        <tr>
          <td style="width: 200px">Certificate</td>
          <td>
            <span v-if="!editForm">{{ qualification.certificate }}</span>
            <v-text-field
              v-else
              autofocus
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="qualification.certificate"
              color="primary"
              :hide-details="!errors.certificate"
              :error-messages="
                errors && errors.certificate ? errors.certificate[0] : ''
              "
            />
          </td>
        </tr>
        <tr>
          <td style="width: 200px">College</td>
          <td>
            <span v-if="!editForm">{{ qualification.collage }}</span>
            <v-text-field
              v-else
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="qualification.collage"
              color="primary"
              :hide-details="!errors.collage"
              :error-messages="
                errors && errors.collage ? errors.collage[0] : ''
              "
            />
          </td>
        </tr>
        <tr>
          <td>Start Date</td>
          <td>
            <span v-if="!editForm">{{ qualification.start }}</span>

            <v-menu
              v-else
              ref="menu"
              v-model="menu_start"
              :close-on-content-click="false"
              :return-value.sync="menu_start"
              transition="scale-transition"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  append-icon="mdi-calendar"
                  class="small-input-font"
                  style="border-bottom: 1px solid #eaeaea"
                  dense
                  v-model="qualification.start"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                  :hide-details="!errors.start"
                  :error-messages="
                    errors && errors.start ? errors.start[0] : ''
                  "
                ></v-text-field>
              </template>
              <v-date-picker
                v-model="qualification.start"
                no-title
                scrollable
                @input="menu_start = false"
              ></v-date-picker>
            </v-menu>
          </td>
        </tr>
        <tr>
          <td>End Date</td>
          <td>
            <span v-if="!editForm">{{ qualification.end }}</span>

            <v-menu
              v-else
              v-model="menu_end"
              :close-on-content-click="false"
              :return-value.sync="menu_end"
              transition="scale-transition"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  append-icon="mdi-calendar"
                  class="small-input-font"
                  style="border-bottom: 1px solid #eaeaea"
                  dense
                  v-model="qualification.end"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                  :hide-details="!errors.end"
                  :error-messages="errors && errors.end ? errors.end[0] : ''"
                ></v-text-field>
              </template>
              <v-date-picker
                v-model="qualification.end"
                no-title
                scrollable
                @input="menu_end = false"
              ></v-date-picker>
            </v-menu>
          </td>
        </tr>
        <tr>
          <td style="width: 200px">Type</td>
          <td>
            <span v-if="!editForm">{{ qualification.type }}</span>
            <v-text-field
              v-else
              :readonly="!editForm"
              class="small-input-font"
              style="border-bottom: 1px solid #eaeaea"
              dense
              v-model="qualification.type"
              color="primary"
              :hide-details="!errors.type"
              :error-messages="errors && errors.type ? errors.type[0] : ''"
            />
          </td>
        </tr>
      </tbody>
    </v-simple-table>

    <v-card-actions class="mt-auto">
      <v-spacer></v-spacer>
      <v-btn
        :disabled="!editForm"
        x-small
        class="grey white--text"
        @click="close"
        >Cancel</v-btn
      >
      <v-btn
        :disabled="!editForm"
        x-small
        class="primary"
        :loading="loading"
        @click="submit"
        >Save</v-btn
      >
    </v-card-actions>
  </v-card>
  <NoAccess v-else />
</template>

<script>
export default {
  props: ["employeeId"],
  data() {
    return {
      editForm: false,
      snackbar: false,
      response: "",
      errors: [],
      menu: false,
      qualification: null,
      menu_start: false,
      menu_end: false,
      loading: false,
    };
  },
  created() {
    this.getQualificationInfo(this.employeeId);
  },
  methods: {
    getQualificationInfo(id) {
      this.$axios.get(`qualification/${id}`).then(({ data }) => {
        this.qualification = {
          ...data,
          employee_id: id,
        };
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
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    submit() {
      this.loading = true;

      let payload = {
        ...this.qualification,
        company_id: this.$auth?.user?.company?.id,
        employee_id: this.qualification.employee_id,
      };

      this.$axios
        .post(`qualification`, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.$emit("eventFromchild");
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.close();
          }
        })
        .catch((e) => {
          console.log(e);
          this.loading = false;
        });
    },

    close() {
      this.errors = [];
      setTimeout(() => {
        this.$emit("close-popup");
      }, 1000);
    },
  },
};
</script>
