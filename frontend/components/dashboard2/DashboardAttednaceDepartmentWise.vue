<template>
    <div class="py-2 mt-2 pl-5">

        <v-row>
            <v-col md=" 6">
                <h4>Today Presents By Department wise</h4>
            </v-col>

            <v-col md="2"> <v-icon>mdi-dots-vertical</v-icon></v-col>

        </v-row>
        <v-row style="font-weight: bold;">
            <v-col md="1" class="text--center"> </v-col>
            <v-col md="5"> </v-col>
            <v-col md="1" title="presentCount" style="text-align: center;">P</v-col>
            <v-col md="1" title="absentCount" style="text-align: center;">A </v-col>
            <v-col md="1" title="missingCount" style="text-align: center;">M </v-col>
            <v-col md="1" title="absentCount" style="text-align: center;">V </v-col>
            <v-col md="1" title="missingCount" style="text-align: center;">L </v-col>
        </v-row>
        <v-row v-for="(item, index, i) in departments">
            <v-col md="1" class="text--center">
                <v-avatar size="40" style="color:#FFF" :color="(i + 1) % 2 == 0 ? 'green' : 'red'">
                    <v-icon size="20" style="color:#FFF">mdi-laptop</v-icon>
                </v-avatar>


            </v-col>
            <v-col md="5" class="mt-2">{{ index }} </v-col>
            <v-col md="1" title="Presents" style="color:green;text-align: center;">
                {{ item.presentCount }}
            </v-col>
            <v-col md="1" title="Absents" style="color:red;text-align: center;">{{ item.absentCount }}</v-col>
            <v-col md="1" title="Missing" style="color:orange;text-align: center;">{{ item.missingCount }} </v-col>
            <v-col md="1" title="Vacation" style=" text-align: center;">{{ item.vaccationCount }} </v-col>
            <v-col md="1" title="Leave" style=" text-align: center;">{{ item.leaveCount }} </v-col>
        </v-row>




    </div>
</template>
  
<script>
// import VueApexCharts from 'vue-apexcharts'
export default {

    data() {
        return {
            departments: [],
        };
    },
    watch: {



    },
    created() {
        //  this.loading = true;
        this.getDataFromApi();

    },
    mounted() {


    },

    methods: {

        getDataFromApi() {
            let options = {
                params: {

                    company_id: this.$auth.user.company_id,
                },
            };

            this.$axios.get('dashboard_get_count_department', options).then(({ data }) => {

                this.departments = data;



            });
        }

    },
};
</script>
  