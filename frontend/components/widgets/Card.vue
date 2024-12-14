<template>
  <v-card rounded="lg" outlined class="pa-2">
    <!-- :style="{ borderColor: options.color }"   -->
    <v-row no-gutter>
      <v-col :cols="options.col || '4'" class="pt-5 text-left">
        <v-row>
          <v-col cols="12" class="tex-center">
            <div class="text-center">
              <v-icon size="30" class="" :color="options.color">{{
                options.icon
              }}</v-icon>
              <div :class="`${options.color}--text`">
                <small>{{ options.label }}</small>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-col>
      <v-col class="text-center mt-3">
        <v-avatar :color="options.color">
          <h3 class="white--text" :color="options.color">
            {{ options.value }}
          </h3>
        </v-avatar>
      </v-col>
    </v-row>
  </v-card>
</template>
<script>
export default {
  props: ["options"],
};
</script>
