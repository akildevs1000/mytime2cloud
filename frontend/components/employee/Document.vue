<template>
  <div class="mt-8">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-container>
      <v-row class="pl-1 mt-0 mb-5">
        <v-col cols="12">
          <v-card class="mb-5 rounded-md" elevation="0">
            <v-toolbar
              class="rounded-md"
              style="border-radius: 5px 5px 0px 0px"
              color="background"
              dense
              flat
              dark
            >
              <span> Documents List</span>

              <v-spacer></v-spacer>
              <v-toolbar-items>
                <v-btn
                  dark
                  small
                  class="primary toolbar-button-design"
                  @click="addDocumentInfo"
                >
                  Document&nbsp; <v-icon>mdi-plus</v-icon>
                </v-btn>
              </v-toolbar-items>
            </v-toolbar>

            <table class="employee-table" style="border: 1px solid #ddd">
              <v-progress-linear
                v-if="loading"
                :active="loading"
                :indeterminate="loading"
                absolute
                color="primary"
              ></v-progress-linear>
              <tr>
                <th>Title</th>
                <th>Download</th>
                <th>Delete</th>
              </tr>
              <tr v-for="(d, index) in document_list" :key="index">
                <td>
                  <span>{{ d.title }}</span>
                </td>
                <td>
                  <a :href="d.attachment" download target="_blank">
                    <v-icon color="primary"> mdi-download </v-icon>
                  </a>
                </td>
                <td>
                  <v-icon color="error" @click="delete_document(d.id)">
                    mdi-delete
                  </v-icon>
                </td>
              </tr>
            </table>
          </v-card>
        </v-col>
      </v-row>

      <v-form
        v-if="displayForm"
        class="mt-5"
        ref="form"
        method="post"
        v-model="valid"
        lazy-validation
      >
        <v-row v-for="(d, index) in Document.items" :key="index">
          <v-col cols="5">
            <label for="">Title <span color="error"></span></label>
            <v-text-field
              solo
              dense
              outlined
              v-model="d.title"
              :rules="TitleRules"
              label="Title"
            ></v-text-field>
            <span v-if="errors && errors.title" class="text-danger mt-2">{{
              errors.title[0]
            }}</span>
          </v-col>
          <v-col cols="5">
            <div class="form-group">
              <label for="">Title <span color="error"></span></label>
              <v-file-input
                solo
                dense
                outlined
                v-model="d.file"
                placeholder="Upload your file"
                label="Attachment"
                :rules="FileRules"
              >
                <template v-slot:selection="{ text }">
                  <v-chip v-if="text" small label color="primary">
                    {{ text }}
                  </v-chip>
                </template>
              </v-file-input>

              <span
                v-if="errors && errors.attachment"
                class="text-danger mt-2"
                >{{ errors.attachment[0] }}</span
              >
            </div>
          </v-col>
          <v-col cols="2">
            <div class="form-group">
              <v-btn
                dark
                class="error mt-5"
                fab
                @click="removeItem(index)"
                x-small
              >
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </div>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" class="text-right">
            <v-btn
              :disabled="!Document.items.length"
              class="primary"
              small
              @click="save_document_info"
              >Save</v-btn
            >
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </div>
</template>

<script>
export default {
  props: ["employeeId"],
  data() {
    return {
      loading: false,
      snackbar: false,
      valid: false,
      displayForm: false,
      documents: false,
      response: "",
      errors: [],
      FileRules: [
        (value) =>
          !value ||
          value.size < 200000 ||
          "File size should be less than 200 KB!",
      ],
      TitleRules: [(v) => !!v || "Title is required"],
      Document: {
        items: [{ title: "", file: "" }],
      },
      document_list: [],
    };
  },
  created() {
    this.getInfo(this.employeeId);
  },
  methods: {
    getInfo(id) {
      this.loading = true;
      this.$axios.get(`documentinfo/${id}`).then(({ data }) => {
        this.document_list = data;
        this.loading = false;
      });
    },
    can(item) {
      return true;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    addDocumentInfo() {
      // this.Document.items.push({
      //   title: "",
      //   file: "",
      // });
      this.valid = true;
      this.Document.items = [{ title: "", file: "" }];
      this.displayForm = true;
    },

    save_document_info() {
      this.loading = true;
      if (!this.$refs.form.validate()) {
        alert("Enter required fields!");
        return;
      }

      let options = {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      };
      let payload = new FormData();

      this.Document.items.forEach((e) => {
        payload.append(`items[][title]`, e.title);
        payload.append(`items[][file]`, e.file || {});
      });

      payload.append(`company_id`, this.$auth?.user?.company?.id);
      payload.append(`employee_id`, this.employeeId);

      this.$axios
        .post(`documentinfo`, payload, options)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = data.message;
            this.getDocumentInfo(this.employeeId);

            this.close_document_info();
            this.displayForm = false;
            this.loading = false;
          }
        })
        .catch((e) => console.log(e));
    },

    getDocumentInfo(id) {
      this.loading = true;
      this.$axios.get(`documentinfo/${id}`).then(({ data }) => {
        this.document_list = data;
        this.documents = false;
        this.loading = false;
      });
    },

    close_document_info() {
      this.documents = false;
      this.errors = [];
    },

    removeItem(index) {
      //this.Document.items.splice(index, 1);
      this.displayForm = false;
    },

    delete_document(id) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(`documentinfo/${id}`)
          .then(({ data }) => {
            this.loading = false;

            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.errors = [];
              this.snackbar = true;
              this.response = data.message;
              this.getDocumentInfo(this.employeeId);
              this.close_document_info();
            }
          })
          .catch((e) => console.log(e));
    },
  },
};
</script>
