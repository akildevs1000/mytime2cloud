<template>
  <v-card flat>
    <v-alert dense flat class="grey lighten-3 body-1 mb-2"
      >Attendanc Rating System</v-alert
    >
    <v-card-text>
      <table>
        <thead>
          <tr>
            <td>Attendance Percentage</td>
            <td>Rating</td>
            <td>Description</td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in ratings" :key="index">
            <td style="width: 20%">{{ item.percentage }}</td>
            <td>{{ item.rating }}</td>
            <td>{{ item.description }}</td>
          </tr>
        </tbody>
      </table>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  data() {
    return {
      ratings: [
        {
          percentage: "91% to 100%",
          rating: 5,
          description: "Excellent attendance",
        },
        {
          percentage: "81% to 90%",
          rating: 4.5,
          description: "Very good attendance",
        },
        { percentage: "71% to 80%", rating: 4, description: "Good attendance" },
        {
          percentage: "61% to 70%",
          rating: 3.5,
          description: "Satisfactory attendance",
        },
        {
          percentage: "51% to 60%",
          rating: 3,
          description: "Moderate attendance",
        },
        {
          percentage: "41% to 50%",
          rating: 2.5,
          description: "Below average attendance",
        },
        { percentage: "31% to 40%", rating: 2, description: "Poor attendance" },
        {
          percentage: "21% to 30%",
          rating: 1.5,
          description: "Very poor attendance",
        },
        {
          percentage: "11% to 20%",
          rating: 1,
          description: "Extremely low attendance",
        },
        { percentage: "0% to 10%", rating: 0, description: "No attendance" },
      ],
    };
  },
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
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
</style>
