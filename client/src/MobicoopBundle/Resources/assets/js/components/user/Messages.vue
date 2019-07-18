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
        <v-tabs
          slider-color="yellow"
        >
          <v-tab
            ripple
          >
            Direct
          </v-tab>
          <v-tab-item v-if="this.threadsDM.length==0">
            Aucun message direct
          </v-tab-item>
          <v-tab-item v-else>
            <v-card
              v-for="(thread, index) in threadsDM"
              :key="index"
              class="threads mx-auto"
              :class="thread.selected ? 'selected' : ''"
              max-width="400"
              dark
              @click="updateMessages(thread.idFirstMessage,index,thread.contactId)"
            >
              <v-card-title>
                <i class="material-icons">
                  account_circle
                </i>
                &nbsp;<span class="title font-weight-light white--text">{{ thread.contactFirstName }} {{ thread.contactLastName }}</span>
              </v-card-title>
            </v-card>
          </v-tab-item>
          <v-tab
            ripple
          >
            Covoiturage
          </v-tab>
          <v-tab-item v-if="this.threadsCM.length==0">
            Aucun message de covoiturage
          </v-tab-item>
          <v-tab-item v-else>
            <v-card
              v-for="(threadCM, index) in threadsCM"
              :key="index"
              class="threads mx-auto"
              :class="threadCM.selected ? 'selected' : ''"
              max-width="400"
              dark
              @click="updateMessages(threadCM.idFirstMessage,index,threadCM.contactId)"
            >
              <v-card-title>
                <i class="material-icons">
                  account_circle
                </i>
                &nbsp;<span class="title font-weight-light white--text">{{ threadCM.contactFirstName }} {{ threadCM.contactLastName }}</span>
              </v-card-title>
            </v-card>
          </v-tab-item>
        </v-tabs>
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
            :idmessage="item.idMessage"
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
                v-model="textToSend"
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
                  id="validSendMessage"
                  round
                  :idlastmessage="idLastMessage"
                  @click="sendInternalMessage()"
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
            {{ textSpinner }}
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
    threadsdirectmessagesforview: {
      type: Array,
      default: function(){return []}
    },
    threadscarpoolingmessagesforview: {
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
    },
    idrecipientdefault:{
      type: String,
      default: ""
    }
  },
  data() {
    return {
      items: [],
      threadsDM: this.threadsdirectmessagesforview,
      threadsCM: this.threadscarpoolingmessagesforview,
      spinner:false,
      textToSend:"",
      idLastMessage:-1,
      idRecipient:null,
      textSpinnerLoading:"Chargement des messages",
      textSpinnerSendMessage:"Envoi...",
      textSpinner:""
    }
  },
  mounted () {
    this.textSpinner = this.textSpinnerLoading;
    this.updateMessages();
  },
  methods: {
    updateMessages(idMessage=this.idmessagedefault,idThreadSelected=0,idrecipient=this.idrecipientdefault){
      this.threadsDM.forEach((thread, index) =>{
        this.threadsDM[index].selected = (index === idThreadSelected) ? true : false;
      });
      this.threadsCM.forEach((thread, index) =>{
        this.threadsCM[index].selected = (index === idThreadSelected) ? true : false;
      });
      this.textSpinner = this.textSpinnerLoading;
      this.spinner = true;
      axios
        .get("/utilisateur/messages/"+idMessage)
        .then(res => {
          let messagesThread = (res.data);
          this.items.length = 0;
          for (let message of messagesThread) {
            let tabItem = new Array();

            // All messages of the current thread
            tabItem["idMessage"] = message.id;
            tabItem["userFirstName"] = message.user.givenName;
            tabItem["userLastName"] = message.user.familyName.substr(0,1).toUpperCase()+".";
            tabItem["icon"] = "account_circle";
            tabItem["text"] = message.text;
            (message.user.id==this.userid) ? tabItem["origin"] = "own" : tabItem["origin"] = "contact";
            this.items.push(tabItem);

            // Id of the last message to be send for a message post
            (this.idLastMessage<message.id) ? this.idLastMessage=message.id : "";

            // Id of the current recipient
            this.idRecipient = idrecipient;

            this.spinner = false;
          }
        })
    },
    sendInternalMessage(){
      let messageToSend = new FormData();
      messageToSend.append("idLastMessage",this.idLastMessage);
      messageToSend.append("text",this.textToSend);
      messageToSend.append("idRecipient",this.idRecipient);
      this.textSpinner = this.textSpinnerSendMessage;
      this.spinner = true;
      axios
        .post("/utilisateur/messages/envoyer",messageToSend)
        .then(res => {
          console.error(res.data);
          this.spinner = false;
          this.updateMessages();
        });
    }
  }
}
</script>