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
      <v-row>
        <v-col class="col-4 pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline">
          <mail-box-header>{{ $t("ui.pages.messages.label.messages") }}</mail-box-header>
        </v-col>
        <v-col
          text-xs-left
          class="col-5 pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline"
        >
          <mail-box-header />
        </v-col>
        <v-col
          text-xs-left
          class="col-3 pt-5 pb-4 pl-2 mr-0 secondary white--text font-weight-bold headline"
        >
          <mail-box-header>{{ $t("ui.pages.messages.label.context") }}</mail-box-header>
        </v-col>
      </v-row>
      <v-row>
        <v-col
          id="threadColumn"
          class="col-4"
        >
          <v-tabs
            v-model="modelTabs"
            slider-color="secondary"
            color="secondary"
            grow
          >
            <v-tab
              :key="0"
              ripple
              href="#tab-cm"
              class="ml-0"
            >
              {{ $t("ui.pages.messages.label.ongoingasks") }}
            </v-tab>
            <v-tab
              :key="1"
              ripple
              href="#tab-dm"
              class="ml-0"
            >
              {{ $t("ui.pages.messages.label.directmessages") }}
            </v-tab>
          </v-tabs>          
          <v-tabs-items v-model="modelTabs">
            <v-tab-item value="tab-cm">
              <threads-carpool />
            </v-tab-item>
            <v-tab-item value="tab-dm">
              <threads-direct @idMessageForTimeLine="updateDetails" />
            </v-tab-item>
          </v-tabs-items>
        </v-col>
        <v-col
          id="messagesColumn"
          class="col-5"
        >
          <v-row>
            <v-col cols="12">
              <thread-details
                ref="threadDetails"
                :id-message="this.idMessage"
                :id-user="this.idUser"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <send-message 
                :id-thread-message="this.idMessage"
                :id-recipient="this.idRecipient"
                @updateThreadDetails="updateThreadDetails"
              />
            </v-col>
          </v-row>
        </v-col>
        <v-col
          id="contextColumn"
          class="col-3"
        >
          <thread-actions />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/mailbox/Messages.json";
import MailBoxHeader from '@components/user/mailbox/MailBoxHeader'
import ThreadsDirect from '@components/user/mailbox/ThreadsDirect'
import ThreadsCarpool from '@components/user/mailbox/ThreadsCarpool'
import ThreadDetails from '@components/user/mailbox/ThreadDetails'
import ThreadActions from '@components/user/mailbox/ThreadActions'
import SendMessage from '@components/user/mailbox/SendMessage'

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
    SendMessage
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
      idRecipient:null
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
    },
    updateThreadDetails(data){
      this.idMessage = data.idThreadMessage;
      this.$refs.threadDetails.getCompleteThread();
    }
  }
};
</script>