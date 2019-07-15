<template>
  <v-container
    text-xs-center
    fill-height
    grid-list-md
  >
    <v-layout
      fill-height
    >
      <v-flex
        id="threadColumn"
        xs3
      >
        <!-- Threads -->



        <v-card
          v-for="(thread, index) in threads"
          :key="index"
          class="threads mx-auto"
          :class="thread.selected ? 'selected' : ''"
          max-width="400"
          dark
          @click="updateMessages(thread.idFirstMessage,index)"
        >
          <v-card-title>
            <i class="material-icons">
              account_circle
            </i>
            &nbsp;<span class="title font-weight-light white--text">{{ thread.contactFirstName }} {{ thread.contactLastName }}</span>
          </v-card-title>
        </v-card>
      </v-flex>




      <v-flex
        id="messagesColumn"
        xs6
      >
        <!-- Messages -->

        <v-timeline align-top>
          <v-timeline-item
            v-for="(item, i) in items"
            :key="i"
            fil-dot
            :right="item.origin==='own'"
            :left="item.origin!=='own'"
          >
            <template v-slot:icon>
              <v-avatar>
                <i class="material-icons">
                  {{ item.icon }}
                </i>
              </v-avatar>
            </template>
            <template v-slot:opposite>
              <span>{{ item.userFirstName }} {{ item.userLastName }}</span>
            </template>
            <v-card class="elevation-2">
              <v-card-text>{{ item.text }}</v-card-text>
            </v-card>
          </v-timeline-item>
        </v-timeline>   

        <v-container
          fluid
          grid-list-md
        >
          <v-layout
            row
            wrap
          >
            <v-flex xs10>
              <v-textarea
                name="typedMessage"
                box
                label="Saisissez un message"
                auto-grow
                rows="2"
                background-color="#FFFFFF"
                value=""
              />
            </v-flex>
            <v-flex
              xs2
              align-self-center
            >
              <div class="text-xs-center">
                <v-btn
                  round
                  color="primary"
                  dark
                >
                  Envoyer
                </v-btn>
              </div>            
            </v-flex>
          </v-layout>
        </v-container>
      </v-flex>
      <v-flex
        id="contextColumn"
        xs3
      >
        droite
      </v-flex>
    </v-layout>

    <div class="text-xs-center">
      <v-dialog
        id="spinnerMessages"
        v-model="spinner"
        hide-overlay
        persistent
        width="300"
      >
        <v-card>
          <v-card-text>
            Chargement des messages
            <v-progress-linear
              indeterminate
              color="white"
              class="mb-0"
            />
          </v-card-text>
        </v-card>
      </v-dialog>
    </div>
  </v-container>
</template>
<script>
import axios from 'axios';

export default {
  props: {
    threadsforview: {
      type: Array,
      default: function(){return []}
    },
    userid:{
      type: String,
      default: ""
    },
    idmessagedefault:{
      type: String,
      default: ""
    }
  },
  data() {
    return {
      items: [],
      threads: this.threadsforview,
      spinner:false
    }
  },
  mounted () {
    this.updateMessages()
  },
  methods: {
    updateMessages(idMessage=this.idmessagedefault,idThreadSelected=0){
      this.threads.forEach((thread, index) =>{
        this.threads[index].selected = (index === idThreadSelected) ? true : false;
      });
      this.spinner = true;
      axios
        .get("/utilisateur/messages/"+idMessage)
        .then(res => {
          let messagesThread = (res.data);
          this.items.length = 0;
          for (let message of messagesThread) {
            let tabItem = new Array();
            tabItem["userFirstName"] = message.user.givenName;
            tabItem["userLastName"] = message.user.familyName.substr(0,1).toUpperCase()+".";
            tabItem["icon"] = "account_circle";
            tabItem["text"] = message.text;
            (message.user.id==this.userid) ? tabItem["origin"] = "own" : tabItem["origin"] = "contact";
            this.items.push(tabItem);
            this.spinner = false;
          }
        })
    }
  }
}
</script>