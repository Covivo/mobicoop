<template>
  <v-app>
    <v-content>
      <v-container
        text-xs-center
        grid-list-md
        fluid
      >
        <v-layout id="headGridMessages">
          <v-flex
            xs4
            class="pt-5 pb-4 mr-1 pl-2"
          >
            Messages
          </v-flex>
          <v-flex
            xs5
            text-xs-left
            class="pt-5 pb-4 mr-1 pl-2"
          >
            {{ currentcorrespondant }}
          </v-flex>
          <v-flex
            xs3
            text-xs-left
            class="pt-5 pb-4 pl-2"
          >
            Annonces(s)
          </v-flex>
        </v-layout>
        <v-layout>
          <v-flex
            id="threadColumn"
            xs4
          >
            <!-- Threads -->
            <v-tabs
              slider-color="secondary"
              color="secondary"
              grow
            >
              <v-tab
                :key="0"
                ripple
              >
                Demandes en cours
              </v-tab>
              <v-tab-item v-if="this.threadsCM.length==0">
                Aucun message de covoiturage
              </v-tab-item>
              <v-tab-item
                v-else
              >
                <v-card
                  v-for="(threadCM, index) in threadsCM"
                  :key="index"
                  class="threads mx-auto mt-2"
                  :class="threadCM.selected ? 'selected' : ''"
                  max-width="400"
                  @click="updateMessages(threadCM.idThreadMessage,threadCM.contactId)"
                >
                  <v-card-title>
                    <i class="material-icons">
                      account_circle
                    </i>
                    &nbsp;<span class="title font-weight-light">{{ threadCM.contactFirstName }} {{ threadCM.contactLastName.substr(0,1).toUpperCase()+"." }}</span>
                  </v-card-title>
                </v-card>
              </v-tab-item>
              <v-tab
                :key="1"
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
                  class="threads mx-auto mt-2"
                  :class="thread.selected ? 'selected' : ''"
                  max-width="400"
                  @click="updateMessages(thread.idThreadMessage,thread.contactId,generateName(thread.contactFirstName,thread.contactLastName))"
                >
                  <v-card-title>
                    <i class="material-icons">
                      account_circle
                    </i>
                    &nbsp;<span class="title font-weight-light">{{ generateName(thread.contactFirstName,thread.contactLastName) }}</span>
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

            <v-timeline>
              <v-timeline-item
                v-for="(item, i) in items"
                :key="i"
                :fil-dot="item.divider===false"
                :hide-dot="item.divider===true"
                :right="item.origin==='own'"
                :left="item.origin!=='own'"
                :idmessage="item.idMessage"
                :class="(item.divider ? 'divider' : '')+' '+item.origin"
              >
                <template
                  v-if="item.divider===false"
                  v-slot:icon
                >
                  <v-avatar color="secondary">
                    <i
                      class="material-icons"
                    >
                      {{ item.icon }}
                    </i>
                  </v-avatar>
                </template>
                <template
                  v-if="item.divider===false"
                  v-slot:opposite
                >
                  <span>{{ item.createdTimeReadable }}</span>
                </template>
                <v-card
                  v-if="item.divider===false"
                  class="elevation-2"
                >
                  <v-card-text>{{ item.text }}</v-card-text>
                </v-card>
                <span
                  v-if="item.divider===true"
                  class="datesDividers"
                >
                  {{ item.createdDateReadable }}
                </span>
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
                    filled
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
                      class="mx-2 black--text"
                      fab
                      rounded
                      :idthreadmessage="idThreadMessage"
                      color="primary"
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
            <!-- Context -->
            <v-layout>
              <v-flex
                xs12
                text-center
              >
                <v-card>
                  <v-card-text>
                    {{ currentcorrespondant }}
                  </v-card-text>
                  <v-card-text v-if="currentAskHistory">
                    zog zog
                  </v-card-text>
                  <v-card-text v-else>
                    N'est pas lié à un covoiturage
                  </v-card-text>
                  <v-btn
                    v-if="currentAskHistory && currentAskHistory.ask.status==1"
                    rounded
                    color="secondary"
                    class="mb-2"
                  >
                    Demander un covoiturage
                  </v-btn>
                  <div
                    v-if="currentAskHistory && currentAskHistory.ask.status==2"
                    class="my-2"
                  >
                    <v-btn
                      color="success"
                      class="mb-2"
                      fab
                    >
                      <v-icon>done</v-icon>
                    </v-btn>
                    <v-btn
                      color="error"
                      class="mb-2"
                      fab
                    >
                      <v-icon>clear</v-icon>
                    </v-btn>
                  </div>
                </v-card>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>

        <div class="text-xs-center">
          <v-dialog
            v-model="spinner"
            hide-overlay
            persistent
            width="300"
          >
            <v-card id="spinnerMessages">
              <v-card-text>
                {{ textSpinner }}
                <v-progress-linear
                  color="blue accent-4"
                  indeterminate
                  rounded
                  height="6"
                />
              </v-card-text>
            </v-card>
          </v-dialog>
        </div>
      </v-container>
    </v-content>
  </v-app>
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
    },
    firstnamerecipientdefault:{
      type: String,
      default: ""
    },
    lastnamerecipientdefault:{
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
      textSpinner:"",
      currentAskHistory:null
    }
  },
  watch: {
    // whenever question changes, this function will run
    currentAskHistory: function (newCurrentAskHistory, oldCurrentAskHistory) {
      this.updateContextPanel();
    }
  },
  mounted () {
    this.textSpinner = this.textSpinnerLoading;
    this.updateMessages();
  },
  methods: {
    updateMessages(idMessage=this.idmessagedefault,idrecipient=this.idrecipientdefault,contactName = this.generateName(this.firstnamerecipientdefault,this.lastnamerecipientdefault)){
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
          this.items.length = 0; // Reset items (the source of messages column)

          // update askHistory
          this.currentAskHistory = res.data.askHistory;

          // The date of the first message
          let divider = {
            "divider":true,
            "createdDateReadable": res.data.createdDateReadable
          }
          this.addMessageToItems(divider);


          let threadMessage = {
            'id': res.data.id,
            'user': res.data.user,
            'text': res.data.text,
            'createdDateReadable': res.data.createdDateReadable,
            'createdTimeReadable': res.data.createdTimeReadable,
            'divider': false
          };


          this.addMessageToItems(threadMessage);


          // The correspondant for the view
          this.currentcorrespondant = contactName;

          // Id of the current recipient
          this.idRecipient = idrecipient;
          
          let currentDate = res.data.createdDateReadable;
          for (let message of messagesThread) {

            // If the date is different, push a divider
            if(message.createdDateReadable!==currentDate){
              let divider = {
                "divider":true,
                "createdDateReadable": message.createdDateReadable
              }
              currentDate = message.createdDateReadable;
              this.addMessageToItems(divider);
            }

            this.addMessageToItems(message);

          }
          this.spinner = false;
        })
    },
    updateContextPanel(){
      
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

      tabItem["divider"] = (message.divider!==undefined) ? message.divider : false;
      tabItem["createdDateReadable"] = message.createdDateReadable;

      if(!message.divider){
        tabItem["idMessage"] = message.id;
        tabItem["userFirstName"] = message.user.givenName;
        tabItem["userLastName"] = message.user.familyName.substr(0,1).toUpperCase()+".";
        tabItem["icon"] = "account_circle";
        tabItem["text"] = message.text;
        tabItem["createdTimeReadable"] = message.createdTimeReadable;
        (message.user.id==this.userid) ? tabItem["origin"] = "own" : tabItem["origin"] = "contact";
      }

      this.items.push(tabItem);

    },
    generateName(firstname,lastname){
      return firstname+' '+lastname.substr(0,1).toUpperCase()+'.'
    },
  }
}
</script>