<template>
  <v-dialog v-model="dialog" max-width="360">
    <template v-slot:activator="{ on, attrs }">
      <img
        v-bind="attrs"
        v-on="on"
        class="iconsize30"
        style="cursor: pointer"
        title="Click to Open Door"
        src="/icons/door_open.png"
      />
    </template>

    <style scoped>
      /* Container for the OTP inputs to use Flexbox for even spacing */
      .otp-container {
        display: flex;
        justify-content: center;
        gap: 15px; /* Space between the circles */
      }

      /* 🚀 The CSS for the Circular Input 🚀 */
      .circle-input {
        color: #626262;
        /* Set a fixed size for a perfect circle */
        width: 40px;
        height: 40px;

        /* Key change: Makes the input circular */
        border-radius: 50%;

        /* Styling */
        border: 2px solid var(--v-primary-base, #b9b9b9); /* Use Vuetify primary color */
        text-align: center;
        font-size: 1.25rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        transition: all 0.2s ease-in-out;
      }

      /* Style when the input is focused */
      .circle-input:focus {
        outline: none; /* Remove default focus outline */
        border-color: var(
          --v-accent-base,
          #b9b9b9
        ); /* Highlight with an accent color */
        box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.2); /* Outer glow effect */
        background-color: #f7f7f7;
      }

      /* Optional: Make the text cursor a block to look more like an OTP field */
      .circle-input {
        caret-color: var(--v-primary-base, #1976d2);
      }

      /* For better mobile/desktop usability, hide the default spinners */
      .circle-input::-webkit-outer-spin-button,
      .circle-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }
      .circle-input[type="number"] {
        -moz-appearance: textfield;
      }

      @keyframes shake {
        0%,
        100% {
          transform: translateX(0);
        }
        20% {
          transform: translateX(-8px);
        }
        40% {
          transform: translateX(8px);
        }
        60% {
          transform: translateX(-6px);
        }
        80% {
          transform: translateX(6px);
        }
      }

      .animate-shake {
        animation: shake 0.4s ease;
      }
    </style>

    <WidgetsClose left="350" @click="dialog = false" />
    <v-card flat v-if="item && item.id">
      <v-alert dense flat dark class="primary">Device # {{ item.device_id }}</v-alert>
      <v-container class="py-5 px-8">
        <v-form ref="form" lazy-validation>
          <div class="pa-5">
            <div class="otp-container" :class="errorClass">
              <div v-if="showDoorGif">
                <img src="@/static/door-open.gif" style="width: 150px" />
              </div>
              <input
                v-else
                v-for="index in 4"
                :key="index"
                :id="`otp-input-${index}`"
                type="text"
                maxlength="1"
                inputmode="numeric"
                pattern="[0-9]"
                class="circle-input"
                @input="handleInput($event, index)"
                @keydown="handleKeyDown($event, index)"
                v-model="otpDigits[index - 1]"
                ref="otpInputs"
              />
            </div>
          </div>
        </v-form>
        <v-btn class="mt-5" small block color="primary" @click="checkOtp">
          Submit
        </v-btn>
      </v-container>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ["item", "endpoint"],

  data: () => ({
    otp: "", // Holds the combined 4-digit OTP string
    otpDigits: ["", "", "", ""], // Array for individual digit models
    valid: false,
    loading: false,
    dialog: false,
    response: null,
    selectedDoor: {
      responseStatus: false,
      text: null,
    },
    errorClass: null,
    showDoorGif: false,
  }),
  async created() {
    this.loading = true;
  },

  watch: {
    // Combine the individual digits into the main 'otp' property
    otpDigits: {
      handler(newVal) {
        this.otp = newVal.join("");
      },
      deep: true,
    },
  },

  methods: {
    // -----------------------------------------------------------
    // Input Handling for a better OTP experience
    // -----------------------------------------------------------
    handleInput(event, index) {
      const value = event.target.value;

      // Ensure only a single digit is entered
      if (!/^\d*$/.test(value)) {
        this.otpDigits[index - 1] = "";
        return;
      }

      // Move focus to the next input field
      if (value && index < 4) {
        this.$nextTick(() => {
          document.getElementById(`otp-input-${index + 1}`).focus();
        });
      }

      // If the last digit is entered, call submitOtp automatically (optional)
      if (index === 4 && value) {
        // this.submitOtp(); // Uncomment to auto-submit
      }
    },

    handleKeyDown(event, index) {
      // Handle backspace to delete a digit and move focus backward
      if (
        event.key === "Backspace" &&
        index > 1 &&
        !this.otpDigits[index - 1]
      ) {
        event.preventDefault(); // Stop default backspace behavior
        this.$nextTick(() => {
          document.getElementById(`otp-input-${index - 1}`).focus();
        });
      }
    },
    async checkOtp() {
      if (this.otp.length === 4) {
        let config = {
          params: {
            company_id: this.$auth.user.company_id,
            pin: this.otp,
          },
        };

        let { data } = await this.$axios.get("check-pin", config);

        if (data.status) {
          this.openDoor();
          return;
        }

        this.shakeTheInputSection();
      }

      this.shakeTheInputSection();
    },

    async openDoor() {
      let options = {
        params: {
          device_id: this.item.device_id,
          otp: this.otp, // Pass the OTP to the API
        },
      };
      try {
        await this.$axios.get("open_door", options);

        this.showDoorGif = true;
      } catch (error) {
        console.log(error);
      } finally {
        setTimeout(() => {
          this.otpDigits = ["", "", "", ""];
          this.dialog = false;
          this.showDoorGif = false;
        }, 3000);
      }
    },

    shakeTheInputSection() {
      // 1. Remove the class (equivalent to setShake(false))
      this.errorClass = null;

      // 2. Defer re-applying the class to the next render cycle.
      // The browser removes the class, repaints, and then adds it back.
      // This is essentially what requestAnimationFrame does in React context.
      this.$nextTick(() => {
        this.errorClass = "animate-shake"; // Equivalent to setShake(true)

        // 3. Set a timeout to remove the class and clear inputs
        setTimeout(() => {
          this.errorClass = null;

          // Clear inputs (equivalent to setOtp("") and array mutation)
          this.otpDigits.splice(0, 4, "", "", "", "");

          // Focus the first input
          if (this.$refs.otpInputs && this.$refs.otpInputs.length > 0) {
            this.$refs.otpInputs[0].focus();
          }
        }, 1000);
      });
    },
  },
};
</script>
