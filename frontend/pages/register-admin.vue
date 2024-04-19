<template>
  <div class="mobileBGColor111 bg-body">
    <v-dialog persistent v-model="dialogWhatsapp" width="600px">
      <v-card>
        <v-card-title
          dense
          class="white--text"
          style="background-color: #6946dd; color: #fff !important"
        >
          Whatsapp Verification
          <v-spacer></v-spacer>
          <v-icon @click="dialogWhatsapp = false" outlined dark color="white">
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text>
          <div class="row g-0">
            <div class="col-lg-12">
              <div class="card-body p-md-5 mx-md-4">
                <v-row class="pb-5">
                  <v-col md="12" cols="12" class="text-center">
                    <h2>MyTime2Cloud</h2>
                  </v-col>
                </v-row>

                <h5>
                  Two Step Whatsapp OTP Verification
                  <v-icon dark color="green" fill>mdi-whatsapp</v-icon>
                </h5>
                <p>
                  We sent a verification code to your mobile number. Enter the
                  Code from the mobile in the filed below
                </p>
                <h2 style="font-size: 30px">
                  {{ maskMobileNumber }}
                </h2>
                <br />
                <!-- <v-form ref="form" method="post" v-model="whatsappFormValid" lazy-validation> -->
                <label
                  for=""
                  class="pb-5"
                  style="font-weight: bold; font-size: 20px"
                  >Type your 6 Digit Security Code</label
                >
                <div class="form-outline mb-4">
                  <v-otp-input
                    v-model="otp"
                    length="6"
                    :rules="requiredRules"
                  ></v-otp-input>
                </div>

                <div class="text-center pt-1 mb-5 pb-1">
                  <span v-if="msg" class="error--text">
                    {{ msg }}
                  </span>
                  <v-btn
                    :loading="loading"
                    @click="checkOTP(otp)"
                    class="btn btn-block fa-lg mt-1 mb-3"
                    style="background-color: #6946dd; color: #fff"
                  >
                    Verify OTP
                  </v-btn>
                  <!-- <v-btn :loading="loading" @click="checkOTP(otp)"
                      class="btn btn-primary btn-block text-white fa-lg primary mt-1 mb-3 btntext">
                      Verify OTP
                    </v-btn> -->
                </div>

                <div
                  class="d-flex align-items-center justify-content-center pb-4"
                ></div>
                <!-- </v-form> -->
              </div>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar" top="top" color="error" elevation="24">
      {{ snackbarMessage }}
    </v-snackbar>

    <v-row class="" style="height: 100%">
      <v-col xs="12" sm="12" md="12" lg="5" style="padding: 0px">
        <div
          class="card-body p-md-5 mx-md111111-4"
          style="
            padding: 3rem !important;
            max-width: 500px;
            margin: auto;
            text-align: center;
          "
        >
          <div style="min-height: 100px">
            <div style="width: 100%" class="text-center">
              <v-img
                class="text-center"
                style="
                  width: 200px;
                  padding: 0px;
                  margin: auto;
                  text-align: center;
                "
                src="/logo22.png"
              ></v-img>
            </div>
            <h3 class="pb-7 pt-15">
              Welcome To
              <span style="font-size: 20px"> Mytime2Cloud </span>
            </h3>
          </div>
          <div>
            <v-form
              ref="form"
              method="post"
              v-model="valid"
              lazy-validation
              autocomplete="off"
            >
              <div class="form-outline">
                <v-text-field
                  role="presentation"
                  label="Email"
                  v-model="credentials.email"
                  :hide-details="false"
                  id="form2Example11"
                  autofill="false"
                  required
                  dense
                  outlined
                  type="email"
                  prepend-inner-icon="mdi-account"
                  append-icon="mdi-at"
                  autocomplete="off"
                  aria-autocomplete="none"
                ></v-text-field>
              </div>

              <div class="form-outline">
                <v-text-field
                  role="presentation"
                  label="Password"
                  dense
                  outlined
                  autocomplete="off"
                  prepend-inner-icon="mdi-lock  "
                  :append-icon="show_password ? 'mdi-eye' : 'mdi-eye-off'"
                  :type="show_password ? 'text' : 'password'"
                  v-model="credentials.password"
                  class="input-group--focused"
                  @click:append="show_password = !show_password"
                ></v-text-field>
              </div>

              <div class="text-center pt-1 mb-5 pb-1">
                <span v-if="msg" class="error--text111" style="color: #ff9f87">
                  {{ msg }}
                </span>
                <v-btn
                  :loading="loading"
                  @click="login()"
                  class="btn primary btn-black btn-block mt-1 mb-3 p-4 btntext"
                  style="width: 100%; height: 48px"
                >
                  Register
                </v-btn>
              </div>
            </v-form>
          </div>
          <div class="text-center">
            Already have an account?
            <v-btn to="/login" text small dense color="primary"
              >Click to login</v-btn
            >
          </div>

          <v-row class="text-center" style="font-size: 13px">
            <v-col class="pa-5">
              For Technical Support :
              <a
                target="_blank"
                href="https://wa.me/971529048025?text=Hello MyTime2Cloud. I need your support."
                ><v-icon color="black">mdi-whatsapp</v-icon></a
              >
              <a
                style="text-decoration: none; color: black"
                href="tel:+971529048025"
                >+971 52 904 8025</a
              ></v-col
            >
          </v-row>
          <v-row class="text-center" style="font-size: 13px">
            <v-col class="pa-5">
              <a
                style="text-decoration: none; color: black"
                href="mailto:support@mytime2cloud.com"
                >support@mytime2cloud.com</a
              ></v-col
            >
          </v-row>
        </div>
      </v-col>
      <v-col
        xs="12"
        sm="12"
        md="12"
        lg="7"
        style=""
        class="hide-on-mobile d-none d-lg-flex"
      >
        <div class="about-content">
          <h3>About Mytime2Cloud</h3>
          <div style="font-weight: 300">
            MyTime2Cloud is an innovative and comprehensive platform
            meticulously crafted to redefine how organizations approach
            workforce management. By combining time attendance management with
            facial recognition access control, MyTime2Cloud simplifies and
            provides a streamlined experience for both employees and HR
            professionals.<br />
            Customization and Reporting: The platform offers customizable
            settings to fit the specific needs of different organizations. It
            generates comprehensive reports and analytics based on attendance
            data, enabling informed decision-making and efficient resource
            allocation.
          </div>
          <h3 class="pt-10">Features</h3>
          <ul style="font-weight: 300">
            <li>Employees management</li>

            <li>Time Attendance</li>
            <li>Leave management</li>
            <li>Payroll management</li>
            <li>Access Controll</li>
            <li>Visitor management</li>
            <li>Face Recognisation Integration</li>
            <li>Custom Reports</li>
            <li>Custom Dashboards</li>
            <li>Email and Whatsapp Notifications</li>
          </ul>
          <v-row class="text-left pt-10">
            <v-col class="pa-5">
              <h3>Technical Support</h3>

              <a
                style="font-weight: 300"
                target="_blank"
                href="https://wa.me/971529048025?text=Hello MyTime2Cloud. I need your support."
                ><v-icon color="white">mdi-whatsapp</v-icon></a
              >

              <a
                style="color: #fff; text-decoration: none; font-weight: 300"
                href="tel:+971529048025"
                >+971 52 904 8025</a
              >
              <br />
              <a
                style="text-decoration: none; color: #fff; font-weight: 300"
                href="mailto:support@mytime2cloud.com"
                >support@mytime2cloud.com</a
              >
            </v-col>
          </v-row>
        </div>
      </v-col>
    </v-row>
    <v-dialog v-model="dialogDevice" max-width="700px">
      <v-card>
        <v-card-title>
          Add Device(s) <v-spacer></v-spacer>
          <v-icon @click="dialogDevice = false" color="primary"
            >mdi-close</v-icon
          >
        </v-card-title>
        <v-card-text>
          <v-icon class="ml-2" @click="addDeviceItem" color="primary"
            >mdi-plus-circle-outline</v-icon
          >

          <v-container>
            <v-row v-for="(dd, index) in devicesData" :key="index">
              <v-col cols="5">
                <v-text-field
                  dense
                  outlined
                  v-model="dd.name"
                  label="Name"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="5">
                <v-text-field
                  dense
                  outlined
                  v-model="dd.device_id"
                  label="Device Id"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="2" v-if="index > 0">
                <v-icon color="red" @click="deleteDeviceItem(index)"
                  >mdi-delete</v-icon
                >
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="10" v-if="devicesData && devicesData.length > 1">
                <v-text-field
                  dense
                  outlined
                  v-model="token"
                  label="Token"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-btn color="primary" @click="submitDevicesData">Submit</v-btn>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import ForgotPassword from "../components/ForgotPassword.vue";
export default {
  auth: false,
  layout: "login",
  components: { ForgotPassword },
  data: () => ({
    dialogDevice: true,
    token: null,
    devicesData: [
      {
        name: ``,
        device_id: ``,
        branch_id: 0,
        company_id: 0,
        status_id: 1,
        ip: "0.0.0.0",
        port: "0000",
        utc_time_zone: "Asia/Dubai",
        function: "auto",
        device_type: "all",
      },
    ],
    formValues: {},
    dialogForgotPassword: false,
    maskMobileNumber: "",
    whatsappFormValid: true,
    logo: "/ideaHRMS-final-blue.svg",
    valid: true,
    loading: false,
    snackbar: false,
    snackbarMessage: "",

    show_password: false,
    msg: "",
    requiredRules: [(v) => !!v || "Required"],
    emailRules: [
      (v) => !!v || "E-mail is required",
      (v) => /.+@.+\..+/.test(v) || "E-mail must be valid",
    ],

    passwordRules: [(v) => !!v || "Password is required"],

    dialogWhatsapp: false,
    otp: "",
    userId: "",
    credentials: {
      email: "",
      password: "",
      source: "admin",
    },
    company_id: 0,
  }),
  created() {
    // this.$store.commit("dashboard/resetState", null);
    this.$store.dispatch("dashboard/resetState");
    this.$store.dispatch("resetState");

    this.verifyToken();
  },
  mounted() {
    // setTimeout(() => {
    //   window.location.reload();
    // }, 1000 * 60 * 15); //15 minutes

    this.$store.dispatch("dashboard/resetState");
    this.$store.dispatch("resetState");
  },
  methods: {
    deleteDeviceItem(index) {
      this.devicesData.splice(index, 1);
    },
    addDeviceItem() {
      let json = {
        company_id: this.company_id,
        name: ``,
        device_id: ``,
        branch_id: 0,
        status_id: 1,
        ip: "0.0.0.0",
        port: "0000",
        utc_time_zone: "Asia/Dubai",
        function: "auto",
        device_type: "all",
      };

      this.devicesData.push(json);
    },
    openForgotPassword() {
      this.dialogForgotPassword = true;
    },
    verifyToken() {
      if (this.$route.query.email && this.$route.query.password) {
        this.email = this.$route.query.email;
        this.password = this.$route.query.password;

        this.loginWithOTP();
      }
    },
    hideMobileNumber(inputString) {
      // Check if the input is a valid string
      if (typeof inputString !== "string" || inputString.length < 4) {
        return inputString; // Return input as is if it's not a valid string
      }

      // Use a regular expression to match all but the last 3 digits
      var regex = /^(.*)(\d{5})$/;
      var matches = inputString.match(regex);

      if (matches) {
        var prefix = matches[1]; // Text before the last 3 digits
        var lastDigits = matches[2]; // Last 3 digits
        var maskedPrefix = "*".repeat(prefix.length); // Create a string of asterisks of the same length as the prefix
        return maskedPrefix + lastDigits;
      } else {
        return inputString; // Return input as is if there are fewer than 3 digits
      }
    },

    handleInputChange() {},
    // mxVerify(res) {
    //   this.reCaptcha = res;
    //   this.showGRC = this.reCaptcha ? false : true;
    // },
    checkOTP(otp) {
      if (otp == "") {
        alert("Enter OTP");
        return;
      }
      let payload = {
        userId: this.userId,
      };
      this.$axios
        .post(`check_otp/${otp}`, payload)
        .then(({ data }) => {
          if (!data.status) {
            alert("Invalid OTP. Please try again");
          } else {
            this.login();
          }
        })
        .catch((err) => console.log(err));
    },

    loginWithOTP() {
      if (this.$refs.form.validate()) {
        this.loading = true;
        this.$store.commit("email", this.credentials.email);
        this.$store.commit("password", this.credentials.password);

        this.$axios
          .post("loginwith_otp", this.credentials)
          .then(({ data }) => {
            if (!data.status) {
              //alert("OTP Verification: " + data.message);
              alert("Invalid Login Deails");
            } else if (data.user_id) {
              if (data.enable_whatsapp_otp == 1) {
                this.dialogWhatsapp = true;
                this.userId = data.user_id;
                if (data.mobile_number) {
                  this.maskMobileNumber = this.hideMobileNumber(
                    data.mobile_number
                  );
                }

                this.loading = false;
              } else {
                this.login();
              }
            } else {
              this.snackbar = true;
              this.snackbarMessage = "Invalid Login Details";
              //alert("Invalid Login Deails");
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.snackbar = true;
        this.snackbarMessage = "Invalid Emaild and Password";
      }
      this.loading = false;
    },
    login() {
      if (this.$refs.form.validate()) {
        console.log(this.credentials);

        this.msg = "";
        this.loading = true;
        this.$axios
          .post("register", this.credentials)
          .then(({ data }) => {
            alert(data.record.id);
            this.company_id = data.record.id;
            let json = {
              name: ``,
              device_id: ``,
              branch_id: 0,
              company_id: data.record.id,
              status_id: 1,
              ip: "0.0.0.0",
              port: "0000",
              utc_time_zone: "Asia/Dubai",
              function: "auto",
              device_type: "all",
            };

            this.devicesData.push(json);

            this.dialogDevice = true;

            setTimeout(() => (this.loading = false), 2000);

            // this.$router.push("/login");
          })
          .catch(({ response }) => {
            if (!response) {
              return false;
            }
            let { status, data, statusText } = response;
            this.msg = status == 422 ? data.message : statusText;
            setTimeout(() => (this.loading = false), 2000);
          });
      } else {
        this.snackbar = true;
        this.snackbarMessage = "Invalid Emaild and Password";
      }
    },
  },
};
</script>

<style scoped>
@import url("../assets/registerStyle.css");
</style>
