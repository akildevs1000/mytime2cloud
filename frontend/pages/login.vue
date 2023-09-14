<template>
  <v-app>

    <v-dialog persistent v-model="dialogWhatsapp" width="600px">

      <v-card>
        <v-card-title dense class="    white--text  " style="background-color:#6946dd;">
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


                <v-form ref="form" method="post" v-model="whatsappFormValid" lazy-validation>
                  <label for="">OTP</label>
                  <div class="form-outline mb-4">
                    <v-otp-input v-model="otp" length="6" :rules="requiredRules"></v-otp-input>
                  </div>

                  <div class="text-center pt-1 mb-5 pb-1">
                    <span v-if="msg" class="error--text">
                      {{ msg }}
                    </span>
                    <v-btn :loading="loading" @click="checkOTP(otp)" class="btn   btn-block   fa-lg   mt-1 mb-3 btntext">
                      Verify OTP
                    </v-btn>
                    <!-- <v-btn :loading="loading" @click="checkOTP(otp)"
                      class="btn btn-primary btn-block text-white fa-lg primary mt-1 mb-3 btntext">
                      Verify OTP
                    </v-btn> -->
                  </div>

                  <div class="d-flex align-items-center justify-content-center pb-4"></div>
                </v-form>

              </div>
            </div>

          </div>

        </v-card-text>


      </v-card>
    </v-dialog>
    <section class="h-100 gradient-form" style="background-color: #eee">
      <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-xl-8">
            <div class="card1 rounded-3 text-black">

              <div class="row g-0">
                <div class="col-lg-6 col-md-12  col-sm-12 col-lg-6" style="background-color: #6946dd;">


                  <div class="text-center" style="height:100px">
                    <!-- <img width="35%" :src="logo" alt="logo" /> -->
                  </div>
                  <div class="card-body p-md-5 mx-md-4 " style="color:#FFF">
                    <h2 class="pb-7">Welcome To EzTime</h2>

                    <v-form ref="form" method="post" v-model="valid" lazy-validation autocomplete="off">

                      <div class="form-outline ">
                        <v-text-field dark color="white--text" rounded v-model="email" :rules="emailRules"
                          :hide-details="false" id="form2Example11" placeholder="username" autofill="false" required dense
                          outlined type="email" prepend-inner-icon="mdi-account" autocomplete="false"
                          aria-autocomplete="none"></v-text-field>
                      </div>



                      <div class="form-outline  ">


                        <v-text-field dark color="white--text" rounded dense outlined :rules="passwordRules"
                          autocomplete="off" placeholder="Password" prepend-inner-icon="mdi-lock  "
                          :append-icon="show_password ? 'mdi-eye' : 'mdi-eye-off'" :type="show_password ? 'text'
                            : 'password'
                            " v-model="password" class="input-group--focused text-white"
                          @click:append="show_password = !show_password"></v-text-field>
                      </div>

                      <v-row>
                        <v-col md="6">

                          <v-checkbox color="white--text" value="red" dark>

                            <template v-slot:label>
                              <label style="color:#FFF">Remember Password</label>
                            </template>

                          </v-checkbox>
                        </v-col>
                        <v-col md="6" class="text-right pt-8">

                          <nuxt-link class="text-white " to="/reset-password">Forgot password?</nuxt-link>
                        </v-col>
                      </v-row>


                      <div class="text-center pt-1 mb-5 pb-1">
                        <span v-if="msg" class="error--text">
                          {{ msg }}
                        </span>
                        <v-btn :loading="loading" @click="loginWithOTP()"
                          class="btn btn-black btn-block    white mt-1 mb-3 p-4 btntext">
                          Login
                        </v-btn>
                      </div>


                    </v-form>
                    <div class="text-center white--text text-white">
                      Don't Have an Account?. Contact
                      Admin
                    </div>
                  </div>
                </div>
                <div class="col-md-12  col-sm-12 col-lg-6 d-flex align-items-center  bgimage ">

                </div>
              </div>

            </div>

            <!-- <div class="card rounded-3 text-black">
              <div class="row g-0">
                <div class="col-lg-6">
                  <div class="card-body p-md-5 mx-md-4">
                    <div class="text-center">
                      <img width="35%" :src="logo" alt="logo" />
                    </div>

                    <v-form ref="form" method="post" v-model="valid" lazy-validation>
                      <label for="">Email</label>
                      <div class="form-outline mb-4">
                        <v-text-field v-model="email" :rules="emailRules" :hide-details="false" id="form2Example11"
                          placeholder="master@erp.com" required dense outlined type="email"></v-text-field>
                      </div>

                      <label for="">Password</label>

                      <div class="form-outline mb-4">


                        <v-text-field dense outlined :rules="passwordRules" :append-icon="show_password ? 'mdi-eye' : 'mdi-eye-off'
                          " :type="show_password ? 'text' : 'password'" v-model="password" class="input-group--focused"
                          @click:append="show_password = !show_password"></v-text-field>
                      </div>


                      <div class="text-center pt-1 mb-5 pb-1">
                        <span v-if="msg" class="error--text">
                          {{ msg }}
                        </span>
                        <v-btn :loading="loading" @click="login"
                          class="btn btn-primary btn-block text-white fa-lg primary mt-1 mb-3">
                          Log in
                        </v-btn>
                      </div>

                      <div class="d-flex align-items-center justify-content-center pb-4">

                      </div>
                    </v-form>
                    <div class="text-right">
                      <nuxt-link class="text-muted text-right" to="/reset-password">Forgot password?</nuxt-link>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 d-flex align-items-center primary">
                  <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                    <h6>IDEA-HRMS THE RIGHT SOLUTION FOR YOU</h6>
                    <p class="small mb-0">
                      Make it simple, easy and accessible anywhere, anytime.
                      Save time, stay compliant and reduce labor costs by
                      streamlining how you collect hours worked and time-off
                      accruals.
                    </p>
                  </div>
                </div>
              </div>

            </div> -->
          </div>
        </div>
      </div>
    </section>
  </v-app>
</template>

<script>
export default {
  // components: { VueRecaptcha },

  layout: "login",
  data: () => ({
    // sitekey: "6Lf1wYwhAAAAAOMJYvI73SgjCSrS_OSS2kDJbVvs", // i am not robot
    // reCaptcha: null,
    // showGRC: false,
    whatsappFormValid: '',
    logo: "/ideaHRMS-final-blue.svg",
    valid: true,
    loading: false,
    snackbar: false,
    email: "demo@gmail.com",
    password: "",
    show_password: false,
    msg: "",
    requiredRules: [
      v => !!v || 'Required',

    ],
    emailRules: [
      (v) => !!v || "E-mail is required",
      (v) => /.+@.+\..+/.test(v) || "E-mail must be valid",
    ],

    passwordRules: [(v) => !!v || "Password is required"],


    dialogWhatsapp: false,
    otp: '',
    userId: '',
  }),
  created() { },
  methods: {
    handleInputChange() {

    },
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
        let credentials = {
          email: this.email,
          password: this.password,
        };

        let payload = credentials;

        //geenrate OTP 
        this.$axios
          .post(`loginwith_otp`, payload)
          .then(({ data }) => {
            if (!data.status) {
              alert('OTP Verification: ' + data.message);

            } else if (data.user_id) {

              if (data.enable_whatsapp_otp == 1) {
                this.dialogWhatsapp = true;
                this.userId = data.user_id;

              }
              else {
                this.login();
              }


            }
            else {
              alert('OTP Verification: ' + "Invalid Deails");
            }
          })
          .catch((err) => console.log(err));
      }

    },
    login() {


      // this.showGRC = this.reCaptcha ? false : true;

      // if (this.$refs.form.validate() && this.reCaptcha) {
      if (this.$refs.form.validate()) {





        this.msg = "";
        this.loading = true;
        // const token = await this.$recaptcha.getResponse();
        let credentials = {
          email: this.email,
          password: this.password,
        };


        this.$auth
          .loginWith("local", { data: credentials })
          .then(({ data }) => {




            // if (data.user && data.user.user_type == "employee") {
            //   this.$router.push(`/employee_dashboard`);
            //   id = data.user?.employee?.id;
            //   name = data.user?.employee?.first_name;
            // } else if (data.user && data.user.user_type == "company") {
            //   id = data.user?.company?.id;
            //   name = data.user?.company?.name;
            // } else if (data.user && data.user.user_type == "master") {
            //   this.$router.push(`/master`);
            //   id = data.user?.id;
            //   name = data.user?.name;
            // }

            if (data.user && data.user.user_type == "master") {
              this.$router.push(`/master`);
              id = data.user?.id;
              name = data.user?.name;
            }

            // this.$axios.post(`activity`, {
            //   user_id: id,
            //   action: "Logged In",
            //   type: "Login",
            //   model_id: id,
            //   model_type: "User",
            //   description: `${name} logged In`
            // });
          })
          .catch(({ response }) => {
            if (!response) {
              return false;
            }
            let { status, data, statusText } = response;
            this.msg = status == 422 ? data.message : statusText;
            setTimeout(() => (this.loading = false), 2000);
          });
      }
    },
  },
};
</script>
<style  scoped>
.v-text-field--outlined>>>fieldset {
  border-color: #FFF;
}

.v-list__group__header__prepend-icon .v-icon {
  color: red;
}

.v-input__icon {
  color: #FFF !important;
}

.hidden-sm-and-down .v-icon {
  color: white !important;
}

.v-text-field--rounded {
  border-radius: 10px;
}

.text-white {
  color: #FFF;
}

.v-label {
  color: #FFF !important;
}

.bgimage {
  /* background-image: url(../static/login2.jpg) no-repeat center center fixed;
  ; */

  background-image: url("../static/login2.jpg");
  background-size: cover;

  min-height: 600px;
}

.v-btn {
  text-transform: inherit !important;
}

.v-input__control .v-label {
  color: red;
}

.btntext {
  color: #6946dd;
  font-weight: bold;
  font-size: 22px;
}

/* fieldset {
  border-radius: 10px;
  ;
} */
/* 
i {
  color: #FFF;
} */

@media (min-width: 768px) {
  .gradient-form {
    height: 100vh !important;
  }
}

@media (min-width: 769px) {
  .primary {
    background: #5fafa3 !important;
    /* #5fafa3 */
    border-top-right-radius: 0.3rem;
    border-bottom-right-radius: 0.3rem;
  }
}
</style>
