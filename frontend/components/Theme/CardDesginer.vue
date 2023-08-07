<template>
  <v-container fluid>
    <v-row>
      <v-col
        v-for="(card, index) in cardData"
        :key="index"
        :cols="card.cols"
        :sm="card.sm"
        :md="card.md"
      >
        <v-card
          :color="card.color"
          dark
          dense
          style="border-radius: 15px !important"
        >
          <div class="text-right px-2">
            <v-icon small @click="editCard(index)">mdi-pencil</v-icon>
            <v-icon small @click="deleteCard(index)">mdi-delete</v-icon>
          </div>
          <div class="text-center pa-5">
            <h1>{{ card.value }}</h1>
            <p>{{ card.title }}</p>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card
          @click="addCard"
          :color="'background'"
          dark
          dense
          style="border-radius: 15px !important"
        >
          <div class="text-right px-2">
            <v-icon disabled color="background" small @click="editCard(index)"
              >mdi-pencil</v-icon
            >
          </div>
          <div class="text-center pa-5">
            <h1><v-icon>mdi-plus-circle-outline</v-icon></h1>
            <p>Add New Card</p>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-dialog v-model="editDialog" max-width="900">
          <v-card>
            <v-card-title
              >Edit Card <v-spacer></v-spacer>
              <v-icon color="black" @click="closeEdit">mdi-close</v-icon>
              <v-icon color="black" @click="saveEdit">mdi-database</v-icon>
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col>
                  <v-text-field
                    @input="reflectChange"
                    v-model="editedCard.cols"
                    label="Default Col"
                  ></v-text-field>
                </v-col>
                <v-col>
                  <v-text-field
                    @input="reflectChange"
                    v-model="editedCard.md"
                    label="Medium Col"
                  ></v-text-field>
                </v-col>
                <v-col>
                  <v-text-field
                    @input="reflectChange"
                    v-model="editedCard.sm"
                    label="Small Col"
                  ></v-text-field>
                </v-col>
              </v-row>

              <v-row>
                <v-col>
                  <v-text-field
                    @input="reflectChange"
                    v-model="editedCard.title"
                    label="Title"
                  ></v-text-field>
                </v-col>
                <v-col>
                  <v-autocomplete
                    @input="reflectCount"
                    v-model="editedCard.value"
                    item-text="title"
                    item-value="value"
                    :items="[
                      {
                        title: `Emplopyee Count`,
                        value: `employeeCount`,
                      },
                      {
                        title: `Present`,
                        value: `presentCount`,
                      },
                      {
                        title: `Absent`,
                        value: `absentCount`,
                      },
                      {
                        title: `Missing`,
                        value: `missingCount`,
                      },
                    ]"
                  >
                  </v-autocomplete>
                </v-col>
              </v-row>

              <v-row>
                <v-col>
                  Color Picker

                  <v-color-picker
                    @input="reflectChange"
                    v-model="editedCard.color"
                    dot-size="20"
                    show-swatches
                    mode="hexa"
                    swatches-max-height="250"
                  ></v-color-picker>
                </v-col>
                <v-col>
                  Card Preview
                  <v-card
                    :color="editedCard.color"
                    dark
                    dense
                    style="border-radius: 15px !important"
                  >
                    <div class="text-right px-2">
                      <v-icon disabled small @click="editCard(index)"
                        >mdi-pencil</v-icon
                      >
                      <v-icon disabled small @click="deleteCard(index)"
                        >mdi-delete</v-icon
                      >
                    </div>
                    <div class="text-center pa-5">
                      <h1>{{ displayValueCount }}</h1>
                      <p>{{ editedCard.title }}</p>
                    </div>
                  </v-card>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-dialog>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  props: ["page"],
  data() {
    return {
      editDialog: false,
      editedCard: {
        title: "New Card",
        value: "0",
        color: "#5fafa3",
        icon: "mdi mdi-account",
        cols: "12",
        sm: "6",
        md: "3",
      },
      editIndex: null,

      cardData: [
        // {
        //   title: "Total Employees",
        //   value: 5,
        //   color: "#FF0000FF",
        //   icon: "mdi mdi-account",
        //   cols: "12",
        //   sm: "6",
        //   md: "3",
        // },
        // {
        //   title: "Present",
        //   value: 5,
        //   color: "#004BE4ED",
        //   icon: "mdi mdi-book",
        //   cols: "12",
        //   sm: "6",
        //   md: "3",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "green",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Employees",
        //   value: 5,
        //   color: "yellow",
        //   icon: "mdi mdi-account",
        // },
        // {
        //   title: "Total Present",
        //   value: 5,
        //   color: "orange",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "purple",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "brown",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "teal",
        //   icon: "mdi mdi-book",
        // },
        // {
        //   title: "Total Missing",
        //   value: 5,
        //   color: "pink",
        //   icon: "mdi mdi-book",
        // },
      ],
      displayValueCount: 0,
    };
  },
  created() {
    this.getRecord();
  },

  methods: {
    getRecord() {
      let payload = {
        page: this.page,
        type: "card",
        company_id: this.$auth.user.company.id,
      };

      let options = {
        params: payload,
      };

      this.$axios
        .get("theme", options)
        .then(({ data }) => {
          this.cardData = data;
        })
        .catch((e) => console.log(e));
    },
    addCard() {
      this.cardData.push(this.editedCard);
    },

    editCard(index) {
      this.editDialog = true;
      this.editIndex = index;
      this.editedCard = { ...this.cardData[index] };
    },
    deleteCard(index) {
      this.cardData.splice(index, 1);
    },
    reflectChange() {
      this.cardData[this.editIndex] = { ...this.editedCard };
    },
    reflectCount() {
      let payload = {
        value: this.editedCard.value,
        company_id: this.$auth.user.company.id,
      };

      this.$axios
        .get("theme_count", { params: payload })
        .then(({ data }) => {
          this.displayValueCount = data;
        })
        .catch((e) => console.log(e));
    },
    saveEdit() {
      this.reflectChange();

      let payload = {
        page: "dashboard",
        type: "card",
        style: this.cardData,
        company_id: this.$auth.user.company.id,
      };

      this.$axios
        .post("theme", payload)
        .then(({ data }) => {
          alert("Data inserted");
          this.getRecord();
          this.closeEdit();
        })
        .catch((e) => console.log(e));
    },
    closeEdit() {
      this.editDialog = false;
      this.editedCard = {
        title: "New Card",
        value: "0",
        color: "#4A79DBED",
        icon: "mdi mdi-account",
        cols: "12",
        sm: "6",
        md: "3",
      };
      this.editIndex = null;
    },
  },
};
</script>
