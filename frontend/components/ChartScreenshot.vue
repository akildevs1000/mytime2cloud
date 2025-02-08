<template>
  <v-app>
    <v-container>
      <v-row justify="center">
        <v-col cols="12" md="8">
          <!-- Target area to capture -->
          <v-card id="screenshot-target" class="pa-4">
            <h1>Chart Screenshot <v-icon>mdi-camera</v-icon></h1>
            <div id="chart"></div>
          </v-card>
        </v-col>
      </v-row>
      <v-row justify="center">
        <v-col cols="12" md="8" class="text-center">
          <!-- Button to take screenshot with MDI icon -->
          <v-btn color="primary" @click="takeScreenshot">
            <v-icon left>mdi-camera</v-icon> Save as PDF
          </v-btn>
        </v-col>
      </v-row>
      <v-row justify="center" v-if="screenshot">
        <v-col cols="12" md="8">
          <!-- Display the screenshot -->
          <v-card id="screenshot-result" class="pa-4">
            <img :src="screenshot" alt="Screenshot" style="max-width: 100%;">
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-app>
</template>

<script>
export default {
  data() {
    return {
      screenshot: null, // Stores the screenshot data URL
      chartOptions: {
        chart: {
          type: 'line',
          height: 300,
          toolbar: {
            show: false, // Hide the toolbar (including download, zoom, etc.)
          },
          zoom: {
            enabled: false, // Disable zooming
          },
        },
        series: [{
          name: 'Sales',
          data: [30, 40, 35, 50, 49, 60, 70, 91, 125],
        }],
        xaxis: {
          categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
        },
        colors: ['#1976D2'], // Vuetify primary color
        tooltip: {
          enabled: false, // Disable tooltips
        },
      },
    };
  },
  methods: {
    takeScreenshot() {
      // Capture the target element
      html2canvas(document.getElementById('screenshot-target')).then((canvas) => {
        // Convert canvas to data URL
        const imgData = canvas.toDataURL('image/png');

        // Create a new PDF
        const pdf = new jspdf.jsPDF('p', 'mm', 'a4'); // Portrait, millimeters, A4 size
        const imgWidth = 210; // A4 width in mm
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Calculate height to maintain aspect ratio

        // Add the image to the PDF
        pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);

        // Save the PDF
        pdf.save('screenshot.pdf');
      });
    },
  },
  mounted() {
    // Load external scripts dynamically
    const scripts = [
      { src: 'https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js', type: 'text/javascript', async: true },
      { src: 'https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js', type: 'text/javascript', async: true },
      { src: 'https://cdn.jsdelivr.net/npm/apexcharts@latest', type: 'text/javascript', async: true },
      { src: 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', type: 'text/javascript', async: true },
      { src: 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', type: 'text/javascript', async: true },
    ];

    // Append scripts dynamically
    scripts.forEach(script => {
      const scriptTag = document.createElement('script');
      scriptTag.src = script.src;
      scriptTag.type = script.type;
      scriptTag.async = script.async;
      document.head.appendChild(scriptTag);
    });

    // Initialize ApexCharts after scripts are loaded
    const interval = setInterval(() => {
      if (window.ApexCharts) {
        clearInterval(interval);
        new window.ApexCharts(document.querySelector('#chart'), this.chartOptions).render();
      }
    }, 100);
  },
};
</script>