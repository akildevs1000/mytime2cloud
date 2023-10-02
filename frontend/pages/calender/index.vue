<template>
  <div v-if="can('calendar_access') && can('calendar_view')">
    <v-row>
      <v-calendar
        ref="calendar"
        v-model="value"
        locale="en"
        color="primary"
        type="week"
        :events="events"
        :event-color="getEventColor"
        :event-ripple="false"
        @change="getEvents"
        @click:event="showEvent"
        :weekdays="weekday"
        :interval-format="intervalFormat"
      >
        <template v-slot:event="{ event }">
          <div class="v-event-draggable">
            <strong>{{ event.name }}</strong
            ><br />
            {{ formatEventTime(event.start) }} -
            {{ formatEventTime(event.end) }}
          </div>
        </template>
      </v-calendar>
      <v-col cols="12">
        <FullCalendar
          ref="fullCalendar"
          :options="calendarOptions"
          style="background: #fff; height: 500px"
        />
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
import FullCalendar from "@fullcalendar/vue";

import interactionPlugin from "@fullcalendar/interaction";
import resourceTimelinePlugin from "@fullcalendar/resource-timeline";

export default {
  // directives: {
  //   mask: VueMask.directive,
  // },
  components: {
    FullCalendar,
  },
  data() {
    return {
      calendarOptions: {
        height: "500px",
        plugins: [interactionPlugin, resourceTimelinePlugin],
        locale: "en",

        initialView: "resourceTimeline",
      },
    };
  },

  created() {},

  activated() {},

  watch: {},

  methods: {
    can(per) {
      let u = this.$auth.user;
      if (!u.permissions) return false;
      return (
        (u && u.permissions.some((e) => e == per || per == "/")) || u.is_master
      );
    },
    formatEventTime(date) {
      return new Date(date).toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    },
  },
};
</script>

<style scoped>
.columnheader {
  height: 40px !important;
}

.fc-timeline-header {
  height: 30px;
}

.fc .fc-scrollgrid-section table {
  height: 42px !important;
}

.fc .fc-scrollgrid-section table td {
  height: 28px !important;
}
</style>
<style>
.fc-timeline-lane-frame {
  height: 30px !important;
  overflow: hidden;
}

.fc-timeline-event {
  border-radius: 5px;
}

.fc-timeline-event-harness {
  margin-left: 14px;
  margin-right: -15px;
}

.Hall {
  margin-left: -9px !important;
  margin-right: 21px !important;
}

.fc-datagrid-cell-frame {
  height: 30px !important;
  overflow: hidden;
}

/* .fc .fc-scrollgrid-section table td {
      height: 28px !important;
    } */
</style>
