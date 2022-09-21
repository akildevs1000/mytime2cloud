<template>
<v-app>
<div class="text-center ma-2">

<v-snackbar
v-model="snackbar"
:top="'top'"
:color="color"
>
{{response.msg}}
<v-btn      
text
@click="snackbar = false"
>
Close
</v-btn>
</v-snackbar>
</div>


<v-data-table
:headers="headers"
:items="orders"
:search="search"
class="elevation-1"
:options.sync="options"
:server-items-length="data.total"
@pagination="paginate"
:items-per-page=10
:footer-props="{
itemsPerPageOptions:[10]
}"
>


<template  v-slot:item.status="{ item }">
<v-chip small class="white--text" :class="myBtnClass(item.status)">
  {{(item.status == 'on the way') ? 'Dispatch' : item.status}}
</v-chip>
</template>


<template v-slot:top>

<v-toolbar flat>
<v-toolbar-title dark>Orders</v-toolbar-title>
<v-divider
class="mx-4"
inset
vertical
></v-divider>
<v-text-field
v-model.lazy="search"
@input="searchIt"
label="Search"
single-line
hide-details
></v-text-field>





<v-dialog v-model="dialog" max-width="1200px">

<v-card>
<v-card-text>

<v-container>
<v-row>
<v-col>
<v-toolbar
class="primary mb-2 accent--text"
dark
flat
>

<v-toolbar-title>Order # {{editedItem.id}}</v-toolbar-title>
<v-spacer></v-spacer>
<v-toolbar-title>{{ editedItem.company_name }}</v-toolbar-title>
</v-toolbar>
</v-col>


</v-row>


<v-row>
<v-col> 
<template>
<v-simple-table dense>
<template v-slot:default>
<thead>
<tr>
<th class="text-left">Id</th>
<th class="text-left">Item Code</th>
<th class="text-left">Title</th>
<th class="text-left">Image</th>
<th class="text-center">Quantity</th>
<th class="text-left">Unit Price</th>
<th class="text-left">Price</th>
<th class="text-left" v-if="!isReadOnly">Change Product</th>
<th class="text-left" v-if="!isReadOnly">Change Quantity</th>
<th v-if="!isReadOnly">Action</th>
</tr>
</thead>
<tbody>

<tr v-for="(item,index) in editedItem.products" :key="index">
<td>{{ item.order_item_id }}</td>
<td>{{ item.legacy_code_sku }}</td>
<td>{{ item.product_title }}</td>
<td height="25" width="15%"><img style="padding-top:5px;" width="30%" :src="item.product_image" alt=""/></td>
<td width="14%" class="text-center">{{ item.product_quantity }} 

<span :class="get_stock_info(item.stock).class">
  
{{get_stock_info(item.stock).text}}
{{item.stock > 0 ? '(' + item.stock + ')' : '' }}
</span>  
</td>
<td>AED {{ item.product_price }}</td>
<td>AED {{sum_of_product_price(item.product_quantity , item.product_price)}}</td>
<td v-if="!isReadOnly">
<v-select
class="mb-2"
v-model="order_item.id[index]"
:items="order_items"
item-value="id"
item-text="product_title" 
label="Products"
></v-select>
</td>
<td v-if="!isReadOnly">
  <v-text-field type="number" v-model="order_item_quantity[index]"></v-text-field>
 
</td>
<td v-if="!isReadOnly">
<v-btn class="primary accent--text" @click="get_product(index,item.order_item_id,item.id)" fab x-small dark>
<v-icon>mdi-content-save</v-icon>

</v-btn>
</td>
</tr>
</tbody>

</template>
</v-simple-table>
</template>
</v-col>

</v-row>
<v-divider></v-divider>
<v-form ref="form" lazy-validation>

<v-row>
<v-col cols="3">
<v-autocomplete

class="mb-2"
:rules="Rules"
v-if="!isReadOnly"
v-model="editedItem.driver_id" 
:items="drivers"
item-value="id"
item-text="name" 
label="Driver"
></v-autocomplete>
</v-col>
<v-col md="4">
<template>
  <v-row>
    <v-col v-if="!isReadOnly">
      <v-menu
      
        ref="menu"
        v-model="menu"
        :close-on-content-click="false"
        :return-value.sync="date"
        transition="scale-transition"
        offset-y
        min-width="290px"
        
      >
        <template v-slot:activator="{ on }">
          <v-text-field
          :rules="Rules"
            dense
            v-model="date"
            label="Payment Due Date"
            readonly
            v-on="on"
          ></v-text-field>
        </template>
        <v-date-picker color="primary accent--text" v-model="date" no-title scrollable>
          <v-spacer></v-spacer>
          <v-btn text class="primary accent--text" @click="menu = false">Cancel</v-btn>
          <v-btn text class="primary accent--text" @click="$refs.menu.save(date)">OK</v-btn>
        </v-date-picker>
      </v-menu>
    </v-col>
   <v-col v-if="!isReadOnly">
      <v-menu
      
        ref="menu2"
        v-model="menu2"
        :close-on-content-click="false"
        :return-value.sync="date2"
        transition="scale-transition"
        offset-y
        
        min-width="290px"
      >
        <template v-slot:activator="{ on }">
          <v-text-field
          :rules="Rules"
            dense
            v-model="date2"
            label="Delivery Date"
            readonly
            v-on="on"
          ></v-text-field>
        </template>
        <v-date-picker color="primary accent--text" v-model="date2" no-title scrollable>
          <v-spacer></v-spacer>
          <v-btn text class="primary accent--text" @click="menu2 = false">Cancel</v-btn>
          <v-btn text class="primary accent--text"  @click="$refs.menu2.save(date2)">OK</v-btn>
        </v-date-picker>
      </v-menu>
    </v-col>
  
    </v-row>

</template>
</v-col>
<v-simple-table dense style="margin-left:11%; width:30%;">
<tbody>
<tr>
<th>Gross Amount</th>
<td style="border:none;">AED {{ editedItem.order_gross | get_decimal_value }}</td>
</tr>    
<tr>
<th>Discount Amount</th>
<td style="border:none;">AED {{ editedItem.discounted_price | get_decimal_value  }}</td>
</tr>    
<tr>
<th>Tax (VAT %5)</th>
<td style="border:;">AED {{ editedItem.order_tax | get_decimal_value }}</td>
</tr>
<tr>
<th>Grand Total</th>
<th>AED {{editedItem.order_total | get_decimal_value}}  </th>
</tr>


</tbody>
</v-simple-table>

</v-row>
<v-row>
<v-col>
<v-btn v-if="!isReadOnly" class="primary accent--text" text @click="close">Cancel</v-btn>
&nbsp;
<v-btn v-if="!isReadOnly" class="primary accent--text" text @click="save">Save</v-btn>
</v-col>

</v-row>

</v-form>



</v-container>
</v-card-text>

</v-card>
</v-dialog>


</v-toolbar>
</template>
<template v-slot:item.order_total="{ item }">
{{ item.order_total | get_decimal_value}}  
</template>

<template v-slot:item.action="{ item }">
<v-icon
small
class="mr-2"
@click="viewItem(item)"
>
mdi-eye
</v-icon>
<v-icon small class="mr-2" v-if="item.order_status_id == 1 || item.order_status_id == 2" @click="editItem(item)">
mdi-pencil
</v-icon>
</template>

</v-data-table>
</v-app>
</template>
<script>
import VueJsonToCsv from '../../node_modules/vue-json-to-csv'
export default {

  components : {
    VueJsonToCsv
  },

data: () => ({
date: '',
date2: '',
menu: false,
menu2: false,
product_id:'',    
timestamp:'',
status_id:'',
driver_id:'',

order_item:{
id:[]
},
order_item_quantity:[],
options:{
sortBy:['id','order_total','created_at'],
sortDesc:[true]
},
snackbar:false,
action:'',
search:'',
dialog: false,
headers: [


{
text: 'Order #',
align: 'left',
value: 'id',
},
{
text: 'Company Name',
align: 'left',
sortable: false,
value: 'company_name',
},
{
text: 'Contact Person Name',
align: 'left',
sortable: false,
value: 'contact_person_name',
},
{
text: 'Mobile Number',
align: 'left',
sortable: false,
value: 'mobile_number',
},

{
text: 'Customer Class',
align: 'left',
sortable: false,
value: 'customer_category_name',   
},
{
text: 'Order Total',
align: 'left',
value: 'order_total',
},

{
text: 'Order Status',
align: 'left',
sortable: false,
value: 'status',
},
{
text: 'Driver',
align: 'left',
sortable: false,
value: 'driver[0].name',
},
{
text: 'Order DateTime',
align: 'left',
value: 'created_at',
},
{ text: 'Actions', value: 'action', sortable: false },
],

msg:"",
product_list:[],
snackbar:false,
Rules : [
v => !!v || 'This field is required',
],

drivers:[],
order_items:[],
editedIndex: -1,
editedItem: {
products:[],
driver_id:'',
product_id:'',

},
defaultItem: {
products:[],
driver_id:'',
product_id:'',
},
response : {
msg:''
},
orders:[],
states:[],
cities:[],
drivers:[],
customers:[],
statusses:[],
data:[],

change_product:[],
color:'',
}),

computed: {

isReadOnly(){
return this.action == 'View Item'
},

},

watch: {
dialog (val) {
val || this.close()
},
},

created () {
this.initialize()
this.myBtnClass()

},  


filters: {

  get_decimal_value (value) {
  return (Math.round(value * 100) / 100).toFixed(2);
  },

},

methods: {

async searchIt(){


if(this.search){
let  data = await this.$axios.get('search_order/'+this.search,{params:{
'per_page':10,
'sort_by':'desc',
'order_by':'id'}});

this.orders = data.data.orders;
this.data = data.data;
}
},    
get_stock_info (e) {
switch(e) {
case 'Stock does not exist':
return {class:'stock_does_not_exist',text:'Stock does not exist'}
case 0:
return {class:'out_of_stock',text:'out of stock'}
default:
return {class:'in_stock',text:'in stock'}

} 
},    
myBtnClass(name) {
switch(name) {

case 'pending':
return 'warning darken-3'
case 'processing':
return 'primary'
case 'loaded':
return 'teal'
case 'on the way':
return 'blue lighten-1'
case 'delivered':
return 'green lighten-1'
default:
return 'error'

}

}, 


async get_product (i,order_item_id,p_id) {

var payload = {
order_id : this.editedItem.id,
order_item_id : order_item_id,
product_id : this.order_item.id[i] || p_id ,
item_quantity : this.order_item_quantity[i]
};

if(!payload.item_quantity){
this.snackbar = true;
this.color = 'red';
this.response.msg = 'Action Required';

}
else{


await this.$axios.post('change_order_item',payload)
.then(res =>{

this.color = '';
this.snackbar = res.data.response_status;
this.response.msg = res.data.message;
this.editedItem.order_gross = res.data.updated_record.order_gross
this.editedItem.order_tax =   res.data.updated_record.order_tax
this.editedItem.order_total = this.orders[this.editedIndex].order_total = res.data.updated_record.order_total
Object.assign(this.editedItem.products[i],res.data.updated_record.products)

this.order_item.id = []
this.order_items = [];
this.filtered_products(this.editedItem);
})
.catch(error => console.log(error));
}
},



async paginate (e) {

  const orderBy =  this.options.sortBy.length == 0 ? 'id' : this.options.sortBy[0];
  const sortBy =  this.options.sortDesc.length > 0 || this.options.sortDesc[0]    
  ? 'desc' : 'asc';


this.$axios.get('order?page='+e.page,{params:{
'per_page':e.itemsPerPage,
'sort_by':sortBy,
'order_by':orderBy,
'timestamp' : this.timestamp,
'driver' : this.driver_id,
'status' : this.status_id,
'product' : this.product_id
}}).then(res => {
    this.orders = res.data.orders;
    this.data = res.data;
});


},
async initialize () {
  const drivers = await this.$axios.get('driver');
  this.drivers = drivers.data;
},

editItem (item) {

this.filtered_products(item);


this.editedIndex = this.orders.indexOf(item)
this.editedItem = Object.assign({}, item)
this.dialog = true
this.action = 'Edit Item';
},

viewItem (item) {
this.editedIndex = this.orders.indexOf(item)
this.editedItem = Object.assign({}, item)
this.dialog = true
this.action = 'View Item';
},
close () {

this.dialog = false
setTimeout(() => {
this.order_items = [];    
this.editedItem = Object.assign({}, this.defaultItem)
this.order_item.id = [];
this.editedIndex = -1
}, 300)
},

save () {


if(this.$refs.form.validate()){
  const error = this.editedItem.products.filter((v) => {
              return v.stock == 0 || v.stock == 'Stock does not exist' || v.product_quantity > v.stock
  });

if(error.length > 0){
  this.snackbar = true;
  this.color = 'red';
  this.response.msg = 'Kindly adjust your stock';
}

else{


const payload = {
driver_id : this.editedItem.driver_id,
order_id : this.editedItem.id,
scheduling:this.date2,
payment_due_date:this.date,
status_id : 2
};

this.$axios.post('status_change',payload)
.then(res => {
 
  this.color = '';
  this.snackbar = res.data.response_status;
  this.response.msg = res.data.message;

  
  this.editedItem.products = res.data.updated_record.products;
  Object.assign(this.orders[this.editedIndex] , res.data.updated_record);
  setTimeout(() => this.close() ,1000);
})
.catch(error => console.log(error));
}
}


},
filtered_products(item){

this.$axios.get('product').then((res) => {

  res.data.filter(v => {

  if(!item.products.map(v => v.id).includes(v.id)){
    this.order_items.push(v);
  }
  });

  }).catch(error => console.log(error));
},
sum_of_product_price (a,b) {
return (Math.round((a * b) * 100) / 100).toFixed(2)
},

},
}
</script>
<style scoped>

.stock_does_not_exist{
font-size: 11px;
display: block;
background: #fbdede;
color: #cc0000;
border-radius: 20px;
/* width: 120px; */
text-align: center;
}
.out_of_stock{
font-size: 11px;
display: block;
background: #fbdede;
color: #cc0000;
border-radius: 20px;
/* width: 120px; */
text-align: center;
}
.in_stock{
font-size: 11px;
display: block;
background: #ddf9be;
color: #207910;
border-radius: 20px;
text-align: center;
}
</style>