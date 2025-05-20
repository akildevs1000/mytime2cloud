<template>
  <span>
    <v-card>
      <v-card-title>Leave Quotta<v-spacer></v-spacer> </v-card-title>
      <v-card-text>
        <v-row v-if="leaveStats.length">
          <v-col cols="12">
            <v-simple-table dense class="w-100">
              <thead>
                <tr>
                  <th class="text-center" style="font-size: 12px">
                    Leave Group
                  </th>
                  <th class="text-center" style="font-size: 12px">Total</th>
                  <th class="text-center" style="font-size: 12px">Used</th>
                  <th class="text-center" style="font-size: 12px">Available</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(d, index) in leaveStats" :key="index">
                  <td class="text-center" style="font-size: 12px">
                    {{ d?.leave_type?.short_name }}
                  </td>
                  <td class="text-center" style="font-size: 12px">
                    {{ d.leave_type_count }}
                  </td>
                  <td class="text-center" style="font-size: 12px">
                    {{ d.employee_used }}
                  </td>
                  <td class="text-center" style="font-size: 12px">
                    {{ d.leave_type_count - d.employee_used }}
                  </td>
                </tr>
              </tbody>
            </v-simple-table>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </span>
</template>
<script>
export default {
  props: {
    employeeId: {
      type: [Number, String],
      required: true,
    },
    employeeObject: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    leaveDialogKey: 1,
    dialog: false,
    leaveStats: [],
  }),
  async mounted() {
    if (this.employeeObject?.leave_group_id) {
      await this.fetchLeaveStats(this.employeeObject.leave_group_id);
    }
  },
  methods: {
    async fetchLeaveStats(leaveGroupId) {
      try {
        const response = await this.$axios.get(`leave_groups/${leaveGroupId}`, {
          params: {
            per_page: 1000,
            company_id: this.$auth.user.company_id,
            employee_id: this.employeeObject.id,
          },
        });

        if (Array.isArray(response.data) && response.data[0]?.leave_count) {
          this.leaveStats = response.data[0].leave_count;
        } else {
          this.leaveStats = [];
          console.warn("Unexpected response format:", response.data);
        }
      } catch (error) {
        console.error("Error fetching leave stats:", error);
      }
    },
  },
};
</script>
