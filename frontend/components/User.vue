<template>
  <v-data-table
    :headers="headers"
    :items="data"
    class="elevation-1"
  >
    <template v-slot:top>
      <v-toolbar
        flat
      >
        <v-toolbar-title>{{Entity}} List</v-toolbar-title>
        <v-divider
          class="mx-4"
          inset
          vertical
        ></v-divider>
        <v-spacer></v-spacer>
        <v-dialog
          v-model="dialog"
          max-width="800px"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              small
              color="primary"
              dark
              class="mb-2"
              v-bind="attrs"
              v-on="on"
            >
              New {{Entity}}
            </v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ formTitle }} {{Entity}}</span>
            </v-card-title>

            <v-card-text>
              <v-container>
                <v-row>
                  
                  <v-col
                    cols="12"
                    sm="12"
                    md="6"
                  >
                    <v-text-field
                      v-model="editedItem.name"
                      :label="labels.name"
                    ></v-text-field>
                  </v-col>

                   <v-col
                    cols="12"
                    sm="12"
                    md="6"
                  >
                    <v-text-field
                    type="email"
                      v-model="editedItem.email"
                      :label="labels.email"
                    ></v-text-field>
                  </v-col>

                  <v-col
                    cols="12"
                    sm="12"
                    md="6"
                  >
                    <v-text-field
                    type="password"
                      v-model="editedItem.password"
                      :label="labels.password"
                    ></v-text-field>
                  </v-col>
                  <v-col
                    cols="12"
                    sm="12"
                    md="6"
                  >
                    <v-text-field
                    type="password"
                      v-model="editedItem.password_confirmation"
                      :label="labels.password_confirmation"
                    ></v-text-field>
                  </v-col>

                

                  <v-col
                    cols="12"
                    sm="12"
                    md="12"
                  >

                  <v-autocomplete
                  label="Select Role"
                  v-model="editedItem.role_id"
                  :items="roles"
                  item-text="role"
                  item-value="id"
                  >
                  </v-autocomplete>
                  
                  </v-col>

                  <v-col>

                    <ul v-for="(e , i) in errors" :key="i">
                      <li class="red--text">
                          {{e}}
                      </li>
                    </ul>
                  </v-col>


                   
                </v-row>
              </v-container>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="red darken-1"
                dark
                small
                @click="close"
              >
                Cancel
              </v-btn>
              <v-btn
                color="blue darken-1"
                dark
                small
                @click="save"
              >
                Save
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogDelete" max-width="500px" >
          <v-card>

            <v-card-title class="text-h5">Are you sure you want to delete this item?</v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" small dark @click="closeDelete">Cancel</v-btn>
              <v-btn color="red darken-1" small dark @click="deleteItemConfirm">OK</v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>

     <template v-slot:item.role="{ item }">
     
       <v-chip v-if="item.role && item.role.role" class="ma-1 primary">

         {{item.role.role}}

       </v-chip>

       <v-chip v-else dark>No Role</v-chip>
     </template>
    

    <template v-slot:item.actions="{ item }">
     
      <v-btn mall text class="info--text" @click="editItem(item)">
        <v-icon
        x-small
        class="mr-2"
       >
        mdi-pencil
      </v-icon>
      Edit
      </v-btn>
      <v-btn small text class="error--text"   @click="deleteItem(item)">
        <v-icon
        color="error"
        small
        >
        mdi-delete
        </v-icon>
      delete</v-btn>
    </template>
    <template v-slot:no-data>
      <v-btn
        color="primary"
        @click="initialize"
      >
        Reset
      </v-btn>
    </template>
  </v-data-table>
</template>

<script>
  export default {
    data: () => ({
      

      dialog: false,
      dialogDelete: false,
      Entity : 'User',
      model : 'user',

      labels : {
        name : 'Name',
        email : 'Email',
        password : 'Password',
        password_confirmation : 'Password Confirmation',
        role_id : 'Select Role'

      },

      headers: [
       
        { text: 'Name', value: 'name' },
        { text: 'Email', value: 'email' },
        { text: 'Role', value: 'role' },
        { text: 'Actions', value: 'actions', sortable: false },
      ],
      data: [],
      roles : [],
      errors : [],
      editedIndex: -1,
      
      editedItem: {
        name : '',
        email : '',
        password : '',
        password_confirmation : '',
        role_id : 0
      },
      defaultItem: {
        name : '',
        email : '',
        password : '',
        password_confirmation : '',
        role_id : 0
      },
    }),

    computed: {
      formTitle () {
        return this.editedIndex === -1 ? 'New' : 'Edit'
      },
    },

    watch: {
      dialog (val) {

        val || this.close() , this.errors = []

      },
      dialogDelete (val) {
        val || this.closeDelete()
      },
    },

    created () {
      this.initialize()
    },

    methods: {
      initialize () {

        this.$axios.get(`/${this.model}`).then(res => this.data = res.data);
        this.$axios.get(`/role`).then(res => this.roles = res.data);


      },

      editItem (item) {


        this.editedIndex = this.data.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialog = true
      },

      deleteItem (item) {
        
        this.editedIndex = this.data.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialogDelete = true
      },

      deleteItemConfirm () {

        this.$axios.delete(`/${this.model}/${this.editedItem.id}`)
            .then(res => {
              this.data.splice(this.editedIndex, 1)
              this.closeDelete()
                 this.$swal.fire(
                    res.data.message,
                    '',
                    'error'
                    )
            });


        
      },

      close () {
        this.dialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      closeDelete () {
        this.dialogDelete = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

     
      save () {
        if (this.editedIndex > -1) {
          
            this.$axios.put(`/${this.model}/${this.editedItem.id}`,this.editedItem)
              .then(res => {

                console.log(res.data);

                if(res.data.status){
                  Object.assign(this.data[this.editedIndex], res.data.payload)
                  this.errors = []
                  this.close()
                   this.$swal.fire(
                        res.data.message,
                        '',
                        'success'
                     
                    )
                   
                }
                else{
                  this.errors = res.data.errors;
                }

             });

          
        } else {

          this.$axios.post(`/${this.model}`,this.editedItem)
          .then(res => {

            if(res.data.status){

              this.data.push(res.data.payload)
              this.errors = []
              this.close()
               this.$swal.fire(
                    res.data.message,
                    '',
                    'success'
                    )
            }
            else{

              console.log(res.data);
              this.errors = res.data.messages;
            }
        
             
          })
          .catch(e => console.log(e));



          
        }
       
      },
    },
  }
</script>