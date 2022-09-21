<template>
	<v-app>
		<div class="text-center ma-2">
			<v-snackbar v-model="snackbar" :top="'top'"> {{response.msg}}
				<v-btn text @click="snackbar = false"> Close </v-btn>
			</v-snackbar>
		</div>
		<v-data-table :headers="headers" :items="orders" class="elevation-1" hide-default-footer>
			<template v-slot:item.status="{ item }">
				<v-chip small class="white--text" :class="myBtnClass(item.status)"> {{(item.status == 'on the way') ? 'Dispatch' : item.status}} </v-chip>
			</template>
			<template v-slot:top>
				<v-toolbar flat class="primary">
					<v-toolbar-title class="accent--text">Recent Orders</v-toolbar-title>
					<v-spacer></v-spacer>
					<VueJsonToCsv 
						:json-data="orders" 
						:labels="{ 
							id:{ title: 'order id' },
							order_total:{ title: 'order total' }, 
							order_tax:{ title: 'order tax' }, 
							order_gross:{ title: 'order gross' }, 
							order_confirmed_date:{ title: 'order confirmed date' }, 
							order_shipped_date:{ title: 'order shipped date' }, 
							order_delivered_date:{ title: 'order delivered date' },  
							discounted_price:{ title: 'discounted price' }, 
							delivery_date:{ title: 'delivery date' },  
							payment_due_date:{ title: 'payment due date' },  
							status:{ title: 'status' },  
							email	:{ title: 'email' },  
							phone_number:{ title: 'phone number' },  
							mobile_number:{ title: 'mobile number' },  
							ntn:{ title: 'ntn' },  
							address:{ title: 'address' },  
							company_name:{ title: 'company name' },  
							trade_name:{ title: 'trade name' },  
							contact_person_name:{ title: 'contact person name' },  
							payment_type:{ title: 'payment type' },  
							delivery_from:{ title: 'delivery from' },
							delivery_to:{ title: 'delivery to' },
							customer_category_name:{ title: 'customer category name' },
							state_name:{ title: 'state name' },
							city_name:{ title: 'city name' }
						}">
						<v-btn small class="mx-2 no_print">
							<v-icon>mdi-file-export</v-icon><b>&nbsp;Export CSV </b> 
						</v-btn>
					</VueJsonToCsv>
					<v-btn small class="mx-2 no_print" to="order">
						<v-icon>mdi-eye</v-icon>&nbsp; View All</v-btn>
					<v-dialog v-model="dialog" max-width="1200px">
						<v-card>
							<v-card-text>
								<v-container>
									<v-row>
										<v-col>
											<v-toolbar class="primary mb-2 accent--text" dark flat>
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
																<td>{{ item.id }}</td>
																<td>{{ item.legacy_code_sku }}</td>
																<td>{{ item.product_title }}</td>
																<td height="25" width="15%">
																		<img style="padding-top:5px;" width="30%" :src="item.product_image" alt="" />
																</td>
																<td width="14%" class="text-center">{{ item.product_quantity }} 
																	<span :class="get_stock_info(item.stock).class">
																		{{get_stock_info(item.stock).text}}
																		{{item.stock > 0 ? '(' + item.stock + ')' : '' }} 
																	</span>
																</td>
																<td>Rs {{ item.product_price }}</td>
																<td>Rs {{sum_of_product_price(item.product_quantity , item.product_price)}}</td>
																<td v-if="!isReadOnly">
																	<v-select class="mb-2" v-model="order_item.id[index]" :items="order_items" item-value="id" item-text="product_title" label="Products"></v-select>
																</td>
																<td v-if="!isReadOnly">
																	<v-text-field type="number" v-model="order_item_quantity[index]"></v-text-field>
																</td>
																<td v-if="!isReadOnly">
																	<v-btn class="primary accent--text" @click="get_product(index,item.order_item_id)" fab x-small dark>
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
												<v-autocomplete class="mb-2" :rules="Rules" v-if="!isReadOnly" v-model="editedItem.driver_id" :items="drivers" item-value="id" item-text="name" label="Driver"></v-autocomplete>
											</v-col>
											<v-col md="4">
												<template>
													<v-row>
														<v-col>
															<v-menu ref="menu" v-model="menu" :close-on-content-click="false" :return-value.sync="date" transition="scale-transition" offset-y min-width="290px">
																<template v-slot:activator="{ on }">
																	<v-text-field :rules="Rules" dense v-model="date" label="Payment Due Date" readonly v-on="on"></v-text-field>
																</template>
																<v-date-picker color="primary" v-model="date" no-title scrollable>
																	<v-spacer></v-spacer>
																	<v-btn text class="primary accent--text" @click="menu = false">Cancel</v-btn>
																	<v-btn text class="primary accent--text" @click="$refs.menu.save(date)">OK</v-btn>
																</v-date-picker>
															</v-menu>
														</v-col>
														<v-col>
															<v-menu ref="menu2" v-model="menu2" :close-on-content-click="false" :return-value.sync="date2" transition="scale-transition" offset-y min-width="290px">
																<template v-slot:activator="{ on }">
																	<v-text-field :rules="Rules" dense v-model="date2" label="Delivery Date" readonly v-on="on"></v-text-field>
																</template>
																<v-date-picker v-model="date2" no-title scrollable>
																	<v-spacer></v-spacer>
																	<v-btn text color="primary" @click="menu2 = false">Cancel</v-btn>
																	<v-btn text color="primary" @click="$refs.menu2.save(date)">OK</v-btn>
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
														<td style="border:none;">AED {{ get_decimal_value(editedItem.order_gross) }}</td>
													</tr>
													<tr>
														<th>Discount Amount</th>
														<td style="border:none;">AED {{ get_decimal_value(editedItem.discounted_price) }}</td>
													</tr>
													<tr>
														<th>Tax (VAT %5)</th>
														<td style="border:;">AED {{ get_decimal_value(editedItem.order_tax) }}</td>
													</tr>
													<tr>
														<th>Grand Total</th>
														<th>AED {{get_decimal_value(editedItem.order_total)}} </th>
													</tr>
												</tbody>
											</v-simple-table>
										</v-row>
										<v-row>
											<v-col>
												<v-btn class="primary accent--text" text @click="close">Cancel</v-btn> &nbsp;
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
			<template v-slot:item.order_total="{ item }"> {{get_decimal_value(item.order_total)}} </template>
			<template v-slot:item.action="{ item }">
				<v-icon small class="mr-2" @click="viewItem(item)"> mdi-eye </v-icon>
				<v-icon v-if="item.order_status_id == 1 || item.order_status_id == 2" small class="mr-2" @click="editItem(item)"> mdi-pencil </v-icon>
			</template>
		</v-data-table>
	</v-app>
</template>
<script>
import Vue from '../../node_modules/vue';
import VueHtmlToPaper from '../../node_modules/vue-html-to-paper';
import VueJsonToCsv from '../../node_modules/vue-json-to-csv'
const options = {
	name: '_blank',
	specs: ['fullscreen=yes', 'titlebar=yes', 'scrollbars=yes'],
}
Vue.use(VueHtmlToPaper, options);
export default {
	components: {
		VueJsonToCsv,
		VueHtmlToPaper
	},
	data: () => ({
		date: '',
		date2: '',
		menu: false,
		menu2: false,
		order_item: {
			id: []
		},
		order_item_quantity: [],
		dialog: false,
		headers: [{
				text: 'Order #',
				align: 'left',
				value: 'id',
				sortable: false
			}, {
				text: 'Customer Name',
				align: 'left',
				value: 'contact_person_name',
				sortable: false
			}, {
				text: 'Mobile Number',
				align: 'left',
				value: 'mobile_number',
				sortable: false
			}, {
				text: 'Order Total',
				align: 'left',
				value: 'order_total',
				sortable: false
			}, {
				text: 'Order Status',
				align: 'left',
				value: 'status',
				sortable: false
			}, {
				text: 'Driver',
				align: 'left',
				sortable: false,
				value: 'driver[0].name',
			}, {
				text: 'Order DateTime',
				align: 'left',
				value: 'created_at',
				sortable: false
			},
			// { text: 'Actions', value: 'action', sortable: false },
		],
		emailRules: [
			v => /.+@.+/.test(v) || 'E-mail must be valid',
		],
		msg: "",
		product_list: [],
		snackbar: false,
		Rules: [
			v => !!v || 'This field is required',
		],
		drivers: [],
		order_items: [],
		editedIndex: -1,
		editedItem: {
			products: [],
			driver_id: '',
			product_id: '',
		},
		defaultItem: {
			products: [],
			driver_id: '',
			product_id: '',
		},
		response: {
			msg: ''
		},
		orders: [],
		states: [],
		cities: [],
		drivers: [],
		customers: [],
		statusses: [],
		data: [],
		change_product: [],
	}),
	computed: {
		isReadOnly() {
				return this.action == 'View Item'
			},
			formTitle() {
				return(this.action) ? this.action : this.editedIndex === -1 ? 'New Item' : 'Edit Item'
			},
	},
	watch: {
		dialog(val) {
			val || this.close()
		},
	},
	created() {
		this.initialize()
		this.myBtnClass()
	},
	methods: {
		print_me() {
				window.print()
			},
			get_cities_by_id() {
				var arr = [];
				this.$axios.get('state/' + this.state_id).then((res) => {
					res.data.map(r => arr.push({
						id: r.c_id,
						city_name: r.c_name
					}));
					this.cities = arr;
				});
			},
			filter_records() {
				this.$axios.get('order', {
					params: {
						'per_page': 10,
						'sort_by': 'desc',
						'order_by': 'id',
						'timestamp': this.timestamp,
						'driver': this.driver_id,
						'status': this.status_id,
						'customer': this.customer_id,
						'state': this.state_id,
						'city': this.city_id,
						'product': this.product_id
					}
				}).then(res => {
					this.orders = res.data.orders;
					this.data = res.data;
				});
			},
			get_stock_info(e) {
				switch(e) {
					case 'Stock does not exist':
						return {
							class: 'stock_does_not_exist',
							text: 'Stock does not exist'
						}
					case 0:
						return {
							class: 'out_of_stock',
							text: 'out of stock'
						}
					default:
						return {
							class: 'in_stock',
							text: 'in stock'
						}
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
			get_product(i, order_item_id) {
				var payload = {
					order_id: this.editedItem.id,
					order_item_id: order_item_id,
					product_id: this.order_item.id[i],
					item_quantity: this.order_item_quantity[i]
				};
				//console.log(payload)
				this.$axios.post('change_order_item', payload).then(res => {
					console.log(res.data);
					this.editedItem.order_gross = this.get_decimal_value(res.data.updated_record.order_gross)
					this.editedItem.order_tax = this.get_decimal_value(res.data.updated_record.order_tax)
					this.editedItem.order_total = this.orders[this.editedIndex].order_total = this.get_decimal_value(res.data.updated_record.order_total)
					Object.assign(this.editedItem.products[i], res.data.updated_record.products)
					this.snackbar = res.data.response_status;
					this.response.msg = res.data.message;
					this.order_item.id[i] = ''
					this.order_item_quantity[i] = ''
				}).catch(error => console.log(error));
			},
			sum_of_product_price(a, b) {
				return this.get_decimal_value(a * b)
			},
			get_decimal_value(value) {
				return(Math.round(value * 100) / 100).toFixed(2);
			},
			async initialize() {
				const drivers = await this.$axios.get('driver');
				this.drivers = drivers.data;
				const order_items = await this.$axios.get('product');
				this.order_items = order_items.data;
				let data = await this.$axios.get('order', {
					params: {
						'per_page': 10,
						'sort_by': 'desc',
						'order_by': 'id'
					}
				});
				this.orders = data.data.orders;
				this.data = data.data;
			},
			editItem(item) {
				this.editedIndex = this.orders.indexOf(item)
				this.editedItem = Object.assign({}, item)
				this.dialog = true
				this.action = 'Edit Item';
			},
			viewItem(item) {
				this.editedIndex = this.orders.indexOf(item)
				this.editedItem = Object.assign({}, item)
				this.dialog = true
				this.action = 'View Item';
			},
			deleteItem(item) {
				confirm('Are you sure you want to delete this item?') && this.$axios.delete('order/' + item.id).then((res) => {
					if(res.data.response_status) {
						const index = this.orders.indexOf(item)
						this.orders.splice(index, 1)
						this.snackbar = res.data.response_status;
						this.response.msg = res.data.message;
					}
				});
			},
			close() {
				this.dialog = false
				setTimeout(() => {
					this.editedItem = Object.assign({}, this.defaultItem)
					this.editedIndex = -1
				}, 300)
			},
			save() {
				const stock_check = this.editedItem.products.filter((v => v.stock == 0 || v.stock == 'Stock does not exist'));
				console.log(stock_check ? true : false);
				if(this.$refs.form.validate() && stock_check == false) {
					const payload = {
						driver_id: this.editedItem.driver_id,
						order_id: this.editedItem.id,
						status_id: 2
					};
					this.$axios.post('status_change', payload).then(res => {
						this.orders[this.editedIndex].status = res.data.updated_record.status
						this.snackbar = res.data.response_status;
						this.response.msg = res.data.message;
						this.close()
					}).catch(error => console.log(error));
				} else {
					this.snackbar = true;
					this.response.msg = 'Kindly adjust your stock';
				}
			},
	},
}
</script>
<style>

.indicator {
	margin-left: -24%;
	padding: 0;
	background: red !important;
	width: 3px;
}

.stock_does_not_exist {
	font-size: 11px;
	display: block;
	background: #fbdede;
	color: #cc0000;
	border-radius: 20px;
	/* width: 120px; */
	text-align: center;
}

.out_of_stock {
	font-size: 11px;
	display: block;
	background: #fbdede;
	color: #cc0000;
	border-radius: 20px;
	/* width: 120px; */
	text-align: center;
}

.in_stock {
	font-size: 11px;
	display: block;
	background: #ddf9be;
	color: #207910;
	border-radius: 20px;
	text-align: center;
}

@media print {
	.no_print {
		display: none;
	}
}
</style>