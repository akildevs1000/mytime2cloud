<template>
  <span>
    <style>
      .progress-container {
        width: 100%;
        background-color: #d8d8d8;
        display: flex;
        justify-content: center;
        text-align: center;
        overflow: hidden; /* To handle overflow when adding more style */
      }

      .progress-green {
        background-color: #8bc34a;
        color: white;
        transition: width 0.5s ease-in-out; /* Add smooth transition */
      }

      .progress-gray {
        background-color: #d3d3d3;
        color: black;
        transition: width 0.5s ease-in-out; /* Add smooth transition */
      }
    </style>

    <div v-if="engaged > 0"
      class="progress-container"
      aria-label="Progress bar showing engaged vs remaining"
    >
      <div  :class="`${total == 0 && engaged == 0 ? 'progress-gray' : 'progress-green'}`" :style="{ width: `${engagedPercentage}%` }">
        {{ engaged }}
      </div>
      <div class="progress-gray" :style="{ width: `${remainingPercentage}%` }">
        {{ total - engaged }}
      </div>
    </div>
  </span>
</template>

<script>
export default {
  props: {
    engaged: {
      type: Number,
      required: true,
    },
    total: {
      type: Number,
      required: true,
    },
  },
  computed: {
    engagedPercentage() {
      return this.total ? ((this.engaged / this.total) * 100).toFixed(2) : 100;
    },
    remainingPercentage() {
      return this.total
        ? (((this.total - this.engaged) / this.total) * 100).toFixed(2)
        : 0;
    },
  },
};
</script>
