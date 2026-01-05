<template>
  <DesktopCompanyMain v-if="baseUrl" :baseUrl="baseUrl" />
</template>

<script>
export default {
  data: () => ({
    baseUrl: "https://debackend.myhotel2cloud.com/api",
    // baseUrl: "http://localhost:8005/api",
  }),
};
</script>
