<template>
  <div style="text-align: left">
    <v-text-field
      v-model="cardnumber"
      dense
      outlined
      small
      primary
      style="width: 200px"
    ></v-text-field>
    <v-btn small dense @click="generateQrCode">Generate QR Code</v-btn>
    <div v-if="qrCodeResult">QR Code Result: {{ qrCodeResult }}</div>

    <v-avatar class="ma-1" v-if="qrCodeDataURL" size="150" tile>
      <img :src="qrCodeDataURL" alt="Avatar" />
    </v-avatar>
  </div>
</template>

<script>
import { getQrCode } from "@/utils/cardqrercode.js"; // Adjust the path as needed

export default {
  data() {
    return {
      qrCodeResult: null,
      qrCodeDataURL: null,
      cardnumber: 4000,
    };
  },
  methods: {
    async generateQrCode() {
      try {
        const date = new Date("2024-12-01 00:00:00"); // Set your specific date and time
        const cardNum = this.cardnumber; //"4000"; // Replace with your card number
        const cardType = 1; // Use 1 for numeric card numbers, 2 for IDs

        this.qrCodeResult = (
          await getQrCode(date, cardNum, cardType)
        ).toUpperCase();

        try {
          this.qrCodeDataURL = await this.$qrcode.generate(this.qrCodeResult);
        } catch (error) {
          console.error("Error generating QR code:", error);
        }
        console.log("Generated QR Code:", this.qrCodeResult);
      } catch (error) {
        console.error("Error generating QR Code:", error);
      }
    },
  },
};
</script>
