<template>
  <v-tabs color="deep-purple accent-4" right>
    <v-tab>Available Rooms</v-tab>
    <v-tab>Occupied Rooms</v-tab>
    <v-tab>Expiring Soon</v-tab>
    <v-tab-item>
      <CommunityFilteredRooms :key="generateRandomId()" report_type="Available" />
    </v-tab-item>
    <v-tab-item> <CommunityFilteredRooms :key="generateRandomId()" report_type="Occupied" /> </v-tab-item>
    <v-tab-item> <CommunityFilteredRooms :key="generateRandomId()" report_type="Expire" /> </v-tab-item>
  </v-tabs>
</template>
<script>
export default {
  methods: {
    generateRandomId() {
      const length = 8; // Adjust the length of the ID as needed
      const randomNumber = Math.floor(Math.random() * Math.pow(10, length)); // Generate a random number
      return randomNumber.toString().padStart(length, "0"); // Convert to string and pad with leading zeros if necessary
    },
  },
};
</script>
