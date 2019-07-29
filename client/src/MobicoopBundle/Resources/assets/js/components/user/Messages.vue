<template>
  <v-app>
    <v-content>
      <v-container
        text-xs-center
        grid-list-md
        fluid
      >
        <v-layout
          id="headGridMessages"
        >
          <v-flex
            xs4
            class="pt-5 pb-4 mr-1 pl-2 secondary white--text font-weight-bold headline"
          >
            {{ $t("ui.pages.messages.label.messages") }}
          </v-flex>
          <v-flex
            xs5
            text-xs-left
            class="pt-5 pb-4 mr-1 pl-2 secondary white--text font-weight-bold headline"
          >
            {{ currentcorrespondant }}
          </v-flex>
          <v-flex
            xs3
            text-xs-left
            class="pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline"
          >
            {{ $t("ui.pages.messages.label.context") }}
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
                {{ $t("ui.pages.messages.label.ongoingasks") }}
              </v-tab>
              <v-tab-item v-if="this.threadsCM.length==0">
                {{ $t("ui.pages.messages.label.nocarpoolmessages") }}
              </v-tab-item>
              <v-tab-item
                v-else
              >
                <v-card
                  v-for="(threadCM, index) in threadsCM"
                  :key="index"
                  class="threads mx-auto mt-2"
                  :class="threadCM.selected ? 'primary' : ''"
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
                {{ $t("ui.pages.messages.label.directmessages") }}
              </v-tab>

              <v-tab-item v-if="this.threadsDM.length==0">
                {{ $t("ui.pages.messages.label.nodirectmessages") }}
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
                  class="elevation-2 font-weight-bold"
                  :class="(item.origin==='own')?'primary':''"
                >
                  <v-card-text>{{ item.text }}</v-card-text>
                </v-card>
                <span
                  v-if="item.divider===true"
                  class="secondary--text font-weight-bold"
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
                    :label="$t('ui.form.enterMessage')"
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
                      class="mx-2 black--text font-weight-bold"
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
                <v-card class="pa-2">
                  <v-card-text>
                    {{ currentcorrespondant }}
                  </v-card-text>
                  <v-card-text v-if="currentAskHistory">
                    zog zog
                  </v-card-text>
                  <v-card-text v-else>
                    {{ $t("ui.pages.messages.label.notLinkedToACarpool") }}
                  </v-card-text>
                  <v-btn
                    v-if="currentAskHistory && currentAskHistory.ask.status==1 && askUser == userid"
                    rounded
                    color="secondary"
                    class="mb-2"
                    @click="dialogAskCarpool=true"
                  >
                    {{ $t("ui.button.askCarpool") }}
                  </v-btn>
                  <v-card
                    v-else-if="currentAskHistory && currentAskHistory.ask.status==2 && askUser == userid"
                    color="success"
                  >
                    <v-card-text class="white--text">
                      {{ $t("ui.infos.carpooling.askAlreadySent") }}
                    </v-card-text>
                  </v-card>
                  <div
                    v-if="currentAskHistory && currentAskHistory.ask.status==2 && askUser != userid"
                    class="my-2"
                  >
                    <v-tooltip
                      bottom
                      color="primary"
                    >
                      <template v-slot:activator="{ on }">
                        <v-btn
                          color="success"
                          class="mb-2"
                          fab
                          v-on="on"
                        >
                          <v-icon>done</v-icon>
                        </v-btn>
                      </template>
                      <span class="black--text">{{ $t("ui.button.accept") }}</span>
                    </v-tooltip>
                    <v-tooltip
                      bottom
                      color="error"
                    >
                      <template v-slot:activator="{ on }">
                        <v-btn
                          color="error"
                          class="mb-2"
                          fab
                          v-on="on"
                        >
                          <v-icon>clear</v-icon>
                        </v-btn>
                      </template>
                      <span>{{ $t("ui.button.refuse") }}</span>
                    </v-tooltip>
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
            <v-card
              id="spinnerMessages"
              class="secondary"
            >
              <v-card-text class="white--text">
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

        <v-dialog
          v-model="dialogAskCarpool"
          persistent
          max-width="290"
        >
          <v-card>
            <v-card-title class="headline">
              {{ $t("ui.modals.carpooling.askCarpoolTitle") }}
            </v-card-title>
            <v-card-text>{{ $t("ui.modals.carpooling.askCarpoolText") }}</v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn
                class="font-weight-bold"
                color="red darken-1"
                text
                @click="dialogAskCarpool = false"
              >
                {{ $t("ui.button.refuse") }}
              </v-btn>
              <v-btn
                class="font-weight-bold"
                color="green darken-1"
                text
                @click="askCarpool(2)"
              >
                {{ $t("ui.button.accept") }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
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
      dialogAskCarpool:false,
      textToSend:"",
      idThreadMessage:this.idmessagedefault,
      currentcorrespondant:"...",
      idRecipient:null,
      textSpinnerLoading:this.$t('ui.pages.messages.spinner.loading'),
      textSpinnerSendMessage:this.$t('ui.pages.messages.spinner.sendMessage'),
      textSpinnerAskCarpool:this.$t('ui.pages.messages.spinner.askCarpool'),
      textSpinner:"",
      currentAskHistory:null,
      askUser:0
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

          // update askHistory et askUser
          this.currentAskHistory = res.data.lastAskHistory;
          this.askUser = res.data.user.id;

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
    askCarpool(status){
      this.dialogAskCarpool = false;
      this.textSpinner = this.textSpinnerAskCarpool;
      this.spinner = true;
      let params = new FormData();
      params.append("idAsk",this.currentAskHistory.ask.id);
      params.append("status",status);
      axios
        .post("/utilisateur/messages/updateAsk",params)
        .then(res => {
          this.currentAskHistory.ask.status = res.data.status;
          this.spinner = false;
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