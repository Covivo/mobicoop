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
              v-if="threadsCM.length==0"
              value="tab-cm"
            >
              {{ $t("ui.pages.messages.label.nocarpoolmessages") }}
            </v-tab-item>
            <v-tab-item
              v-else
              value="tab-cm"
            >
              <v-container class="window-scroll">
                <v-card
                  v-for="(threadCM, index) in threadsCM"
                  :key="index"
                  class="threads mx-auto mt-2"
                  :class="threadCM.selected ? 'primary' : ''"
                  @click="updateMessages(threadCM.idThreadMessage,threadCM.contactId)"
                >
                  <v-card-title class="pa-0 ma-0">
                    <v-container>
                      <v-row
                        align="start"
                      >
                        <v-col class="col-3 text-center ma-0 pa-0">
                          <v-avatar>
                            <v-icon class="display-2">
                              mdi-account-circle
                            </v-icon>
                          </v-avatar>
                        </v-col>
                        <v-col class="col-6 ma-0 pa-0">
                          <v-card-text class="pa-0">
                            <span
                              class="title font-weight-light secondary--text"
                            >
                              {{ threadCM.contactFirstName }} {{ threadCM.contactLastName.substr(0,1).toUpperCase()+"." }}</span><br>
                            <span :class="threadCM.selected ? 'font-weight-bold' : ''">
                              {{ threadCM.firstWayPoint }}
                              <v-icon color="tertiairy">
                                mdi-arrow-right
                              </v-icon> {{ threadCM.lastWayPoint }}
                            </span><br>
                            <span
                              v-if="!threadCM.dayChecked"
                              class="font-italic"
                            >{{ threadCM.fromDateReadable }} {{ $t("ui.infos.misc.at") }} {{ threadCM.fromTimeReadable }}</span>
                            <span
                              v-else
                              class="font-italic"
                            >{{ threadCM.dayChecked.join(", ") }}</span>
                          </v-card-text>
                        </v-col>
                        <v-col class="col-3 ma-0 pa-0">
                          <v-card-text
                            class="pa-0 ma-0 text-right pr-2 font-italic"
                          >
                            {{ threadCM.lastMessageCreatedDate }}
                          </v-card-text>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-title>
                </v-card>
              </v-container>
            </v-tab-item>
            <v-tab-item
              v-if="threadsDM.length==0"
              value="tab-dm"
            >
              {{ $t("ui.pages.messages.label.nodirectmessages") }}
            </v-tab-item>
            <v-tab-item
              v-else
              value="tab-dm"
            >
              <v-container class="window-scroll">
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
              </v-container>
            </v-tab-item>
          </v-tabs-items>
        </v-col>

        <v-col
          id="messagesColumn"
          class="col-5"
        >
          <!-- Messages -->

          <v-container class="window-scroll">
            <v-timeline v-if="(threadsDM.length>0 || threadsCM.length>0)">
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
          </v-container>
          <v-container
            v-if="(threadsDM.length>0 || threadsCM.length>0)"
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
                v-if="(threadsDM.length>0 || threadsCM.length>0)"
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
                  <ad-summary
                    :display-info="false"
                    :regular="regular"
                    :route="route"
                    :outward-date="outwardDate"
                    :outward-time="outwardTime"
                    :schedules="schedules"
                    :user="user"
                  />

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
import moment from "moment";
import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/Messages.json";
import TranslationsClient from "@clientTranslations/components/user/Messages.json";
import MBtn from '@components/utilities/MBtn'
import AdSummary from '@components/carpool/AdSummary'

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    MBtn,
    AdSummary
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
    },
    user: {
      type: Object,
      default: null
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
      route: {},
      schedules: [],
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
      modelTabs:"tab-cm",
      outwardDate: null,
      outwardTime: null,
      regular: false,
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
        if(thread.lastMessageCreatedDate==="today"){
          thread.lastMessageCreatedDate = this.$t("ui.date.today");
        }
      });
      this.threadsCM.forEach((thread, index) => {
        this.threadsCM[index].selected =
          thread.idThreadMessage === parseInt(idMessage) ? true : false;
        if(thread.lastMessageCreatedDate==="today"){
          thread.lastMessageCreatedDate = this.$t("ui.date.today");
        }
        if(thread.dayChecked){
          thread.dayChecked.forEach((day, index)=>{
            thread.dayChecked[index] = this.$t("ui.date."+day);
          });
        }
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
      // build the route of the carpool
      this.route.waypoints = this.currentAskHistory.ask.matching.waypoints;
      this.route.direction = this.currentAskHistory.ask.matching.proposalRequest.criteria.directionPassenger;
      this.route.origin = this.currentAskHistory.ask.matching.waypoints[0].address;
      for (let waypoint of this.currentAskHistory.ask.matching.waypoints) {
        if (waypoint.destination == true) {
          this.route.destination = waypoint.address
        }
      }
      // format the outwardDate and outwardTime
      this.outwardDate = moment(this.currentAskHistory.ask.criteria.fromDate).format('YYYY-MM-DD')
      this.outwardTime = moment(this.currentAskHistory.ask.criteria.fromTime).format('hh:mm')
      // build schedules of the regular carpool
      if (this.currentAskHistory.ask.criteria.frequency == 2) {
        this.regular = true;
        let hours = new Array();
        (hours[this.currentAskHistory.ask.criteria.monTime]===undefined) ? hours[this.currentAskHistory.ask.criteria.monTime] = ["mon"] : hours[this.currentAskHistory.ask.criteria.monTime].push("mon");
        (hours[this.currentAskHistory.ask.criteria.tueTime]===undefined) ? hours[this.currentAskHistory.ask.criteria.tueTime] = ["tue"] : hours[this.currentAskHistory.ask.criteria.tueTime].push("tue");
        (hours[this.currentAskHistory.ask.criteria.wedTime]===undefined) ? hours[this.currentAskHistory.ask.criteria.wedTime] = ["wed"] : hours[this.currentAskHistory.ask.criteria.wedTime].push("wed");
        (hours[this.currentAskHistory.ask.criteria.thuTime]===undefined) ? hours[this.currentAskHistory.ask.criteria.thuTime] = ["thu"] : hours[this.currentAskHistory.ask.criteria.thuTime].push("thu");
        (hours[this.currentAskHistory.ask.criteria.friTime]===undefined) ? hours[this.currentAskHistory.ask.criteria.friTime] = ["fri"] : hours[this.currentAskHistory.ask.criteria.friTime].push("fri");
        (hours[this.currentAskHistory.ask.criteria.satTime]===undefined) ? hours[this.currentAskHistory.ask.criteria.satTime] = ["sat"] : hours[this.currentAskHistory.ask.criteria.satTime].push("sat");
        (hours[this.currentAskHistory.ask.criteria.sunTime]===undefined) ? hours[this.currentAskHistory.ask.criteria.sunTime] = ["sun"] : hours[this.currentAskHistory.ask.criteria.sunTime].push("sun");

        // build each schedule
        for (let hour in hours) {
          let currentSchedule = {
            outwardTime:moment(hour).format('hh:mm')
          };
          currentSchedule.mon = (hours[hour].indexOf("mon")!==-1);
          currentSchedule.tue = (hours[hour].indexOf("tue")!==-1);
          currentSchedule.wed = (hours[hour].indexOf("wed")!==-1);
          currentSchedule.thu = (hours[hour].indexOf("thu")!==-1);
          currentSchedule.fri = (hours[hour].indexOf("fri")!==-1);
          currentSchedule.sat = (hours[hour].indexOf("sat")!==-1);
          currentSchedule.sun = (hours[hour].indexOf("sun")!==-1);

          if(currentSchedule.outwardTime!=="Invalid date"){
            this.schedules.push(currentSchedule);
          }
        }
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
.window-scroll{
  max-height:600px;
  overflow:auto;
}
</style>