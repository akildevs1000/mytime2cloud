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
    <style scoped>
      .chat-drawer {
        display: flex;
        flex-direction: column;
        height: 100%;
      }

      .chat-container {
        display: flex;
        flex-direction: column;
        height: 94%;
      }

      .chat-messages {
        flex-grow: 1;
        overflow-y: auto;
        padding: 10px;
      }

      .chat-input {
        display: flex;
        align-items: center;
        padding: 10px;
        background: white;
      }

      .chat-message-container {
        display: flex;
        flex-direction: column;
        margin-bottom: 10px;
      }

      .chat-message {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 20px;
        margin-bottom: 5px;
        word-wrap: break-word;
        display: inline-block;
      }

      .chat-message-you {
        background-color: #dcf8c6; /* Light green for 'You' */
        align-self: flex-end; /* Align user messages to the right */
      }

      .chat-message-bot {
        background-color: #f1f0f0; /* Light gray for 'Bot' */
        align-self: flex-start; /* Align bot messages to the left */
      }

      .chat-message-text {
        font-size: 14px;
        line-height: 1.5;
      }
    </style>

    <v-navigation-drawer
      width="400"
      v-model="drawer"
      app
      right
      temporary
      class="chat-drawer"
    >
      <v-alert dense class="primary white--text">
        <v-row no-gutters>
          <v-col>
            <div>
              Ask Anything
            </div>
          </v-col>
          <!-- <v-col cols="6" v-if="$auth?.user?.company?.id == 2">
            <v-switch dense hide-details label="Use AI" dark ></v-switch>
          </v-col> -->
        </v-row>
      </v-alert>
      <v-card flat class="chat-container">
        <v-card-text
          class="chat-messages"
          id="chat-messages"
          style="white-space: pre-line"
        >
          <div
            v-for="(msg, index) in messages"
            :key="index"
            class="chat-message-container"
          >
            <div
              class="chat-message"
              :class="{
                'chat-message-you': msg.sender === 'You',
                'chat-message-bot': msg.sender === 'AI',
              }"
            >
              <span class="chat-message-text" v-html="msg.text"></span>
            </div>
          </div>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="chat-input">
          <v-text-field
            outlined
            dense
            hide-details
            v-model="userInput"
            label="Type a message..."
            @keyup.enter="sendMessage"
            class="flex-grow-1"
            spellcheck="true"
          ></v-text-field>
          <v-icon size="35" color="primary" @click="sendMessage"
            >mdi-send-circle</v-icon
          >
        </v-card-actions>
      </v-card>
    </v-navigation-drawer>
  </div>
</template>

<script>
export default {
  data() {
    return {
      dictionary: null,
      text: "",
      isCorrect: true,
      suggestions: [],
      typo: null,

      userInput: "",
      messages: [
        {
          text: "Hello! How can I assist you today? 😊",
          sender: "AI",
        },
      ],
      searchQuery: "", // Holds the user's search query
      faqs: [], // Holds the list of FAQ results
      drawer: false, // Controls drawer visibility
    };
  },
  mounted() {},
  methods: {
    async getDataFromApi(query) {
      try {
        const response = await this.$axios.get(`/faqs-list?query=${query}`);
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

    async sendMessage() {
      if (this.userInput.trim() !== "") {
        this.messages.push({ sender: "You", text: this.userInput });
        let userMessage = this.userInput;
        this.userInput = "";
        let endpoint = `/faqs-list?query=${encodeURIComponent(userMessage)}`;

        // this.messages.push({
        //   sender: "AI",
        //   text: "Thinking...",
        // });
        // let botResponseIndex = this.messages.length - 1; // ignore thinking message

        let botResponseIndex = this.messages.length;

        try {
          let { data } = await this.$axios.get(endpoint);

          if (data.ask_ai) {
            this.askAI(userMessage);
            return;
          }
          if (!data?.data?.length) {
            this.$set(this.messages, botResponseIndex, {
              sender: "AI",
              text: "No answer found",
            });
            return;
          }

          // Join answers with line breaks
          let answer = data.data
            .map((item, index) => `<b>${index + 1}.</b> ${item.answer}`)
            .join("\n\n");

          this.messages.push({ sender: "AI", text: answer });
        } catch (error) {
          console.error("Error fetching response:", error);
          this.messages.push({
            sender: "AI",
            text: "There was an error processing your request.",
          });
        }
      }
    },
    async askAI(question) {
      let ollama_app_key = process.env.OLLAMA_APP_KEY;
      if (!ollama_app_key) {
        this.messages.push({
          sender: "AI",
          text: "Access token is missing or invalid",
        });
        return;
      }

      let botResponseIndex = this.messages.length - 1; // ignore thinking message
      try {
        const response = await fetch("http://localhost:7799/ask", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            question,
            ollama_app_key,
          }),
        });

        if (response.status !== 200) {
          this.$set(this.messages, botResponseIndex, {
            sender: "AI",
            text: response.statusText,
          });
          return;
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();

        let buffer = "";
        let cleanedResponse = "";

        while (true) {
          const { done, value } = await reader.read();
          if (done) break;

          buffer += decoder.decode(value, { stream: true });
          const lines = buffer.split("\n");
          buffer = lines.pop();

          for (const line of lines) {
            if (line.trim() === "") continue;

            try {
              const chunkData = JSON.parse(line);
              cleanedResponse += chunkData.response
                .replace(/<think>/g, "")
                .replace(/<\/think>/g, "")
                .replace(/\s+/g, " ");

              this.$set(this.messages, botResponseIndex, {
                sender: "AI",
                text: cleanedResponse,
              });
            } catch (error) {
              this.messages.push({
                sender: "AI",
                text: "Error parsing JSON chunk: " + error.message,
              });
            }
          }
        }
      } catch (error) {
        console.log("🚀 ~ askAI ~ error:", error);
        this.messages.push({
          sender: "AI",
          text: "Error fetching response: " + error.message,
        });
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
