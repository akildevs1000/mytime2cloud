<template>
  <div>
    <v-row>
      <v-col md="12">
        <v-slide-group
          v-model="model"
          class="px-4"
          active-class="success"
          show-arrows
        >
          <v-slide-item
            v-for="(item, index) in logs"
            :key="index"
            v-slot="{ active, toggle }"
          >
            <div class="card mx-2 my-2 w-25" v-if="index < 6">
              <div class="banner">
                <v-img
                  class="gg"
                  viewBox="0 0 100 100"
                  style="border-radius: 50%;  height: 80px; max-width: 80px !important"
                  :src="item.profile_picture || '/no-profile-image.jpg'"
                ></v-img>
                <!-- </svg> -->
              </div>
              <!-- employee_id first_name in device_in.short_name -->
              <div class="menu">
                <div class="opener"></div>
              </div>
              <h2 class="name" style="font-size:15px">
                {{ item.first_name }}
              </h2>
              <div class="title" style="font-size:12px !important">
                {{ item.UserCode }}
              </div>
              <div class="title" style="font-size:12px !important">
                {{ item.designation.name }}
              </div>
              <div class="actions">
                <div class="follow-info">
                  <h2>
                    <a href="#"
                      ><span>{{
                        (item && getTime(item.RecordDate)) || "---"
                      }}</span
                      ><small>In</small></a
                    >
                  </h2>
                  <h2>
                    <a href="#"
                      ><span>{{ (item && item.short_name) || "MED" }}</span
                      ><small>D/Name</small></a
                    >
                  </h2>
                </div>
              </div>
            </div>
          </v-slide-item>
        </v-slide-group>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  // props: ["data", "headers"],
  data() {
    return {
      logs: [],
      url: process.env.SOCKET_ENDPOINT,
      socket: null
    };
  },
  mounted() {
    this.socketConnection();
  },
  methods: {
    getTime(item) {
      if (!item) {
        return false;
      }
      var d = new Date(item);
      d.getHours();
      d.getMinutes();
      return d.getHours() + ":" + d.getMinutes();
    },
    socketConnection() {
      this.socket = new WebSocket(this.url);

      this.socket.onmessage = ({ data }) => {
        let json = JSON.parse(data);
        if (json.Status == 200 && json.Data.UserCode !== 0) {
          this.getDetails(json.Data);
          console.log(json.Data);
        }
      };
    },
    getDetails(item) {
      this.$axios
        .get(`/device/${item.DeviceID}/${item.UserCode}/details`)
        .then(({ data }) => {
          if (data.company_id == this.$auth.user.company.id) {
            let obj = {
              ...item,
              ...data
            };
            console.log(obj);

            this.logs.unshift(obj);
          }
        });
    }
  }
};
</script>

<!-- Accesstype : 1 BodyTemperature : 0 DeviceID : "OX-8862021010099" Photo : 1
RecordDate : "2022-10-17 19:53:02" RecordImage :
"/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDABALDA4MChAODQ4SE RecordMsg : "人脸验证"
RecordNumber : 14182 RecordType : 1 UserCode : 544 -->

<style scoped>
@import url("https://fonts.googleapis.com/css?family=Montserrat:400,400i,700");
body {
  font-size: 16px;
  color: #404040;
  font-family: Montserrat, sans-serif;
  background-image: linear-gradient(
    to bottom right,
    #ff9eaa 0% 65%,
    #e860ff 95% 100%
  );
  background-position: center;
  background-attachment: fixed;
  margin: 0;
  padding: 2rem 0;
  display: grid;
  place-items: center;
  box-sizing: border-box;
}
.card {
  /* background-color: #fff;
  max-width: 360px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 2rem;
  box-shadow: 0px 1rem 1.5rem rgba(0, 0, 0, 0.5); */

  height: 300px !important;
  background-color: #fff !important;
  max-width: 200px !important;
  display: flex !important;
  flex-direction: column !important;
  overflow: hidden !important;
  border-radius: 2rem !important;
}
.card .banner {
  /* background-image: url("https://images.unsplash.com/photo-1545703549-7bdb1d01b734?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=400&fit=max&ixid=eyJhcHBfaWQiOjE0NTg5fQ"); */
  background-color: #5fafa3;
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  height: 11rem;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  box-sizing: border-box;
}
.card .banner .gg {
  background-color: #fff;
  width: 8rem;
  height: 8rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
  border-radius: 50%;
  transform: translateY(50%);
  transition: transform 200ms cubic-bezier(0.18, 0.89, 0.32, 1.28);
}
.card .banner svg:hover {
  transform: translateY(50%) scale(1.3);
}
.card .menu {
  width: 100%;
  height: 5.5rem;
  padding: 1rem;
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  position: relative;
  box-sizing: border-box;
}
.card .menu .opener {
  width: 2.5rem;
  height: 2.5rem;
  position: relative;
  border-radius: 50%;
  transition: background-color 100ms ease-in-out;
}
.card .menu .opener:hover {
  background-color: #f2f2f2;
}
.card .menu .opener span {
  background-color: #404040;
  width: 0.4rem;
  height: 0.4rem;
  position: absolute;
  top: 0;
  left: calc(50% - 0.2rem);
  border-radius: 50%;
}
.card .menu .opener span:nth-child(1) {
  top: 0.45rem;
}
.card .menu .opener span:nth-child(2) {
  top: 1.05rem;
}
.card .menu .opener span:nth-child(3) {
  top: 1.65rem;
}
.card h2.name {
  text-align: center;
  padding: 0 2rem 0.5rem;
  margin: 0;
}
.card .title {
  color: #a0a0a0;
  font-size: 0.85rem;
  text-align: center;
  /* padding: 0 2rem 1.2rem; */
}
.card .actions {
  padding: 0 2rem 1.2rem;
  display: flex;
  flex-direction: column;
  order: 99;
}
.card .actions .follow-info {
  padding: 0 0 1rem;
  display: flex;
}
.card .actions .follow-info h2 {
  text-align: center;
  width: 50%;
  margin: 0;
  box-sizing: border-box;
}
.card .actions .follow-info h2 a {
  text-decoration: none;
  padding: 0.8rem;
  display: flex;
  flex-direction: column;
  border-radius: 0.8rem;
  transition: background-color 100ms ease-in-out;
}
.card .actions .follow-info h2 a span {
  color: #1c9eff;
  font-weight: bold;
  transform-origin: bottom;
  transform: scaleY(1.3);
  transition: color 100ms ease-in-out;
  font-size: 20px;
}
.card .actions .follow-info h2 a small {
  color: #afafaf;
  font-size: 0.85rem;
  font-weight: normal;
}
.card .actions .follow-info h2 a:hover {
  background-color: #f2f2f2;
}
.card .actions .follow-info h2 a:hover span {
  color: #007ad6;
}
.card .actions .follow-btn button {
  color: inherit;
  font: inherit;
  font-weight: bold;
  background-color: #ffd01a;
  width: 100%;
  border: none;
  padding: 1rem;
  outline: none;
  box-sizing: border-box;
  border-radius: 1.5rem/50%;
  transition: background-color 100ms ease-in-out,
    transform 200ms cubic-bezier(0.18, 0.89, 0.32, 1.28);
}
.card .actions .follow-btn button:hover {
  background-color: #efb10a;
  transform: scale(1.1);
}
.card .actions .follow-btn button:active {
  background-color: #e8a200;
  transform: scale(1);
}
.card .desc {
  text-align: justify;
  padding: 0 2rem 2.5rem;
  order: 100;
}
</style>
