<template>
  <v-tabs color="deep-purple accent-4" right>
    <v-tab>Designation</v-tab>
    <v-tab>SubDepartment</v-tab>
    <v-tab>Department</v-tab>

    <v-tab-item>
      <SimpleScreen
        Model="Designation"
        permission_name="designation"
        endpoint="designation"
        :headers="[
          {
            text: 'Name',
            align: 'left',
            value: 'name',
          },
          { text: 'Options', align: 'left', sortable: false, value: 'options' },
        ]"
      />
    </v-tab-item>
    <v-tab-item>
      <SimpleScreen
        Model="Sub Department"
        permission_name="sub_department"
        endpoint="sub-departments"
        :headers="[
          {
            text: 'Name',
            align: 'left',
            value: 'name',
          },
          { text: 'Options', align: 'left', sortable: false, value: 'options' },
        ]"
      />
    </v-tab-item>
    <v-tab-item>
      <SimpleScreen
        Model="Department"
        permission_name="department"
        endpoint="departments"
        :headers="[
          {
            text: 'Name',
            align: 'left',
            value: 'name',
          },
          { text: 'Options', align: 'left', sortable: false, value: 'options' },
        ]"
      />
    </v-tab-item>
  </v-tabs>
</template>
