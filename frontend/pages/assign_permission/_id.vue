<template>
  <div v-if="can(`assign_permission_access`)">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ msg }}
      </v-snackbar>
    </div>

    <v-card>
      <v-form ref="form" lazy-validation>
        <v-card-text>
          <v-row>
            <v-col>
              <div class="display-1 pa-2">Assign Permissions</div>
            </v-col>
            <v-col>
              <div class="display-1 pa-2 text-right">
                <v-btn small class="primary" to="/assign_permission">
                  <v-icon small>mdi-arrow-left</v-icon>&nbsp;Back
                </v-btn>
              </div>
            </v-col>
          </v-row>
          <v-container>
            <v-row>
              <v-col cols="12">
                <v-select
                  :rules="Rules"
                  v-model="role_id"
                  :items="roles"
                  item-value="id"
                  item-text="name"
                  label="Role*"
                ></v-select>
              </v-col>

              <v-col cols="12" v-for="(pa, idx) in permissions" :key="idx">
                <v-checkbox
                  :key="pa.id"
                  :value="pa.id"
                  v-model="permission_ids"
                  :label="`${pa.name}`"
                >
                </v-checkbox>
              </v-col>

              <v-col v-if="errors && errors.length > 0" cols="12">
                <ul>
                  <li class="error--text" v-for="(err, i) in errors" :key="i">
                    {{ err }}
                  </li>
                </ul>
              </v-col>

              <v-col>
                <v-btn
                  v-if="can(`assign_permission_edit`)"
                  dark
                  small
                  color="primary"
                  class="mr-4"
                  @click="save"
                >
                  Submit
                </v-btn>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
      </v-form>

      <template v-slot:item.action="{ item }">
        <v-icon small class="mr-2" @click="editItem(item)">
          mdi-pencil
        </v-icon>
        <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon>
      </template>
    </v-card>
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    role_id: "",
    permission_ids: [],
    permissions: [],
    msg: "",
    snackbar: false,
    Rules: [v => !!v || "This field is required"],
    errors: [],
    roles: [
      // {id:9, name:"fffff"}
    ]
  }),
  created() {
    this.$axios
      .get("assign-permission/" + this.$route.params.id)
      .then(({ data }) => {
        this.role_id = data.role_id;
        this.permission_ids = data.permission_ids;
      })
      .catch(err => console.log(err));

    this.$axios
      .get("permission")
      .then(({ data }) => {
        this.permissions = data;
      })
      .catch(err => console.log(err));

    let options = {
      company_id: this.$auth.user.company.id
    };

    this.$axios
      .get("role", { params: options })
      .then(({ data }) => {
        this.roles = data.data;
      })
      .catch(err => console.log(err));
  },
  methods: {
    can(per) {
      let u = this.$auth.user;
      return (
        (u && u.permissions.some(e => e.name == per || per == "/")) ||
        u.is_master
      );
    },
    save() {
      this.errors = [];
      let payload = {
        role_id: this.role_id,
        permission_ids: this.permission_ids
      };
      this.$axios
        .put("assign-permission/" + this.$route.params.id, payload)
        .then(({ data }) => {
          this.msg = "Permissions has been assigned";
          this.snackbar = true;
          setTimeout(() => this.$router.push("/assign_permission"), 2000);
        });
    }
  }
};
</script>
