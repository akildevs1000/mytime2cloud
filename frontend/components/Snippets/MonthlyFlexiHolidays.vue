<template>
  <div>
    <span>{{ label }}</span>
    <v-select
      class="mt-1"
      @change="setMonthlyHolidays"
      v-model="weekend"
      :items="items"
      item-value="id"
      item-text="name"
      dense
      outlined
      :hide-details="true"
    ></v-select>
  </div>
</template>
<script>
export default {
  props: ["label"],
  auth: false,
  layout: "login",
  data() {
    return {
      weekend: 1,
      items: [
        { id: 1, name: `Not Applicable` },
        { id: 2, name: `1 Day` },
        { id: 3, name: `2 Day` },
        { id: 4, name: `3 day` },
        { id: 5, name: `4 day` },
        { id: 6, name: `5 day` },
        { id: 7, name: `6 day` },
        { id: 8, name: `7 day` },
        { id: 9, name: `8 day` },
        { id: 10, name: `9 day` },
      ],
    };
  },
  methods: {
    setMonthlyHolidays() {
      this.$emit("selectedMonthlyHolidays", this.weekend);
    },
  },
};
</script>
