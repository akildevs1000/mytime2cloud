<template>
  <div v-if="true">
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        ss
      </v-snackbar>
    </div>
    <v-row class="mt-3 mb-4">
      <v-col cols="10">
        <h3>Employee</h3>
        <div>Dashboard / Employee / Details</div>
      </v-col>
      <v-col cols="2">
        <div class="text-right">
          <v-btn small to="/employees" color="primary">
            <v-icon small>mdi-arrow-left</v-icon>&nbsp;Back
          </v-btn>
        </div>
      </v-col>
    </v-row>

    <!-- <v-row>
      <v-col md="2">
        <v-card
          tile
          class="elevation-0"
          style="
            border-top: 1px solid #898785;
            border-left: 1px solid #898785;
            border-right: 1px solid #898785;
            border-bottom: 1px solid #898785;
            "
        >
          <v-list flat>
            <v-list-item-group v-model="selectedItem" color="primary">
              <v-list-item
                v-for="(item, i) in items"
                :key="i"
                style="
              border-bottom: 1px solid #898785;
            "
              >
                <v-list-item-icon>
                  <v-icon v-text="item.icon"></v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title v-text="item.text"></v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>
        </v-card>
      </v-col>
      <v-col md="10">
        <v-card tile class="elevation-0"> </v-card>
      </v-col>
    </v-row> -->

    <v-row>
      <v-col md="2">
        <v-card
          class="msx-auto elevation-0 "
          width="256"
          style="border: 1px solid #A09FA0;"
        >
          <v-list dense>
            <v-list-item-group v-model="selectedItem" color="primary">
              <v-list-item v-for="(item, i) in items" :key="i" class="py-2 ">
                <v-list-item-icon>
                  <v-icon v-text="item.icon"></v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title v-text="item.text"></v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>
        </v-card>
      </v-col>
      <v-col md="10">
        <v-card tile class="elevation-0">ssd </v-card>
      </v-col>
    </v-row>
  </div>
  <NoAccess v-else />
</template>
<script>
export default {
  data: () => ({
    selectedItem: 1,
    items: [
      {
        text: "Personal info",
        icon: "mdi-account-circle ",
        permission: "employee_personal_access"
      },
      {
        text: "Contact info",
        icon: "mdi-account-box ",
        permission: "employee_contact_access"
      },
      {
        text: "Passport info",
        icon: "mdi-file-powerpoint-outline ",
        permission: "employee_passport_access"
      },
      {
        text: "Emirates info",
        icon: "mdi-city-variant ",
        permission: "employee_emirate_access"
      },
      {
        text: "Visa info",
        icon: "mdi-file-document-multiple ",
        permission: "employee_visa_access"
      },
      {
        text: "Bank info",
        icon: "mdi-bank",
        permission: "employee_bank_access"
      },
      {
        text: "Documents",
        icon: "mdi-file",
        permission: "employee_document_access"
      },
      {
        text: "Qualification",
        icon: "mdi-file",
        permission: "employee_qualification_access"
      },
      {
        text: "Setting",
        icon: "mdi-wrench",
        permission: "employee_setting_access"
      },
      {
        text: "Assign Reporter",
        icon: "mdi-account",
        permission: "employee_setting_access"
      }
    ]
  })
};
</script>
