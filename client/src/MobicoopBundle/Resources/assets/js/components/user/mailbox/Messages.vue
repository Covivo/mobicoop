<template>
  <v-content>
    <v-container
      text-xs-center
      grid-list-md
      fluid
    >
      <v-row 
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <h1>{{ $t('ui.pages.title.messages') }}</h1>
        </v-col>
      </v-row>
      <v-row id="headGridMessages">
        <v-col class="col-4 pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline">
          <mail-box-header>{{ $t("headers.messages") }}</mail-box-header>
        </v-col>
        <v-col
          text-xs-left
          class="col-5 pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline"
        >
          <mail-box-header>{{ this.recipientName }}</mail-box-header>
        </v-col>
        <v-col
          text-xs-left
          class="col-3 pt-5 pb-4 pl-2 mr-0 secondary white--text font-weight-bold headline"
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
                ref="threadsCarpool"
                @idMessageForTimeLine="updateDetails"
                @toggleSelected="refreshSelected"
              />
            </v-tab-item>
            <v-tab-item value="tab-dm">
              <threads-direct
                ref="threadsDirect"
                @idMessageForTimeLine="updateDetails"
                @toggleSelected="refreshSelected"
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
                ref="threadDetails"
                :id-message="this.idMessage"
                :id-user="this.idUser"
                @updateAskHistory="updateAskHistory"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col
              v-if="this.idMessage"
              cols="12"
            >
              <type-text
                ref="typeText"
                :id-thread-message="this.idMessage"
                :id-recipient="this.idRecipient"
                @sendInternalMessage="sendInternalMessage"
              />
            </v-col>
          </v-row>
        </v-col>
        <v-col
          class="col-4"
        >
          <thread-actions
            :id-ask-history="this.currentIdAskHistory"
            :recipient-name="this.recipientName"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>
import axios from "axios";
import CommonTranslations from "@translations/translations.json";
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
    sharedMessages: CommonTranslations
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
    }
  },
  data() {
    return {
      modelTabs:"tab-cm",
      idMessage:null,
      idRecipient:null,
      currentIdAskHistory:null,
      recipientName:""
    };
  },
  watch: {
  },
  mounted() {
  },
  methods: {
    updateDetails(data){
      this.idMessage = data.idMessage;
      this.idRecipient = data.idRecipient;
      this.recipientName = data.name;
    },
    sendInternalMessage(data){
      this.$refs.typeText.updateLoading(true);
      let messageToSend = {
        idThreadMessage: data.idThreadMessage,
        text: data.textToSend,
        idRecipient: data.idRecipient,
        idAskHistory: this.currentIdAskHistory
      };
      axios.post(this.$t("urlSend"), messageToSend).then(res => {
        this.idMessage = data.idThreadMessage;
        // Update the thread details
        this.$refs.threadDetails.getCompleteThread();
        this.$refs.typeText.updateLoading(false);
      });
    },
    updateAskHistory(data){
      this.currentIdAskHistory = data.currentAskHistory;
    },
    refreshSelected(data){
      if(this.$refs.threadsDirect !== undefined){this.$refs.threadsDirect.refreshSelected(data.idMessage);}
      if(this.$refs.threadsCarpool !== undefined){this.$refs.threadsCarpool.refreshSelected(data.idMessage)};
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