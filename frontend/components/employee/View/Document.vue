<template>
  <v-card flat v-if="can('access')">
    <v-dialog persistent v-model="imageViewerDialog" width="800px">
      <WidgetsClose @click="imageViewerDialog = false" left="790" />
      <v-card style="max-height: 80vh; overflow-y: auto">
        <v-alert flat dense class="grey lighten-3"> Image View </v-alert>
        <v-container>
          <div class="text-center">
            <v-avatar tile size="600">
              <img :src="imageViewerSrc" alt="imageViewerSrc" />
            </v-avatar>
          </div>
        </v-container>
      </v-card>
    </v-dialog>

    <v-dialog persistent v-model="dialogUploadDocuments" width="400px">
      <WidgetsClose @click="dialogUploadDocuments = false" left="390" />
      <v-card>
        <v-alert flat dense class="grey lighten-3"> Documents </v-alert>
        <v-form
          v-if="displayForm"
          ref="form"
          method="post"
          v-model="valid"
          lazy-validation
        >
          <v-card-text>
            <div class="pa-1" v-for="(d, index) in Document.items" :key="index">
              <div style="display: flex; gap: 15px">
                <div style="width: 45%">
                  <v-text-field
                    outlined
                    small
                    type="text"
                    class="employee-schedule-search-box"
                    label="Title"
                    dense
                    v-model="d.title"
                    :rules="TitleRules"
                    hide-details
                  ></v-text-field>
                  <span
                    v-if="errors && errors.title"
                    class="text-danger mt-2"
                    >{{ errors.title[0] }}</span
                  >
                </div>
                <div style="width: 45%">
                  <v-file-input
                    class="employee-schedule-search-box"
                    outlined
                    prepend-icon=""
                    append-icon=""
                    label="Attachment"
                    dense
                    v-model="d.file"
                    placeholder="Upload your file"
                    :rules="FileRules"
                    hide-details
                    :clearable="false"
                  >
                    <template v-slot:selection="{ text }">
                      <v-chip v-if="text" x-small label color="primary">
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
                <div style="width: 10%" class="text-right">
                  <span v-if="Document.items.length - 1 == index">
                    <v-icon color="grey" @click="addDocumentFile"
                      >mdi-plus-circle</v-icon
                    >
                  </span>
                  <v-icon v-else color="error" @click="removeItem(index)"
                    >mdi-close</v-icon
                  >
                </div>
              </div>
            </div>
            <v-row class="mt-5">
              <v-col cols="12" class="text-right">
                <v-btn
                  :disabled="!Document.items.length"
                  class="grey white--text"
                  x-small
                  @click="save_document_info"
                  >Cancel</v-btn
                >
                <v-btn
                  :disabled="!Document.items.length"
                  class="primary"
                  x-small
                  @click="save_document_info"
                  >Save</v-btn
                >
              </v-col>
            </v-row>
          </v-card-text>
        </v-form>
      </v-card>
    </v-dialog>
    <v-card-title
      >Documents <v-spacer></v-spacer>
      <v-btn v-if="can('create')" dark x-small class="primary" @click="addDocumentInfo">
        <v-icon x-small>mdi-plus</v-icon> Add
      </v-btn></v-card-title
    >
    <v-data-table v-if="can('view')"
      dense
      :headers="headers_table"
      :items="document_list"
      model-value="data.id"
      :loading="loading"
      hide-default-footer
      :footer-props="{
        itemsPerPageOptions: [10, 50, 100, 500, 1000],
      }"
      class="elevation-0"
    >
      <template v-slot:item.sno="{ item, index }">
        {{ index + 1 }}
      </template>
      <template v-slot:item.title="{ item }">
        {{ item.title }}
      </template>
      <template v-slot:item.created_at="{ item }">
        {{ item.created_at || "30 jan 2025" }}
      </template>
      <template v-slot:item.action="{ item }">
        <a v-if="can('view')"
          title="Download Profile Picture"
          :href="getDonwloadLink(item.employee_id, item.attachment)"
          ><v-icon small color="violet">mdi-download</v-icon></a
        >

        <v-icon v-if="can('view')"
          small
          color="violet"
          @click="openImageViewer(item.employee_id, item.attachment)"
        >
          mdi-eye
        </v-icon>

        <v-icon v-if="can('delete')" small color="error" @click="delete_document(item.id)">
          mdi-delete
        </v-icon>
      </template>
    </v-data-table>
  </v-card>
  <NoAccess v-else />
</template>

<script>
export default {
  props: ["employeeId"],
  data() {
    return {
      imageViewerDialog: false,
      imageViewerSrc: null,
      dialogUploadDocuments: false,
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
      headers_table: [
        {
          text: "Ref #",
          align: "left",
          sortable: false,
          key: "sno",
          value: "sno",
        },
        {
          text: "Title",
          align: "left",
          sortable: false,
          key: "title",
          value: "title",
        },
        {
          text: "Created Date",
          align: "left",
          sortable: false,
          key: "frequency",
          value: "created_at",
        },
        {
          text: "Action",
          align: "left",
          sortable: false,
          key: "action",
          value: "action",
        },
      ],
    };
  },
  created() {
    this.getInfo(this.employeeId);
  },
  methods: {
    openImageViewer(pic, file_name) {
      this.imageViewerDialog = true;
      this.imageViewerSrc =
        process.env.BACKEND_URL +
        "/download-emp-documents/" +
        pic +
        "/" +
        file_name;
    },
    getInfo(id) {
      this.loading = true;
      this.$axios.get(`documentinfo/${id}`).then(({ data }) => {
        this.document_list = data;
        this.loading = false;
      });
    },
    can(per) {
      return this.$pagePermission.can("employee_document_" + per, this);
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
      this.dialogUploadDocuments = true;
      // this.Document.items.push({
      //   title: "",
      //   file: "",
      // });
      this.valid = true;
      this.Document.items = [{ title: "", file: "" }];
      this.displayForm = true;
    },

    addDocumentFile() {
      this.Document.items.push({
        title: "",
        file: "",
      });
    },

    save_document_info() {
      if (!this.$refs.form.validate()) {
        alert("Enter required fields!");
        return;
      }
      this.loading = true;
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
          this.dialogUploadDocuments = false;
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Document saved successfully"; //data.message;
            this.getDocumentInfo(this.employeeId);

            // this.close_document_info();
            // this.displayForm = false;
            this.loading = false;
          }
        })
        .catch((e) => console.log(e));
    },
    getDonwloadLink(pic, file_name) {
      return (
        process.env.BACKEND_URL +
        "/download-emp-documents/" +
        pic +
        "/" +
        file_name
      );
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
      this.$emit("close-popup");
    },

    removeItem(index) {
      this.Document.items.splice(index, 1);
      //this.displayForm = false;
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
