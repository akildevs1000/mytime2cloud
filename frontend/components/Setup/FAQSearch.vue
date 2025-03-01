<template>
  <div>
    <!-- Button to open the FAQ search drawer -->
    <v-btn
      style="position: fixed; right: 8px; bottom: 1%"
      color="primary"
      @click="drawer = true"
      fab
      ><v-icon>mdi-chat</v-icon></v-btn
    >
    <!-- FAQ Search Drawer -->
    <v-dialog width="700" v-model="drawer" app right temporary>
      <WidgetsClose left="690"/>
      <v-card flat class="pa-0">
        <!-- Remove shadow with flat and padding set to 0 -->
        <!-- Content Section for FAQs -->
        <v-card-text class="pa-0" style="overflow-y: auto">
          <v-list v-if="faqs.length">
            <v-list-item-group>
              <v-list-item v-for="faq in faqs" :key="faq.id">
                <v-list-item-content>
                  <v-list-item-title>{{ faq.question }}</v-list-item-title>
                  <v-list-item-subtitle>{{ faq.answer }}</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>

          <!-- Optional: You can show a message if no FAQs are found -->
          <!-- <v-alert v-else type="info" dense>No FAQs found</v-alert> -->
        </v-card-text>

        <!-- Input Section for FAQ Search -->
        <v-card-subtitle class="pa-2">
          <v-text-field
            outlined
            dense
            v-model="searchQuery"
            placeholder="Ask Anything"
            single-line
            hide-details
            @input="searchIt"
          ></v-text-field>
          <v-btn color="primary" block @click="searchIt" class="chat-btn">
            Ask a Question
          </v-btn>
        </v-card-subtitle>

      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  data() {
    return {
      searchQuery: "", // Holds the user's search query
      faqs: [], // Holds the list of FAQ results
      drawer: false, // Controls drawer visibility
    };
  },
  methods: {
    async getDataFromApi(query) {
      try {
        const response = await this.$axios.get(
          `/faqs-list?query=${query}`
        );
        this.faqs = response.data; // Store the search results
        this.searchQuery = "";
      } catch (error) {
        console.error("Error fetching FAQs:", error);
      }
    },
    searchIt() {
      if (this.searchQuery.length >= 3) {
        // Call the API with the query parameter
        this.getDataFromApi(this.searchQuery);
      } else {
        this.faqs = []; // If search query is shorter than 3 characters, clear results
      }
    },
  },
  watch: {
    // Reset the FAQs list when searchQuery is cleared
    searchQuery(newQuery) {
      if (!newQuery) {
        this.faqs = [];
      }
    },
  },
};
</script>
