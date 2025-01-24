<template>
  <div>
    <v-dialog v-model="dialog" max-width="600">
      <WidgetsClose left="590" @click="dialog = false" />
      <template v-slot:activator="{ on, attrs }">
        <v-icon v-bind="attrs" v-on="on" color="blue">mdi-clock-outline</v-icon>
      </template>
      <v-card elevation="0">
        <v-alert flat dense color="grey lighten-3"
          >Employee Leave Timeline</v-alert
        >
        <v-card-text>
          <v-timeline dense>
            <v-timeline-item
              v-for="(item, index) in items"
              :key="index"
              color=""
            >
              <span slot="icon"
                ><v-icon color="primary">mdi-clock-outline</v-icon></span
              >
              <v-card outlined>
                <v-card-text>
                  <div>
                    {{ item.date }}
                  </div>
                  <small v-html="item.description"></small>

                  <br /><small
                    ><strong>{{
                      formateDateTime(item?.created_at)
                    }}</strong></small
                  >
                </v-card-text>
              </v-card>
            </v-timeline-item>
          </v-timeline>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
  props: ["items"],
  data: () => ({
    dialog: false,
  }),
  methods: {
    formateDateTime(custom_date) {
      let dateTime = new Date(custom_date);

      // Format time as HH:mm:ss in Dubai time zone
      let time = new Intl.DateTimeFormat("en-GB", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        timeZone: "Asia/Dubai",
        hour12: false,
      }).format(dateTime);

      // Format date as DD Mon YYYY in Dubai time zone and remove comma
      let date = new Intl.DateTimeFormat("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        timeZone: "Asia/Dubai",
      })
        .format(dateTime)
        .replace(",", ""); // Removes the comma, if present

      return `${time} @ ${date}`;
    },
  },
};
</script>
