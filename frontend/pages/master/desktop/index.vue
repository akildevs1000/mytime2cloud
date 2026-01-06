<template>
  <DesktopCompanyMain v-if="baseUrl" :baseUrl="baseUrl" />
</template>

<script>
export default {
  layout({ $auth }) {
    let { user_type } = $auth.user;
    if (user_type == "master") {
      return "master";
    } else if (user_type == "employee") {
      return "employee";
    } else if (user_type == "master") {
      return "default";
    }
  },
  data: () => ({
    // baseUrl: "https://debackend.myhotel2cloud.com/api",
    baseUrl: "http://localhost:8005/api",
  }),
};
</script>
