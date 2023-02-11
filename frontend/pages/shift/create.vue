<template>
  <div v-if="can(`shift_create`)">
    <v-row class="mt-5 mb-10">
      <v-col md="6">
        <h3>{{ Model }}</h3>
        <div>Dashboard / {{ Model }} / Create</div>
      </v-col>
      <v-col md="6">
        <div class="text-right">
          <v-btn x-small dark fab color="background" to="/shift">
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-card>
      <v-stepper v-model="e1">
        <v-stepper-header>
          <v-stepper-step :complete="e1 > 1" step="1">
            Shift Type
          </v-stepper-step>

          <v-divider></v-divider>

          <v-stepper-step :complete="e1 > 2" step="2">
            Shift Details
          </v-stepper-step>
        </v-stepper-header>

        <v-stepper-items>
          <v-stepper-content step="1">
            <v-card flat>
              <v-card-text>
                <v-row class="mx-auto">
                  <v-col cols="12">
                    Select Shift Type <span class="error--text">*</span>
                    <v-skeleton-loader
                      v-if="shift_types && !shift_types.length"
                      type="card"
                    />
                    <v-radio-group v-model="shift_type_id" row>
                      <v-radio
                        v-for="(shift_type, index) in shift_types"
                        :key="index"
                        :label="`${shift_type.name}`"
                        :value="shift_type.id"
                      ></v-radio>
                    </v-radio-group>
                  </v-col>
                  <v-col cols="12">
                    <div class="text-left">
                      <v-btn
                        dark
                        small
                        color="primary"
                        @click="(e1 = 2), getComponent()"
                      >
                        Continue
                      </v-btn>
                    </div>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-stepper-content>

          <v-stepper-content step="2">
            <v-card flat>
              <v-btn x-small dark fab color="background" @click="e1 = 1">
                <v-icon>mdi-arrow-left</v-icon>
              </v-btn>
              <v-card-text>
                <v-row>
                  <v-col cols="12">
                    <component :shift_type_id="shift_type_id" :is="comp" />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-stepper-content>
        </v-stepper-items>
      </v-stepper>
    </v-card>
  </div>
  <NoAccess v-else />
</template>

<script>
export default {
  data: () => ({
    Model: "Shift",
    comp: null,
    shift_types: [],
    e1: 1,
    shift_type_id: 6
  }),

  created() {
    let options = {
      per_page: 1000,
      company_id: this.$auth.user.company.id
    };

    this.$axios.get("shift_type", { params: options }).then(({ data }) => {
      this.shift_types = data;
    });
  },
  watch: {},
  computed: {},
  methods: {
    getComponent() {
      switch (this.shift_type_id) {
        case 6:
          this.comp = "SingleShift";
          break;
        case 5:
          this.comp = "SplitShift";
          break;
        case 4:
          this.comp = "OverNightShift";
          break;
        case 3:
          this.comp = "AutoShift";
          break;
        case 2:
          this.comp = "MultiInOutShift";
          break;
        default:
          this.comp = "FiloShift";
          break;
      }
    },
    can(per) {
      const user = this.$auth.user;

      if (!user) return false;

      if (user.is_master) return true;

      return user.permissions.includes(per) || per === "/";
    }
  }
};
</script>
