<template>
  <v-content>
    <v-container
      text-xs-center
      grid-list-md
      fluid
    >
      <v-row id="headGridMessages">
        <v-col class="col-3 pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline">
          <mail-box-header>{{ $t("headers.messages") }}</mail-box-header>
        </v-col>
        <v-col
          text-xs-left
          class="col-5 pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline"
        >
          <mail-box-header>{{ recipientName }}</mail-box-header>
        </v-col>
        <v-col
          text-xs-left
          class="col-4 pt-5 pb-4 pl-2 mr-0 secondary white--text font-weight-bold headline"
        >
          <mail-box-header>{{ $t("headers.context") }}</mail-box-header>
        </v-col>
      </v-row>
      <v-row>
        <v-col
          class="col-3"
        >
          <v-tabs
            v-model="modelTabs"
            slider-color="secondary"
            color="secondary"
            class="pa-0"
            grow
          >
            <v-tab
              :key="0"
              href="#tab-cm"
              class="ma-0"
              ripple
            >
              <v-icon class="display-1">
                mdi-car
              </v-icon>
            </v-tab>
            <v-tab
              :key="1"
              href="#tab-dm"
              class="ma-0"
              ripple
            >
              <v-icon class="display-1">
                mdi-chat
              </v-icon>
            </v-tab>
          </v-tabs>
          <v-tabs-items v-model="modelTabs">
            <v-tab-item value="tab-cm">
              <threads-carpool
                :new-thread="newThreadCarpool"
                :id-thread-default="idThreadDefault"
                :id-message-to-select="idMessage"
                :refresh-threads="refreshThreadsCarpool"
                @idMessageForTimeLine="updateDetails"
                @toggleSelected="refreshSelected"
                @refreshThreadsCarpoolCompleted="refreshThreadsCarpoolCompleted"
              />
            </v-tab-item>
            <v-tab-item value="tab-dm">
              <threads-direct
                :new-thread="newThreadDirect"
                :id-thread-default="idThreadDefault"
                :id-message-to-select="idMessage"
                :refresh-threads="refreshThreadsCarpool"
                @idMessageForTimeLine="updateDetails"
                @toggleSelected="refreshSelected"
                @refreshThreadsDirectCompleted="refreshThreadsDirectCompleted"
              />
            </v-tab-item>
          </v-tabs-items>
        </v-col>
        <v-col
          class="col-5"
        >
          <v-row>
            <v-col cols="12">
              <thread-details
                :id-message="idMessage"
                :id-user="idUser"
                :refresh="refreshDetails"
                @refreshCompleted="refreshDetailsCompleted"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col
              v-if="idMessage"
              cols="12"
            >
              <type-text
                ref="typeText"
                :id-thread-message="idMessage"
                :id-recipient="idRecipient"
                :loading="loadingTypeText"
                @sendInternalMessage="sendInternalMessage"
              />
            </v-col>
          </v-row>
        </v-col>
        <v-col
          class="col-4"
        >
          <thread-actions
            :id-ask="currentIdAsk"
            :id-user="idUser"
            :id-recipient="idRecipient"
            :loading-init="loadingDetails"
            :refresh="refreshActions"
            @refreshActionsCompleted="refreshActionsCompleted"
            @updateStatusAskHistory="updateStatusAskHistory"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>
import axios from "axios";
import Translations from "@translations/components/user/mailbox/Messages.json";
import MailBoxHeader from '@components/user/mailbox/MailBoxHeader'
import ThreadsDirect from '@components/user/mailbox/ThreadsDirect'
import ThreadsCarpool from '@components/user/mailbox/ThreadsCarpool'
import ThreadDetails from '@components/user/mailbox/ThreadDetails'
import ThreadActions from '@components/user/mailbox/ThreadActions'
import TypeText from '@components/user/mailbox/TypeText'

export default {
  i18n: {
    messages: Translations,
  },
  components: {
    MailBoxHeader,
    ThreadsDirect,
    ThreadsCarpool,
    ThreadDetails,
    ThreadActions,
    TypeText
  },
  props: {
    idUser:{
      type: Number,
      default:null
    },
    idThreadDefault:{
      type: Number,
      default:null
    },
    newThread:{
      type:Object,
      default:null
    },
  },
  data() {
    return {
      modelTabs:"tab-cm",
      idMessage:null,
      idRecipient:null,
      currentIdAsk:null,
      recipientName:"",
      newThreadDirect:null,
      newThreadCarpool:null,
      loadingTypeText:false,
      refreshDetails:false,
      refreshThreadsDirect:false,
      refreshThreadsCarpool:false,
      refreshActions:false,
      loadingDetails:false
    };
  },
  mounted() {
    // If there is a new thread we give it to te right component
    if(this.newThread){
      if(this.newThread.carpool){
        this.newThreadCarpool = this.newThread
        this.modelTabs="tab-cm";
      }
      else{
        this.newThreadDirect = this.newThread;
        this.modelTabs="tab-dm";
      }
      
    }
  },
  methods: {
    updateDetails(data){
      (data.type=="Carpool") ? this.currentIdAsk = data.idAsk : this.currentIdAsk = null;
      this.idMessage = data.idMessage;
      this.idRecipient = data.idRecipient;
      this.recipientName = data.name;
    },
    sendInternalMessage(data){
      this.loadingTypeText = true;
      let messageToSend = {
        idThreadMessage: data.idThreadMessage,
        text: data.textToSend,
        idRecipient: data.idRecipient,
        idAsk: this.currentIdAsk
      };
      axios.post(this.$t("urlSend"), messageToSend).then(res => {
        this.idMessage = (data.idThreadMessage!==-1) ? data.idThreadMessage : res.data.id ;
        this.loadingTypeText = false;
        // Update the threads list
        (this.currentIdAskHistory) ? this.refreshThreadsCarpool = true : this.refreshThreadsDirect = true;
        // We need to delete new thread data or we'll have two identical entries
        this.refreshDetails = true;
        this.newThreadDirect = null;
        this.newThreadCarpool = null;
        this.refreshSelected({'idMessage':this.idMessage});
      });
    },
    updateStatusAskHistory(data){
      let params = {
        idAsk:this.currentIdAsk,
        status:data.status
      }
      axios.post(this.$t("urlUpdateAsk"),params)
        .then(response => {
          //console.error(response.data);
          this.refreshActions = true;
        })
        .catch(function (error) {
          console.error(error);
        });
    },
    refreshSelected(data){
      this.loadingDetails = true;
      this.idMessage = data.idMessage;
    },
    refreshDetailsCompleted(){
      this.refreshActions = true;
      this.refreshDetails = false;
    },
    refreshThreadsDirectCompleted(){
      this.refreshThreadsDirect = false;
    },
    refreshThreadsCarpoolCompleted(){
      this.refreshThreadsCarpool = false;
    },
    refreshActionsCompleted(){
      this.loadingDetails = false;
      this.refreshActions = false;
    }
  }
};
</script>
<style lang="scss">
.v-content__wrap{
  #headGridMessages{
    .col{
      border-left: 2px solid white !important;
    }
  }
}
</style>
