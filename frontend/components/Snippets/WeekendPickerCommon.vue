<template>
  <div>
    <span>{{ label }} </span>
    <v-select
      class="mt-1"
      @change="setWeekend"
      v-model="weekend"
      :items="items"
      item-value="name"
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
      weekend: `Not Applicable`,
      items: [
        { id: 1, name: `Not Applicable` },
        { id: 2, name: `Flexi` },
        { id: 3, name: `Half Day` },
        { id: 4, name: `Monday` },
        { id: 5, name: `Tueday` },
        { id: 6, name: `Wednesday` },
        { id: 7, name: `Thursday` },
        { id: 8, name: `Friday` },
        { id: 9, name: `Saturday` },
        { id: 10, name: `Sunday` },
      ],
    };
  },
  watch: {
    weekend(value) {
      this.weekend = value;
    },
  },
  methods: {
    setWeekend() {
      this.$emit("selectedWeekend", this.weekend);
    },
  },
};
</script>
