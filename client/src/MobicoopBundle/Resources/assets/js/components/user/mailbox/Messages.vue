<template>
  <div>
    <v-container
      text-xs-center
      grid-list-md
      fluid
    >
      <v-row justify="center">
        <v-col class="mr-n12 ml-n12">
          <warning-message :fraud-warning-display="fraudWarningDisplay" />
        </v-col>
      </v-row>
      <v-row id="headGridMessages">
        <v-col class="col-3 pt-5 pb-4 pl-2 secondary white--text font-weight-bold text-h5">
          <mail-box-header>{{ $t("headers.messages") }}</mail-box-header>
        </v-col>
        <v-col
          text-xs-left
          class="col-5 pt-5 pb-4 pl-2 secondary white--text font-weight-bold text-h5"
        >
          <mail-box-header>{{ recipientName }}</mail-box-header>
        </v-col>
        <v-col
          text-xs-left
          class="col-4 pt-5 pb-4 pl-2 mr-0 secondary white--text font-weight-bold text-h5"
        >
          <mail-box-header>{{ $t("headers.context") }}</mail-box-header>
        </v-col>
      </v-row>
      <v-row>
        <v-col class="col-3">
          <v-tabs
            v-model="modelTabs"
            slider-color="secondary"
            color="secondary"
            center-active
            centered
            next-icon="mdi-arrow-right-thick"
            prev-icon="mdi-arrow-left-thick"
            show-arrows
          >
            <v-tab
              :key="0"
              href="#tab-cm"
              class="ma-0 mx-lg-6"
              ripple
              @click="reloadOnIcon()"
            >
              <div>
                <v-icon class="text-h5">
                  mdi-car
                </v-icon>
                <br>
                <div
                  class="mb-2"
                  style="letter-spacing: -0.15px;"
                >
                  <v-badge
                    :value="(unreadMessages.currentUnreadCarpoolMessages > 0) ? true : false"
                    :content="unreadMessages.currentUnreadCarpoolMessages"
                    color="secondary"
                    inline
                  >
                    {{ $t("headersCategories.titleCarpool") }}
                  </v-badge>
                </div>
              </div>
            </v-tab>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-tab
                  :key="1"
                  v-bind="attrs"
                  href="#tab-dm"
                  class="ma-0"
                  ripple
                  v-on="on"
                  @click="reloadOnIcon()"
                >
                  <div>
                    <v-icon class="text-h5">
                      mdi-chat
                    </v-icon>
                    <br>
                    <div class="mb-2">
                      <v-badge
                        :value="(unreadMessages.currentUnreadDirectMessages > 0) ? true : false"
                        :content="unreadMessages.currentUnreadDirectMessages"
                        color="secondary"
                        inline
                      >
                        {{ $t("headersCategories.titleLive") }}
                      </v-badge>
                    </div>
                  </div>
                </v-tab>
              </template><span>{{ $t('tooltip.message') }}</span>
            </v-tooltip>
            <v-tab
              v-if="solidaryDisplay"
              :key="2"
              href="#tab-sm"
              class="ma-0"
              ripple
              @click="reloadOnIcon()"
            >
              <div>
                <v-icon class="text-h5">
                  mdi-hand-heart
                </v-icon>
                <br>
                <div
                  class="mb-2"
                  style="letter-spacing: -0.15px;"
                >
                  <v-badge
                    :value="(unreadMessages.currentUnreadSolidaryMessages > 0) ? true : false"
                    :content="unreadMessages.currentUnreadSolidaryMessages"
                    color="secondary"
                    inline
                  >
                    {{ $t("headersCategories.titleSolidary") }}
                  </v-badge>
                </div>
              </div>
            </v-tab>
          </v-tabs>
          <v-tabs-items v-model="modelTabs">
            <v-container class="window-scroll">
              <v-tab-item value="tab-cm">
                <threads-carpool
                  :new-thread="newThreadCarpool"
                  :id-message="idMessage"
                  :id-thread-default="idThreadDefault"
                  :id-ask-to-select="currentIdAsk"
                  :id-booking-to-select="currentIdBooking"
                  :refresh-threads="refreshThreadsCarpool"
                  @idMessageForTimeLine="updateDetails"
                  @toggleSelected="refreshSelected"
                  @toggleSelectedBooking="refreshSelectedBooking"
                  @refreshThreadsCarpoolCompleted="refreshThreadsCarpoolCompleted"
                />
              </v-tab-item>
              <v-tab-item value="tab-dm">
                <threads-direct
                  :new-thread="newThreadDirect"
                  :id-message="idMessage"
                  :id-thread-default="idThreadDefault"
                  :id-message-to-select="idMessage"
                  :refresh-threads="refreshThreadsDirect"
                  @idMessageForTimeLine="updateDetails"
                  @toggleSelected="refreshSelected"
                  @refreshThreadsDirectCompleted="refreshThreadsDirectCompleted"
                />
              </v-tab-item>
              <v-tab-item
                v-if="solidaryDisplay"
                value="tab-sm"
              >
                <threads-solidary
                  :id-message="idMessage"
                  :id-thread-default="idThreadDefault"
                  :id-ask-to-select="currentIdAsk"
                  :refresh-threads="refreshThreadsSolidary"
                  @idMessageForTimeLine="updateDetails"
                  @toggleSelected="refreshSelected"
                  @refreshThreadsSolidaryCompleted="refreshThreadsSolidaryCompleted"
                />
              </v-tab-item>
            </v-container>
          </v-tabs-items>
        </v-col>
        <v-col class="col-5">
          <v-row>
            <v-col cols="12">
              <thread-details
                v-if="idMessage"
                :id-message="idMessage"
                :id-user="idUser"
                :refresh="refreshDetails"
                :hide-no-thread-selected="(idRecipient !== null)"
                :fraud-warning-display="fraudWarningDisplay"
                :carpoolers-identity="carpoolersIdentity"
                @refreshCompleted="refreshDetailsCompleted"
              />
              <v-alert
                v-else
                type="info"
              >
                {{ $t('info.withoutThread') }}
              </v-alert>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <booking-thread-details
                v-if="idBooking"
                :id-booking="idBooking"
                :id-user="idUser"
                :refresh-booking="refreshBookingDetails"
                :hide-no-thread-selected="(idRecipient !== null)"
                :fraud-warning-display="fraudWarningDisplay"
                :carpoolers-identity="carpoolersIdentity"
                @refreshCompleted="refreshBookingDetailsCompleted"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col
              v-if="(idMessage && idMessage !== -2) || newThread || idBooking !== null"
              cols="12"
            >
              <type-text
                ref="typeText"
                :id-thread-message="idMessage"
                :id-recipient="idRecipient"
                :loading="loadingTypeText"
                :is-external-standard-message="isExternalStandard"
                :hidden="hideClickIcon"
                :recipient-blocked-id="blockerId"
                @sendInternalMessage="sendInternalMessage"
                @sendExternalStandardMessage="sendExternalStandardMessage"
              />
            </v-col>
          </v-row>
        </v-col>
        <v-col class="col-4">
          <thread-actions
            :id-booking="idBooking"
            :refresh-booking="refreshBookingActions"
            :is-external-standard="isExternalStandard"
            :id-ask="currentIdAsk"
            :id-user="idUser"
            :email-user="emailUser"
            :id-recipient="idRecipient"
            :loading-init="loadingDetails"
            :refresh="refreshActions"
            :loading-btn="loadingBtnAction"
            :recipient-name="recipientName"
            :recipient-avatar="recipientAvatar"
            :blocker-id="blockerId"
            :new-thread="newThread"
            @refreshActionsCompleted="refreshActionsCompleted"
            @refreshBookingActionsCompleted="refreshBookingActionsCompleted"
            @updateStatusAskHistory="updateStatusAskHistory"
            @updateStatusBooking="updateStatusBooking"
            @recipientIdentity="setCarpoolerIdentity"
          />
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>
<script>
import maxios from "@utils/maxios";
import { messages_en, messages_fr, messages_eu, messages_nl } from "@translations/components/user/mailbox/Messages/";
import MailBoxHeader from '@components/user/mailbox/MailBoxHeader'
import ThreadsDirect from '@components/user/mailbox/ThreadsDirect'
import ThreadsCarpool from '@components/user/mailbox/ThreadsCarpool'
import ThreadsSolidary from '@components/user/mailbox/ThreadsSolidary'
import ThreadDetails from '@components/user/mailbox/ThreadDetails'
import BookingThreadDetails from '@components/user/mailbox/BookingThreadDetails'
import ThreadActions from '@components/user/mailbox/ThreadActions'
import TypeText from '@components/user/mailbox/TypeText'
import WarningMessage from '@components/utilities/WarningMessage.vue';

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu': messages_eu
    }
  },
  components: {
    MailBoxHeader,
    ThreadsDirect,
    ThreadsCarpool,
    ThreadsSolidary,
    ThreadDetails,
    ThreadActions,
    TypeText,
    WarningMessage,
    BookingThreadDetails
  },
  props: {
    user: {
      type: Object,
      default: null
    },
    idUser: {
      type: Number,
      default: null
    },
    emailUser: {
      type: String,
      default: null
    },
    idThreadDefault: {
      type: Number,
      default: null
    },
    newThread: {
      type: Object,
      default: null
    },
    givenIdAsk: {
      type: Number,
      default: null
    },
    givenIdBooking: {
      type: String,
      default: null
    },
    givenIdMessage: {
      type: Number,
      default: null
    },
    givenIdRecipient: {
      type: Number,
      default: null
    },
    solidaryDisplay: {
      type: Boolean,
      default: true
    },
    fraudWarningDisplay: {
      type: Boolean,
      default: false
    },
    defaultThreadTab: {
      type: String,
      default: null
    },
    eecDisplay: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      modelTabs: "tab-cm",
      idMessage: this.givenIdMessage ? this.givenIdMessage : null,
      idBooking: this.givenIdBooking ? this.givenIdBooking : null,
      idRecipient: this.givenIdRecipient ? this.givenIdRecipient : null,
      currentIdAsk: this.givenIdAsk ? this.givenIdAsk : null,
      currentIdBooking: this.givenIdBooking ? this.givenIdBooking : null,
      recipientName: null,
      recipientAvatar: null,
      newThreadDirect: null,
      newThreadCarpool: null,
      loadingTypeText: false,
      refreshDetails: false,
      refreshThreadsDirect: false,
      refreshThreadsCarpool: false,
      refreshThreadsSolidary: false,
      refreshActions: false,
      loadingDetails: false,
      loadingBtnAction: false,
      hideClickIcon: false,
      blockerId: null,
      unreadMessages: {
        currentUnreadCarpoolMessages: 0,
        currentUnreadDirectMessages: 0,
        currentUnreadSolidaryMessages: 0
      },
      isExternalStandard: false,
      refreshBookingActions: false,
      carpoolersIdentity: null,
      refreshBookingDetails: false
    };
  },
  created() {
    switch (this.defaultThreadTab) {
    case 'carpool':
      this.modelTabs = "tab-cm";
      break;
    case 'direct':
      this.modelTabs = "tab-dm";
      break;
    case 'solidary':
      this.modelTabs = "tab-sm";
      break;
    }
  },
  mounted() {
    // If there is a new thread we give it to te right component
    if (this.newThread) {
      if (this.newThread.carpool) {
        this.newThreadCarpool = this.newThread
        this.modelTabs = "tab-cm";
        this.idRecipient = this.newThread.idRecipient;
      }
      else {
        this.newThreadDirect = this.newThread;
        this.modelTabs = "tab-dm";
        this.idRecipient = this.newThread.idRecipient;
      }
    }
    if (this.givenIdAsk) {
      this.refreshActions = true;
    }
    this.unreadMessages.currentUnreadCarpoolMessages = this.$store.getters['m/unreadCarpoolMessageNumber'];
    this.unreadMessages.currentUnreadDirectMessages = this.$store.getters['m/unreadDirectMessageNumber'];
    this.unreadMessages.currentUnreadSolidaryMessages = this.$store.getters['m/unreadSolidaryMessageNumber'];
  },
  methods: {
    updateDetails(data) {
      // console.error(data);
      this.hideClickIcon = false;

      this.isExternalStandard = (data.idBooking !== null) ? true : false;
      // Update the current Ask
      (data.type == "Carpool" || data.type == "Solidary") ? this.currentIdAsk = data.idAsk : this.currentIdAsk = null;

      // Update the number of unread messages in the right tab
      if (data.type == "Carpool") {
        this.$store.commit('m/setUnreadCarpoolMessageNumber', this.unreadMessages.currentUnreadCarpoolMessages - 1);
        this.unreadMessages.currentUnreadCarpoolMessages = this.$store.getters['m/unreadCarpoolMessageNumber'];
      }
      else if (data.type == "Solidary") {
        this.$store.commit('m/setUnreadSolidaryMessageNumber', this.unreadMessages.currentUnreadSolidaryMessages - 1);
        this.unreadMessages.currentUnreadSolidaryMessages = this.$store.getters['m/unreadSolidaryMessageNumber'];
      }
      else if (data.type == "Direct") {
        this.$store.commit('m/setUnreadDirectMessageNumber', this.unreadMessages.currentUnreadDirectMessages - 1);
        this.unreadMessages.currentUnreadDirectMessages = this.$store.getters['m/unreadDirectMessageNumber'];
      }

      this.idMessage = data.idMessage;
      this.idBooking = data.idBooking;
      this.idRecipient = data.idRecipient;
      this.recipientName = data.name;
      this.recipientAvatar = data.avatar;
      this.blockerId = data.blockerId;
    },
    sendInternalMessage(data) {
      this.loadingTypeText = true;
      let messageToSend = {
        idThreadMessage: data.idThreadMessage,
        text: data.textToSend,
        idRecipient: data.idRecipient,
        idAsk: this.currentIdAsk
      };

      if (this.newThreadCarpool && this.newThreadCarpool.matchingId) {
        messageToSend.matchingId = this.newThreadCarpool.matchingId;
        messageToSend.proposalId = this.newThreadCarpool.proposalId;
        messageToSend.adIdToRespond = this.newThreadCarpool.adId;
      }
      maxios.post(this.$t("urlSend"), messageToSend).then(res => {
        this.idMessage = (res.data.message !== null) ? res.data.message.id : res.data.id;
        this.currentIdAsk = (res.data.idAsk !== null) ? res.data.idAsk : this.currentIdAsk;
        this.loadingTypeText = false;
        // Update the threads list
        (this.currentIdAsk) ? this.refreshThreadsCarpool = true : this.refreshThreadsDirect = true;
        // We need to delete new thread data or we'll have two identical entries
        this.refreshDetails = true;
        this.newThreadDirect = null;
        this.newThreadCarpool = null;
        (this.currentIdAsk) ? this.refreshSelected({ 'idAsk': this.currentIdAsk }) : this.refreshSelected({ 'idMessage': this.idMessage });
      });
    },
    sendExternalStandardMessage(data) {
      this.loadingTypeText = true;
      let messageToSend = {
        text: data.textToSend,
        from: this.newThreadCarpool.roleDriver ? this.newThreadCarpool.driver : this.newThreadCarpool.passenger,
        to: this.newThreadCarpool.roleDriver ? this.newThreadCarpool.passenger : this.newThreadCarpool.driver,
        driverJourneyId: this.newThreadCarpool.driverJourneyId,
        passengerJourneyId: this.newThreadCarpool.passengerJourneyId,
        recipientCarpoolerType: this.newThreadCarpool.roleDriver ? 'PASSENGER' : 'DRIVER',
        bookingId: this.newThreadCarpool.externalId
      };
      this.loadingTypeText = false;
      console.log(messageToSend);
      maxios.post(this.$t("urlSendExternalMessage"), messageToSend).then(res => {
        this.loadingTypeText = false;
        // Update the threads list
        this.refreshThreadsCarpool = true;
        // We need to delete new thread data or we'll have two identical entries
        this.refreshBookingDetails = true;
        this.newThreadDirect = null;
        this.newThreadCarpool = null;
      });
    },
    updateStatusAskHistory(data) {
      this.loadingBtnAction = true;
      let params = {
        idAsk: this.currentIdAsk
      }

      // Compute the right status for the update
      let statusUpdate = 1;
      if (data.status == 1 && data.driver) {
        statusUpdate = 2
      }
      else if (data.status == 1 && !data.driver) {
        statusUpdate = 3
      }
      else {
        statusUpdate = data.status
      }

      // If it's already a formal ask, we don't need everything
      if (statusUpdate > 3) {
        params = {
          "idAsk": this.currentIdAsk,
          "status": statusUpdate
        }
      }
      else {
        params = {
          "idAsk": this.currentIdAsk,
          "outwardDate": data.fromDate,
          "outwardLimitDate": data.toDate,
          "outwardSchedule": data.outwardSchedule,
          "returnSchedule": data.returnSchedule,
          "status": statusUpdate
        }
      }
      // console.error(data);sk
      // console.error(params);
      maxios.post(this.$t("urlUpdateAsk"), params)
        .then(response => {
          //console.error(response.data);
          this.refreshActions = true;
          // buttons become usable before the whole component is updated and so user can accept or refused multiple times, creating multiple proposals
          // this.loadingBtnAction = false;
        })
        .catch(function (error) {
          console.error(error);
        });

    },
    updateStatusBooking(data) {
      this.loadingBtnAction = true;
      let params = {
        idBooking: this.currentIdBooking,
        status: data.status,
      }
      maxios.post(this.$t("urlUpdateBooking"), params)
        .then(response => {
          console.error(response.data);
          this.refreshActions = true;
          // buttons become usable before the whole component is updated and so user can accept or refused multiple times, creating multiple proposals
          // this.loadingBtnAction = false;
        })
        .catch(function (error) {
          console.error(error);
        });

    },
    refreshSelected(data) {
      this.loadingDetails = true;
      this.isExternalStandard = false;
      this.currentIdBooking = null;
      (data.idAsk) ? this.currentIdAsk = data.idAsk : this.idMessage = data.idMessage;
      this.refreshActions = true;
    },
    refreshSelectedBooking(data) {
      this.currentIdAsk = null;
      this.currentIdBooking = data.idBooking;
      this.message = -99;
      this.isExternalStandard = true;
      this.idBooking = data.idBooking;
      this.refreshBookingActions = true;
    },
    reloadOnIcon() {
      this.loadingDetails = true;
      this.refreshActions = true;
      this.refreshDetails = true;
      this.hideClickIcon = true;
      this.idMessage = -2;
      this.cleanAsk();
    },
    cleanAsk() {
      this.recipientName = null;
      this.currentIdAsk = null;
      this.idRecipient = null;
      this.recipientAvatar = null;
    },
    refreshDetailsCompleted(data) {
      //this.refreshActions = true;
      this.refreshDetails = false;
    },
    refreshBookingDetailsCompleted(data) {
      //this.refreshActions = true;
      this.refreshBookingDetails = false;
    },
    refreshThreadsDirectCompleted() {
      this.refreshThreadsDirect = false;
    },
    refreshThreadsCarpoolCompleted() {
      this.refreshThreadsCarpool = false;
    },
    refreshThreadsSolidaryCompleted() {
      this.refreshThreadsSolidary = false;
    },
    refreshActionsCompleted() {
      this.loadingDetails = false;
      this.refreshActions = false;
      this.loadingBtnAction = false;
      this.refreshBookingActions = false;
    },
    refreshBookingActionsCompleted(booking) {
      console.log(booking);
      this.loadingDetails = false;
      this.loadingBtnAction = false;
      this.refreshBookingActions = false;
      this.newThreadCarpool = booking;
    },
    setCarpoolerIdentity(e) {
      this.carpoolersIdentity = {
        sender: {
          id: this.user.id,
          identityStatus: this.user.identityStatus,
          bankingIdentityStatus: this.user.bankingIdentityStatus,
          eecStatus: this.user.eecStatus,
          role: e.role === DRIVER ? PASSENGER : DRIVER
        },
        recipient: e
      };
    }
  }
};

export const DRIVER = 1;
export const PASSENGER = 2;
</script>
<style lang="scss">
.v-main__wrap {
  #headGridMessages {
    .col {
      border-left: 2px solid white !important;
    }
  }

  .window-scroll {
    max-height: 600px;
    overflow: auto;
  }
}
</style>
