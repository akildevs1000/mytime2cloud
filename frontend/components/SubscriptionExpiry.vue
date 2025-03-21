<template>
  <v-container>
    <v-dialog v-model="showExpiryDialog" max-width="500">
      <WidgetsClose left="490" @click="showExpiryDialog = false" />
      <v-card>
        <div :class="expiryColor" class="white--text pa-2">Expiry Alert</div>
        <v-card-text>
         <v-container class="text-center subtitle-1 pa-5">
         <div class="pa-5">
          Your subscription will expire in {{ daysRemaining }} days!
         </div>
         </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      expiryColor: "primary",
      expiryDate: null,
      showExpiryDialog: false,
      daysRemaining: null,
    };
  },
  mounted() {
    // this.calculateDaysRemaining("2025/04/19");

    this.calculateDaysRemaining(
      this.$auth.user.company.expiry.replaceAll("/", "-")
    );

  },
  methods: {
    calculateDaysRemaining(expiryDate) {
      if (!expiryDate) return null;

      const expiry = new Date(expiryDate);
      const today = new Date();
      const diffTime = expiry - today;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Convert milliseconds to days
      this.daysRemaining = diffDays;
      if (diffDays > 0 && diffDays <= 30) {
        this.showExpiryDialog = true;

        if (diffDays <= 30 && diffDays > 20) {
          console.log(`${diffDays} days left - More than 20 days remaining`);
          this.expiryColor = "primary";
        } else if (diffDays <= 20 && diffDays > 10) {
          console.log(
            `${diffDays} days left - Between 10 and 20 days remaining`
          );
          this.expiryColor = "orange";
        } else {
          console.log(`${diffDays} days left - Less than 10 days remaining`);
          this.expiryColor = "red";
        }
      }
    },
  },
};
</script>
