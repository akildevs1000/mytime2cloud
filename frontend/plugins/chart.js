import Vue from 'vue'
import VueGoogleCharts from 'vue-google-charts'

Vue.use(VueGoogleCharts)

// import Vue from 'vue'
// import { Line,Pie,Doughnut,Bar } from 'vue-chartjs'

// const check = (name,chart) => {
// 	Vue.component(name, {
// 		extends: chart,
// 		props: ['data', 'options'],
// 		mounted () {
// 			this.renderChart(this.data, this.options)
// 		}
// 	})
// }

// check('LineChart',Line);
// check('DoughnutChart',Doughnut);
// check('BarChart',Bar);
// check('PieChart',Pie);
