<template>
    <div>

        <div :id="name"></div>


    </div>
</template>
  
<script>
// import VueApexCharts from 'vue-apexcharts'
export default {
    props: ["name", "height"],
    data() {
        return {
            series: [{
                name: 'Net Profit',
                data: [44, 55, 57, 56, 61, 58, 63, 60, 66]
            }, {
                name: 'Revenue',
                data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
            }, {
                name: 'Free Cash Flow',
                data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
            }],
            chartOptions: {
                chart: {
                    type: 'bar',
                    width: '90%',

                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                },
                yaxis: {
                    title: {
                        text: '$ (thousands)'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "$ " + val + " thousands"
                        }
                    }
                }
            },




        };
    },
    watch: {



    },
    created() {
        //  this.loading = true;


    },
    mounted() {
        // this.getYears();
        this.chartOptions.chart.height = this.height;
        this.chartOptions.series = this.series;
        new ApexCharts(
            document.querySelector("#" + this.name),
            this.chartOptions
        ).render();
        // this.getDataFromApi();


    },

    methods: {
        // getDataFromApi() {
        //     let options = {
        //         params: {
        //             page: "dashboard2",
        //             type: "card",
        //             company_id: this.$auth.company_id,
        //         },
        //     };

        //     this.$axios
        //         .get("theme", options)
        //         .then(async ({ data }) => {
        //             this.loading = false;
        //             this.data = data;
        //             this.chartOptions.colors = await data.map((e) => e.color);
        //             this.chartOptions.labels = await data.map((e) => e.title);
        //             this.chartOptions.series = await data.map((e) => parseInt(e.calculated_value));
        //             new ApexCharts(
        //                 document.querySelector("#AttendancePie"),
        //                 this.chartOptions
        //             ).render();
        //         })
        //         .catch((e) => console.log(e));
        // },
        // formatDate(date) {
        //     var day = date.getDate();
        //     var month = date.getMonth() + 1; // Months are zero-based
        //     var year = date.getFullYear();

        //     return year + '-' + (month < 10 ? '0' : '') + month;//+ '-' + (day < 10 ? '0' : '') + day;
        // },
        // printTable() {


        //     let htmlHeaderContent = '<center><h4>Revenue Report - Month wise -  ' + this.year + '</h4></center>';
        //     const printWindow = window.open('', '_blank');
        //     printWindow.document.write('<html><head><title>Print</title></head><body>');
        //     printWindow.document.write(htmlHeaderContent);
        //     printWindow.document.write(document.querySelector('.v-data-table').outerHTML);
        //     printWindow.document.write('<style>.text-right{text-align:right;} td,th {border-top:1px solid #DDD;border-left:1px solid #DDD} table{border-right:1px solid #DDD;border-bottom:1px solid #DDD;width:100%} body{width:95%}</style></body></html>');
        //     printWindow.document.close();
        //     printWindow.print();
        // },
        // onPageChange() {
        //     this.getDataFromApi();
        // },
        // goToDailyReport(year, month) {
        //     //this.getDataFromApi();
        //     this.$emit('goToDailyReportTab', { tab: 1, filter_from_date: year + '-' + month + '-01', filter_to_date: year + '-' + month + '-' + this.getTotalDays(year, month) });

        //     // this.$store.dispatch('setData', { year: item.year_number, month: item.month_number });
        //     // this.$router.push({ path: '/management/report/daily_revenue' });

        // },
        // can(per) {
        //     let u = this.$auth.user;
        //     return (
        //         (u && u.permissions.some(e => e == per || per == "/")) || u.is_master
        //     );
        // },

        // commonMethod() {
        //     this.getDataFromApi();
        // },

        // getDaysInMonth(month = 2, year = new Date().getFullYear()) {

        // },
        // getTotalDays(year, month) {
        //     return new Date(year, month, 0).getDate();
        // },

        // forceChartRerender() {
        //     this.chartKey += 1;
        // },
        // getYears() {
        //     const year = new Date().getFullYear();
        //     this.years = Array.from({ length: 10 }, (_, i) => year - i);

        // },
        // getDataFromApi(url = this.endpoint) {


        //     let { sortBy, sortDesc, page, itemsPerPage } = this.options;

        //     let sortedBy = sortBy ? sortBy[0] : "";
        //     let sortedDesc = sortDesc ? sortDesc[0] : "";


        //     this.loading = true;
        //     let options = {
        //         params: {
        //             page: page,
        //             sortBy: sortedBy,
        //             sortDesc: sortedDesc,
        //             per_page: itemsPerPage,
        //             company_id: this.$auth.user.company.id,

        //             //year: this.year,
        //             filter_from_date: this.filter_from_date,
        //             filter_to_date: this.filter_to_date,
        //             t: Math.random()
        //         },
        //     };

        //     this.$axios.get('get_report_monthly_wise_group', options).then(({ data }) => {

        //         this.data_table = data.data;
        //         this.loading = false;
        //         this.totalRowsCount = data.totalRowsCount;;
        //         this.grandTotal = data.grandTotal;

        //         let counter = 0;
        //         this.data_table.forEach(item => {

        //             this.barSeriesNew[0]["data"][counter] = parseInt(item.income.replaceAll(',', ''));
        //             this.barSeriesNew[1]["data"][counter] = parseInt(item.total_expenses.replaceAll(',', ''));
        //             this.barSeriesNew[2]["data"][counter] = parseInt(item.sold);
        //             this.barChartOptionsNew.xaxis.categories[counter] = item.month;
        //             // this.barChartOptionsNew.colors[counter] = item.color;
        //             this.barChartOptionsNew.customLabel[counter] = "<table>"
        //                 + "<tr><td>Income</td><td style='text-align:right;color:green'> :  " + item.income + '</td></tr> '
        //                 + "<tr><td>Non-Mng Expenses</td><td style='text-align:right;color:red'>   - " + item.expenses + '</td></tr> '
        //                 + "<tr><td>Management Expenses</td><td style='text-align:right;color:red'>  - " + item.management_expenses + '</td></tr> '
        //                 + "<tr><td>Proffit</td><td style='text-align:right; '>   = " + item.profit + '</td></tr>'
        //                 + "<tr><td>Rooms Sold</td><td> :  " + item.sold + "</td></tr> "
        //                 + "</table > "

        //             counter++;




        //         });
        //         try {
        //             this.$refs.realtimeChart.updateSeries([{
        //                 data: this.barSeriesNew[0].data,
        //             }, {
        //                 data: this.barSeriesNew[1].data,
        //             }, {
        //                 data: this.barSeriesNew[2].data,
        //             }], false, true);
        //         }
        //         catch (e) { }

        //         this.loading = false;

        //     });
        // },
        // process(type, model) {




        //     let url =
        //         process.env.BACKEND_URL +
        //         `${type}?company_id=${this.$auth.user.company.id}&filter_from_date=${this.filter_from_date}&filter_to_date=${this.filter_to_date}`;
        //     console.log(url);
        //     let element = document.createElement("a");
        //     element.setAttribute("target", "_blank");
        //     element.setAttribute("href", `${url}`);
        //     document.body.appendChild(element);
        //     element.click();
        // },
    },
};
</script>
  