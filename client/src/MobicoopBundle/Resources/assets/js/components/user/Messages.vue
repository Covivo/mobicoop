<template>
  <v-content>
    <v-container
      text-xs-center
      grid-list-md
      fluid
    >
      <v-row
        id="headGridMessages"
      >
        <v-col
          class="col-4 pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline"
        >
          {{ $t("ui.pages.messages.label.messages") }}
        </v-col>
        <v-col
          text-xs-left
          class="col-5 pt-5 pb-4 pl-2 secondary white--text font-weight-bold headline"
        >
          {{ currentcorrespondant }}
        </v-col>
        <v-col
          text-xs-left
          class="col-3 pt-5 pb-4 pl-2 mr-0 secondary white--text font-weight-bold headline"
        >
          {{ $t("ui.pages.messages.label.context") }}
        </v-col>
      </v-row>
      <v-row>
        <v-col
          id="threadColumn"
          class="col-4"
        >
          <!-- Threads -->
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
            <v-tab-item
              v-if="this.threadsCM.length==0"
              value="tab-cm"
            >
              {{ $t("ui.pages.messages.label.nocarpoolmessages") }}
            </v-tab-item>
            <v-tab-item
              v-else
              value="tab-cm"
            >
              <v-card
                v-for="(threadCM, index) in threadsCM"
                :key="index"
                class="threads mx-auto mt-2"
                :class="threadCM.selected ? 'primary' : ''"
                @click="updateMessages(threadCM.idThreadMessage,threadCM.contactId)"
              >
                <v-card-title>
                  <v-icon>mdi-account-circle</v-icon>&nbsp;
                  <span
                    class="title font-weight-light"
                  >{{ threadCM.contactFirstName }} {{ threadCM.contactLastName.substr(0,1).toUpperCase()+"." }}</span>
                </v-card-title>
              </v-card>
            </v-tab-item>
            <v-tab-item
              v-if="this.threadsDM.length==0"
              value="tab-dm"
            >
              {{ $t("ui.pages.messages.label.nodirectmessages") }}
            </v-tab-item>
            <v-tab-item
              v-else
              value="tab-dm"
            >
              <v-card
                v-for="(thread, index) in threadsDM"
                :key="index"
                class="threads mx-auto mt-2"
                :class="thread.selected ? 'primary' : ''"
                @click="updateMessages(thread.idThreadMessage,thread.contactId,generateName(thread.contactFirstName,thread.contactLastName))"
              >
                <v-card-title>
                  <v-icon>mdi-account-circle</v-icon>&nbsp;
                  <span
                    class="title font-weight-light"
                  >{{ generateName(thread.contactFirstName,thread.contactLastName) }}</span>
                </v-card-title>
              </v-card>
            </v-tab-item>
          </v-tabs-items>
        </v-col>

        <v-col
          id="messagesColumn"
          class="col-5"
        >
          <!-- Messages -->

          <v-timeline v-if="(this.threadsDM.length>0 || this.threadsCM.length>0)">
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
                  <v-icon>{{ item.icon }}</v-icon>
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
              >{{ item.createdDateReadable }}</span>
            </v-timeline-item>
          </v-timeline>

          <v-container
            v-if="(this.threadsDM.length>0 || this.threadsCM.length>0)"
            fluid
            grid-list-md
          >
            <v-row
              row
              wrap
            >
              <v-col class="col-9">
                <v-textarea
                  v-model="textToSend"
                  name="typedMessage"
                  filled
                  :label="$t('ui.form.enterMessage')"
                  auto-grow
                  rows="2"
                  background-color="#FFFFFF"
                  value
                />
              </v-col>
              <v-col
                class="col-3"
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
                    <v-icon>mdi-send</v-icon>
                  </v-btn>
                </div>
              </v-col>
            </v-row>
          </v-container>
        </v-col>
        <v-col
          id="contextColumn"
          class="col-3"
        >
          <!-- Context -->
          <v-row>
            <v-col
              class="col-12"
              text-center
            >
              <v-card
                v-if="(this.threadsDM.length>0 || this.threadsCM.length>0)"
                class="pa-2 text-center"
              >
                <!-- The current carpool history -->
                <v-card-text class="font-weight-bold headline">
                  {{ currentcorrespondant }}
                </v-card-text>
                <v-card
                  v-if="currentAskHistory"
                  class="mb-3"
                >
                  <v-card-text>{{ currentAskHistory.ask.matching.criteria.fromDateReadable }} {{ $t("ui.infos.misc.at") }} {{ currentAskHistory.ask.matching.criteria.fromTimeReadable }}</v-card-text>
                  <!-- Timeline of the journey -->
                  <v-timeline dense>
                    <v-timeline-item
                      v-for="(waypoint, index) in infosJourney['waypoints']"
                      :key="index"
                      :icon="( (index>0) && (index<waypoint.length-1) ) ? 'mdi-arrow-right' : ''"
                      :color="( (index>0) && (index<waypoint.length-1) ) ? '' : 'primary'"
                      :icon-color="( (index>0) && (index<waypoint.length-1) ) ? 'warning' : 'primary'"
                      :fill-dot="( (index>0) && (index<waypoint.length-1) )"
                      class="text-left pb-2"
                      :class="( (index>0) && (index<waypoint.length-1) ) ? 'waypoint' : ''"
                    >
                      {{ waypoint }}
                    </v-timeline-item>
                  </v-timeline>

                  <v-divider />
                  <v-row>
                    <v-col
                      class="col-8"
                      text-left
                    >
                      <v-card-text class="py-1">
                        {{ $t("ui.infos.carpooling.distance") }}
                      </v-card-text>
                      <v-card-text class="py-1">
                        {{ $t("ui.infos.carpooling.availableSeats") }}
                      </v-card-text>
                      <v-card-text
                        class="font-weight-bold py-1"
                      >
                        {{ $t("ui.infos.carpooling.price") }}
                      </v-card-text>
                    </v-col>
                    <v-col
                      class="col-4"
                      text-right
                    >
                      <v-card-text class="py-1">
                        {{ infosJourney["distanceRounded"] }}km
                      </v-card-text>
                      <v-card-text class="py-1">
                        {{ infosJourney["seats"] }}
                      </v-card-text>
                      <v-card-text class="font-weight-bold py-1">
                        {{ infosJourney["price"] }} €
                      </v-card-text>
                    </v-col>
                  </v-row>
                </v-card>
                <v-card v-else>
                  <v-card-text>{{ $t("ui.pages.messages.label.notLinkedToACarpool") }}</v-card-text>
                </v-card>

                <!-- Button for asking a Carpool (only the contact initiator) -->
                <v-btn
                  v-if="currentAskHistory && currentAskHistory.ask.status==1 && askUser == userid"
                  rounded
                  color="secondary"
                  class="mb-2"
                  @click="dialogAskCarpool=true"
                >
                  {{ $t("ui.button.askCarpool") }}
                </v-btn>

                <!-- Carpooling status -->
                <!-- Carpool Asked -->
                <v-card
                  v-else-if="currentAskHistory && currentAskHistory.ask.status==2 && askUser == userid"
                  color="warning"
                >
                  <v-card-text class="white--text">
                    {{ $t("ui.infos.carpooling.askAlreadySent") }}
                  </v-card-text>
                </v-card>
                <!-- Carpool Confirmed -->
                <v-card
                  v-else-if="currentAskHistory && currentAskHistory.ask.status==3"
                  color="success"
                >
                  <v-card-text class="white--text">
                    {{ $t("ui.infos.carpooling.accepted") }}
                  </v-card-text>
                </v-card>
                <!-- Carpool Refused -->
                <v-card
                  v-else-if="currentAskHistory && currentAskHistory.ask.status==4"
                  color="error"
                >
                  <v-card-text class="white--text">
                    {{ $t("ui.infos.carpooling.refused") }}
                  </v-card-text>
                </v-card>
                <!-- Accept/Refuse a Carpool -->
                <div
                  v-if="currentAskHistory && currentAskHistory.ask.status==2 && askUser != userid"
                  class="my-2"
                >
                  <v-container text-center>
                    <v-row>
                      <v-col class="col-12 col-lg-6">
                        <!-- <v-btn
                          color="success"
                          rounded
                          @click="updateCarpool(3)"
                        >
                          {{ $t("ui.button.accept") }} <v-icon>mdi-check</v-icon>
                        </v-btn> -->
                        <m-btn
                          color="success"
                          @click.native="updateCarpool(3)"
                        >
                          {{ $t("ui.button.accept") }} <v-icon>mdi-check</v-icon>
                        </m-btn>
                      </v-col>
                      <v-col class="col-12 col-lg-6">
                        <m-btn
                          color="error"
                          @click.native="updateCarpool(4)"
                        >
                          {{ $t("ui.button.refuse") }} <v-icon>mdi-close</v-icon>
                        </m-btn>
                      </v-col>
                    </v-row>
                  </v-container>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-col>
      </v-row>

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
              @click="updateCarpool(2)"
            >
              {{ $t("ui.button.accept") }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </v-content>
</template>
<script>
import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/Messages.json";
import TranslationsClient from "@clientTranslations/components/user/Messages.json";
import MBtn from '@components/utilities/MBtn'

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    MBtn
  },
  props: {
    threadsdirectmessagesforview: {
      type: Array,
      default: function() {
        return [];
      }
    },
    threadscarpoolingmessagesforview: {
      type: Array,
      default: function() {
        return [];
      }
    },
    userid: {
      type: String,
      default: ""
    },
    idmessagedefault: {
      type: String,
      default: ""
    },
    idrecipientdefault: {
      type: String,
      default: ""
    },
    firstnamerecipientdefault: {
      type: String,
      default: ""
    },
    lastnamerecipientdefault: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      items: [],
      threadsDM: this.threadsdirectmessagesforview,
      threadsCM: this.threadscarpoolingmessagesforview,
      spinner: false,
      dialogAskCarpool: false,
      textToSend: "",
      idThreadMessage: this.idmessagedefault,
      currentcorrespondant: "...",
      idRecipient: null,
      textSpinnerLoading: this.$t("ui.pages.messages.spinner.loading"),
      textSpinnerSendMessage: this.$t("ui.pages.messages.spinner.sendMessage"),
      textSpinnerAskCarpool: this.$t("ui.pages.messages.spinner.askCarpool"),
      textSpinnerUpdateCarpool: this.$t(
        "ui.pages.messages.spinner.updateCarpool"
      ),
      textSpinner: "",
      currentAskHistory: null,
      askUser: 0,
      infosJourney: [],
      modelTabs:"tab-cm"
    };
  },
  watch: {
    // whenever question changes, this function will run
    currentAskHistory: function(newCurrentAskHistory, oldCurrentAskHistory) {
      this.updateContextPanel();
    }
  },
  mounted() {
    this.textSpinner = this.textSpinnerLoading;
    if (this.threadsDM.length > 0 || this.threadsCM.length > 0) {
      this.updateMessages();
    }
  },
  methods: {
    updateMessages(
      idMessage = this.idmessagedefault,
      idrecipient = this.idrecipientdefault,
      contactName = this.generateName(
        this.firstnamerecipientdefault,
        this.lastnamerecipientdefault
      )
    ) {
      this.threadsDM.forEach((thread, index) => {
        this.threadsDM[index].selected =
          thread.idThreadMessage === parseInt(idMessage) ? true : false;
      });
      this.threadsCM.forEach((thread, index) => {
        this.threadsCM[index].selected =
          thread.idThreadMessage === parseInt(idMessage) ? true : false;
      });
      this.textSpinner = this.textSpinnerLoading;
      this.spinner = true;
      this.idThreadMessage = idMessage;
      axios.get("/utilisateur/messages/" + idMessage).then(res => {
        let messagesThread = res.data.messages;
        this.items.length = 0; // Reset items (the source of messages column)

        // update askHistory et askUser
        this.currentAskHistory = res.data.lastAskHistory;
        this.askUser = res.data.user.id;

        // The date of the first message
        let divider = {
          divider: true,
          createdDateReadable: res.data.createdDateReadable
        };
        this.addMessageToItems(divider);

        let threadMessage = {
          id: res.data.id,
          user: res.data.user,
          text: res.data.text,
          createdDateReadable: res.data.createdDateReadable,
          createdTimeReadable: res.data.createdTimeReadable,
          divider: false
        };

        this.addMessageToItems(threadMessage);

        // The correspondant for the view
        this.currentcorrespondant = contactName;

        // Id of the current recipient
        this.idRecipient = idrecipient;

        let currentDate = res.data.createdDateReadable;
        for (let message of messagesThread) {
          // If the date is different, push a divider
          if (message.createdDateReadable !== currentDate) {
            let divider = {
              divider: true,
              createdDateReadable: message.createdDateReadable
            };
            currentDate = message.createdDateReadable;
            this.addMessageToItems(divider);
          }

          this.addMessageToItems(message);
        }


        // We check that the good tab is active
        (this.currentAskHistory === null) ? this.modelTabs = "tab-dm" : this.modelTabs = "tab-cm";

        this.spinner = false;
      });
    },
    updateContextPanel() {
      if (this.currentAskHistory !== null) {
        this.infosJourney.length = 0; // Reset journey infos

        this.infosJourney["waypoints"] = new Array();
        for (let waypoint of this.currentAskHistory.ask.matching.waypoints) {
          // Get the diffrent waypoints
          this.infosJourney["waypoints"].push(waypoint.address.addressLocality);
        }

        // update distance
        this.infosJourney["distance"] =
          parseInt(
            this.currentAskHistory.ask.matching.proposalRequest.criteria
              .directionPassenger.distance
          ) / 1000;
        this.infosJourney["distanceRounded"] = Math.round(
          this.infosJourney["distance"]
        );

        // update price
        this.infosJourney["price"] = (
          this.infosJourney["distance"] *
          parseFloat(
            this.currentAskHistory.ask.matching.proposalRequest.criteria.priceKm
          )
        ).toFixed(2);

        // seats
        this.infosJourney[
          "seats"
        ] = this.currentAskHistory.ask.matching.criteria.seats;
      }
    },
    sendInternalMessage() {
      let messageToSend = new FormData();
      messageToSend.append("idThreadMessage", this.idThreadMessage);
      messageToSend.append("text", this.textToSend);
      messageToSend.append("idRecipient", this.idRecipient);
      if (this.currentAskHistory !== null) {
        messageToSend.append("idAskHistory", this.currentAskHistory.id);
      }
      this.textSpinner = this.textSpinnerSendMessage;
      this.spinner = true;
      axios.post("/utilisateur/messages/envoyer", messageToSend).then(res => {
        this.textToSend = "";
        this.spinner = false;
        if (this.updateMessages(res.data.message) !== undefined) {
          this.updateMessages(res.data.message.id);
        } else {
          this.updateMessages();
        }
      });
    },
    updateCarpool(status) {
      this.dialogAskCarpool = false;
      this.textSpinner = this.textSpinnerUpdateCarpool;
      this.spinner = true;
      let params = new FormData();
      params.append("idAsk", this.currentAskHistory.ask.id);
      params.append("status", status);
      axios.post("/utilisateur/messages/updateAsk", params).then(res => {
        this.currentAskHistory.ask.status = res.data.status;
        this.spinner = false;
        this.updateMessages();
      });
    },
    addMessageToItems(message) {
      let tabItem = new Array();

      tabItem["divider"] =
        message.divider !== undefined ? message.divider : false;
      tabItem["createdDateReadable"] = message.createdDateReadable;

      if (!message.divider) {
        tabItem["idMessage"] = message.id;
        tabItem["userFirstName"] = message.user.givenName;
        tabItem["userLastName"] =
          message.user.familyName.substr(0, 1).toUpperCase() + ".";
        tabItem["icon"] = "mdi-account-circle";
        tabItem["text"] = message.text;
        tabItem["createdTimeReadable"] = message.createdTimeReadable;
        message.user.id == this.userid
          ? (tabItem["origin"] = "own")
          : (tabItem["origin"] = "contact");
      }

      this.items.push(tabItem);
    },
    generateName(firstname, lastname) {
      return firstname + " " + lastname.substr(0, 1).toUpperCase() + ".";
    }
  }
};
</script>
<style lang="scss" scoped>
#headGridMessages{
  .col{
    border-left: 2px solid white !important;
  }
}
</style>