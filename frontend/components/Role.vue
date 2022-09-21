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
          max-width="500px"
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
                    md="12"
                  >
                    <v-text-field
                      v-model="editedItem.role"
                      :label="labels.role"
                    ></v-text-field>
                  </v-col>

                   <v-col
                    cols="12"
                    sm="12"
                    md="12"
                  >
                    <v-text-field
                      v-model="editedItem.role_slug"
                      :label="labels.role_slug"
                    ></v-text-field>
                  </v-col>

                  <v-col
                    cols="12"
                    sm="12"
                    md="12"
                  >
                   <v-row>
                     <v-col cols="2"> 


                      <v-checkbox
                    dense
                    v-model="editedItem.permissions"
                    label="Add"
                    value= 'add'
                    ></v-checkbox>

                    </v-col>
                    <v-col cols="2">
                    <v-checkbox
                    dense
                    v-model="editedItem.permissions"
                    label="Edit"
                    value= 'edit'
                    ></v-checkbox>
                    </v-col>

                    <v-col cols="2">
                    <v-checkbox
                    dense
                    v-model="editedItem.permissions"
                    label="View"
                    value= 'view'
                    ></v-checkbox>
                    </v-col>

                    <v-col cols="2">
                    <v-checkbox
                    dense
                    v-model="editedItem.permissions"
                    label="Delete"
                    value= 'delete'
                    ></v-checkbox>
                    </v-col>
                   </v-row>

                    
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

     <template v-slot:item.permissions="{ item }">
     
       <v-chip class="ma-1 primary" v-for="(item,index) in item.permissions" :key="index">

         {{item.permission}}

       </v-chip>
     </template>
    

 <template v-slot:item.actions="{ item }">
     
      <v-btn mall text class="info--text" @click="editItem(item)">
        <v-icon
        x-small
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
      Entity : 'Role',
      model : 'role',

      labels : {
        role : 'Role',
        role_slug : 'Slug',
      },

      headers: [
       
        { text: 'Role', value: 'role' },
        { text: 'Slug', value: 'role_slug' },
        { text: 'Permissions', value: 'permissions' },
        { text: 'Actions', value: 'actions', sortable: false },
      ],
      data: [],
      errors : [],
      editedIndex: -1,
      
      editedItem: {
        role: '',
        role_slug: '',
        permissions : [],
      },
      defaultItem: {
        role: '',
        role_slug: '',
        permissions : [],
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

      },

      editItem (item) {


        this.editedIndex = this.data.indexOf(item)
        this.editedItem = Object.assign({}, item)

        let arr = item.permissions.map((e) => e.permission)
        this.editedItem.permissions = arr
       
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