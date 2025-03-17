<template>
  <span>
    <style scoped>
      .mx-input {
        border: 1px solid #9e9e9e !important;
        color: black !important;
      }
      .mx-datepicker {
        width: 200px;
      }

      .mx-table-date td,
      .mx-table-date th {
        text-align: center !important;
      }
    </style>

    <date-picker
      style="width: 100%"
      value-type="format"
      format="YYYY-MM-DD"
      type="date"
      v-model="time3"
      @change="CustomFilter()"
      range
      :disabled="disabled ? disabled : false"
    ></date-picker>
  </span>
</template>

<script>
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
export default {
  components: {
    DatePicker,
  },
  props: ["disabled", "height", "width"],
  data() {
    return {
      from_date: null,
      to_date: null,

      from_menu: false,
      to_menu: false,

      time3: [
        new Date().toISOString().slice(0, 10),
        new Date().toISOString().slice(0, 10),
      ],
      loading: false,
      showTimePanel: false,
    };
  },
  methods: {
    CustomFilter() {
      this.from_date = this.time3[0];
      this.to_date = this.time3[1];
      if (this.from_date && this.to_date) {
        this.$emit("filter-attr", {
          from: this.from_date,
          to: this.to_date,
        });
      }
    },
  },
};
</script>
