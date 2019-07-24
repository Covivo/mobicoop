<template>
  <v-container
    text-xs-center
    grid-list-md
    fluid
  >
    <v-layout id="headGridMessages">
      <v-flex
        xs4
        pt-5
        pb-4
        mr-1
        pl-2
      >
        Messages
      </v-flex>
      <v-flex
        xs5
        text-xs-left
        pt-5
        pb-4
        mr-1
        pl-2
      >
        {{ currentcorrespondant }}
      </v-flex>
      <v-flex
        xs3
        text-xs-left
        pt-5
        pb-4
        pl-2
      >
        Annonces(s)
      </v-flex>
    </v-layout>
    <v-layout
      fill-height
    >
      <v-flex
        id="threadColumn"
        xs4
      >
        <!-- Threads -->
        <v-tabs
          slider-color="yellow"
        >
          <v-tab
            ripple
          >
            Demandes en cours
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
              @click="updateMessages(threadCM.idThreadMessage,threadCM.contactId)"
            >
              <v-card-title>
                <i class="material-icons">
                  account_circle
                </i>
                &nbsp;<span class="title font-weight-light white--text">{{ threadCM.contactFirstName }} {{ threadCM.contactLastName.substr(0,1).toUpperCase()+"." }}</span>
              </v-card-title>
            </v-card>
          </v-tab-item>
          <v-tab
            ripple
          >
            Boîte de dialogue
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
              @click="updateMessages(thread.idThreadMessage,thread.contactId)"
            >
              <v-card-title>
                <i class="material-icons">
                  account_circle
                </i>
                &nbsp;<span class="title font-weight-light white--text">{{ thread.contactFirstName }} {{ thread.contactLastName.substr(0,1).toUpperCase()+"." }}</span>
              </v-card-title>
            </v-card>
          </v-tab-item>
        </v-tabs>
      </v-flex>




      <v-flex
        id="messagesColumn"
        xs5
      >
        <!-- Messages -->

        <v-timeline
          align-top
        >
          <v-timeline-item
            v-for="(item, i) in items"
            :key="i"
            fil-dot
            :right="item.origin==='own'"
            :left="item.origin!=='own'"
            :idmessage="item.idMessage"
          >
            <v-subheader
              v-if="item.header"
              :key="item.header"
            >
              {{ item.header }}
            </v-subheader>
            <v-divider
              v-else-if="item.divider"
              :key="index"
              :inset="item.inset"
            />
            <template v-slot:icon>
              <v-avatar>
                <i class="material-icons">
                  {{ item.icon }}
                </i>
              </v-avatar>
            </template>
            <template v-slot:opposite>
              <span>{{ item.createdTimeReadable }}</span>
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
                  class="mx-2"
                  fab
                  round
                  :idthreadmessage="idThreadMessage"
                  @click="sendInternalMessage()"
                >
                  <v-icon>
                    send
                  </v-icon>
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
      idThreadMessage:this.idmessagedefault,
      currentcorrespondant:"...",
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
    updateMessages(idMessage=this.idmessagedefault,idrecipient=this.idrecipientdefault){
      this.threadsDM.forEach((thread,index) =>{
        this.threadsDM[index].selected = (thread.idThreadMessage === parseInt(idMessage)) ? true : false;
      });
      this.threadsCM.forEach((thread, index) =>{
        this.threadsCM[index].selected = (thread.idThreadMessage === parseInt(idMessage)) ? true : false;
      });
      this.textSpinner = this.textSpinnerLoading;
      this.spinner = true;
      this.idThreadMessage = idMessage;
      axios
        .get("/utilisateur/messages/"+idMessage)
        .then(res => {
          let messagesThread = (res.data.messages);
          this.items.length = 0;
          let threadMessage = {
            'id': res.data.id,
            'user': res.data.user,
            'text': res.data.text,
            'createdDateReadable': res.data.createdDateReadable,
            'createdTimeReadable': res.data.createdTimeReadable
          };

          this.addMessageToItems(threadMessage);
          this.currentcorrespondant = threadMessage.user.givenName+" "+threadMessage.user.familyName.substr(0,1).toUpperCase()+".";

          // Id of the current recipient
          this.idRecipient = idrecipient;
          
          for (let message of messagesThread) {
            this.addMessageToItems(message);
          }
          this.spinner = false;
        })
    },
    sendInternalMessage(){
      let messageToSend = new FormData();
      messageToSend.append("idThreadMessage",this.idThreadMessage);
      messageToSend.append("text",this.textToSend);
      messageToSend.append("idRecipient",this.idRecipient);
      this.textSpinner = this.textSpinnerSendMessage;
      this.spinner = true;
      axios
        .post("/utilisateur/messages/envoyer",messageToSend)
        .then(res => {
          this.textToSend = "";
          this.spinner = false;
          this.updateMessages(res.data.message.id);
        });
    },
    addMessageToItems(message){
      let tabItem = new Array();
      tabItem["idMessage"] = message.id;
      tabItem["userFirstName"] = message.user.givenName;
      tabItem["userLastName"] = message.user.familyName.substr(0,1).toUpperCase()+".";
      tabItem["icon"] = "account_circle";
      tabItem["text"] = message.text;
      tabItem["createdDateReadable"] = message.createdDateReadable;
      tabItem["createdTimeReadable"] = message.createdTimeReadable;
      (message.user.id==this.userid) ? tabItem["origin"] = "own" : tabItem["origin"] = "contact";
      this.items.push(tabItem);
    }
  }
}
</script>