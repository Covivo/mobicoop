<template>
  <v-main>
    <v-card
      class="pa-2 text-center"
      :hidden="hideClickIcon"
    >
      <v-card
        v-if="idAsk"
        class="mb-3"
        flat
      >
        <threads-actions-buttons
          :can-update-ask="infosComplete.canUpdateAsk && dataBlockerId==null"
          :status="infosComplete.askStatus"
          :regular="infosComplete.frequency==2"
          :loading-btn="dataLoadingBtn"
          :driver="driver"
          :passenger="passenger"
          :carpool-context="(idAsk) ? true : false"
          @updateStatus="updateStatus"
        />
      </v-card>
       
      <!-- Always visible (carpool or not) -->
      <v-avatar v-if="!loading && ((infosComplete.carpooler && infosComplete.carpooler.avatars && infosComplete.carpooler.status != 3) || recipientAvatar)">
        <img :src="(recipientAvatar) ? recipientAvatar : infosComplete.carpooler.avatars[0]">
      </v-avatar>
      <v-card-text
        v-if="!loading && ((infosComplete.carpooler && infosComplete.carpooler.status != 3) || recipientName)"
        class="font-weight-bold text-h5"
      >
        {{ buildedRecipientName }}
      </v-card-text>
      <v-card-text
        v-if="infosComplete.carpooler && infosComplete.carpooler.status == 3"
        class="font-weight-bold text-h5"
      >
        {{ $t("userDelete") }}
      </v-card-text>

      <v-row dense>
        <v-col
          cols="6"
          class="text-right align-center"
        >
          <div v-if="infosComplete.carpooler && !loading">
            <v-btn
              v-if="dataBlockerId==null"
              class="ma-2"
              rounded
              text
              color="error"
              :loading="loadingBlock"
              @click="block"
            >
              <v-icon left>
                mdi-account-cancel-outline
              </v-icon>
              {{ $t('block') }}
            </v-btn>
            <v-btn
              v-else
              class="ma-2"
              rounded
              color="error"
              :loading="loadingBlock"
              @click="block"
            >
              <v-icon left>
                mdi-account-cancel
              </v-icon> {{ $t('blocked') }}
            </v-btn>        
          </div>
        </v-col>
        <v-col
          cols="6"
          class="text-left align-center"
        >
          <div
            v-if="idRecipient"
            class="pa-2"
          >
            <Report
              :user-id="idRecipient"
              :default-email="emailUser"
            />
          </div>
        </v-col>
      </v-row>
      <!-- Only visible for carpool -->
      <v-card
        v-if="idAsk && !loading"
        class="mb-3"
        flat
      >
        <v-chip
          v-if="infos.return"
          class="secondary mb-4"
        >
          <v-icon
            left
            color="white"
          >
            mdi-swap-horizontal
          </v-icon>
          {{ $t('roundTrip') }}
        </v-chip>

        <regular-days-summary
          v-if="infosComplete.frequency==2" 
          :mon-active="infos.outward.monCheck"
          :tue-active="infos.outward.tueCheck"
          :wed-active="infos.outward.wedCheck"
          :thu-active="infos.outward.thuCheck"
          :fri-active="infos.outward.friCheck"
          :sat-active="infos.outward.satCheck"
          :sun-active="infos.outward.sunCheck"
        />

        <v-journey
          :waypoints="infos.outward && infos.outward.waypoints"
          :time="infos.outward && !infos.outward.multipleTimes"
          :role="driver ? 'driver' : 'passenger'"
        />
        <v-simple-table v-if="infosComplete.carpooler && infosComplete.carpooler.status != 3">
          <tbody>
            <tr>
              <td class="text-left">
                {{ $t('distance') }}
                <v-tooltip
                  slot="append"
                  right
                  color="info"
                  :max-width="'35%'"
                >
                  <template v-slot:activator="{ on }">
                    <v-icon
                      justify="left"
                      v-on="on"
                    >
                      mdi-help-circle-outline
                    </v-icon>
                  </template>
                  <span>{{ $t('distanceTooltip') }}</span>
                </v-tooltip>                
              </td>
              <td class="text-left">
                {{ distanceInKm }}
              </td>
            </tr>
            <tr>
              <td class="text-left">
                {{ $t('seatsAvailable') }}
              </td>
              <td class="text-left">
                {{ infosComplete.seats }}
              </td>
            </tr>
            <tr>
              <td class="text-left font-weight-bold">
                {{ $t('price') }}
                <v-tooltip
                  slot="append"
                  right
                  color="info"
                  :max-width="'35%'"
                >
                  <template v-slot:activator="{ on }">
                    <v-icon
                      justify="left"
                      v-on="on"
                    >
                      mdi-help-circle-outline
                    </v-icon>
                  </template>
                  <span>{{ $t('priceTooltip') }}</span>
                </v-tooltip>                
              </td>
              <td class="text-left font-weight-bold">
                {{ infosComplete.roundedPrice }} €
              </td>
            </tr>
          </tbody>
        </v-simple-table>
      </v-card>
      <!-- <v-card v-else-if="!loading">
        <v-card-text>
          {{ $t("notLinkedToACarpool") }}
        </v-card-text>
      </v-card> -->
      <v-skeleton-loader
        v-if="loading"
        ref="skeleton"
        type="card"
        class="mx-auto"
      />
      <v-skeleton-loader
        v-if="loading"
        ref="skeleton"
        type="actions"
        class="mx-auto"
      />
    </v-card>




    <!-- Modal to propose a carpool -->
    <v-dialog
      v-model="dialogRegular"
    >
      <v-card>
        <matching-journey
          :result="infosComplete"
          :default-step="2"
          :default-outward-mon-time="outwardMonTime"
          :default-outward-tue-time="outwardTueTime"
          :default-outward-wed-time="outwardWedTime"
          :default-outward-thu-time="outwardThuTime"
          :default-outward-fri-time="outwardFriTime"
          :default-outward-sat-time="outwardSatTime"
          :default-outward-sun-time="outwardSunTime"
          :default-return-mon-time="returnMonTime"
          :default-return-tue-time="returnTueTime"
          :default-return-wed-time="returnWedTime"
          :default-return-thu-time="returnThuTime"
          :default-return-fri-time="returnFriTime"
          :default-return-sat-time="returnSatTime"
          :default-return-sun-time="returnSunTime"
          :default-outward-trip="outwardTrip"
          :default-return-trip="returnTrip"
          :default-role="chosenRole"
          :hide-contact="true"
          @close="dialogRegular=false"
          @carpool="carpoolFromMatchingJourney"
        />
      </v-card>
    </v-dialog>
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu} from "@translations/components/user/mailbox/ThreadActions/";
import ThreadsActionsButtons from '@components/user/mailbox/ThreadsActionsButtons';
import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary';
import VJourney from '@components/carpool/utilities/VJourney';
import MatchingJourney from '@components/carpool/results/MatchingJourney';
import Report from "@components/utilities/Report";
import axios from "axios";
import moment from "moment";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components:{
    ThreadsActionsButtons,
    RegularDaysSummary,
    VJourney,
    MatchingJourney,
    Report
  },
  props: {
    idAsk: {
      type: Number,
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
    idRecipient: {
      type: Number,
      default: null
    },
    loadingInit: {
      type: Boolean,
      default: false
    },
    refresh: {
      type: Boolean,
      default: false
    },
    loadingBtn: {
      type: Boolean,
      default: false
    },
    recipientName: {
      type: String,
      default: null
    },
    recipientAvatar: {
      type: String,
      default: null
    },
    blockerId: {
      type: Number,
      default: null
    }
  },
  data(){
    return{
      locale: this.$i18n.locale,
      loading:this.loadingInit,
      dataLoadingBtn:this.loadingBtn,
      infosComplete:[],
      infos:[],
      driver:false,
      passenger:false,
      dialogRegular:false,
      outwardMonTime: null,
      outwardTueTime: null,
      outwardWedTime: null,
      outwardThuTime: null,
      outwardFriTime: null,
      outwardSatTime: null,
      outwardSunTime: null,
      returnMonTime: null,
      returnTueTime: null,
      returnWedTime: null,
      returnThuTime: null,
      returnFriTime: null,
      returnSatTime: null,
      returnSunTime: null,
      outwardTrip:[],
      returnTrip:[],
      chosenRole:null,
      hideClickIcon : false,
      loadingBlock: false,
      dataBlockerId: this.blockerId
    }
  },
  computed:{
    distanceInKm(){
      return Math.round((this.infos.outward.commonDistance + this.infos.outward.detourDistance) / 1000) + ' km';
    },
    buildedRecipientName(){
      if(this.recipientName){
        return this.recipientName
      }
      else{
        return this.infosComplete.carpooler.givenName+' '+this.infosComplete.carpooler.shortFamilyName
      }
    }
  },
  watch:{
    loadingInit(){
      this.loading = this.loadingInit;
    },
    refresh(){
      (this.refresh) ? this.refreshInfos() : this.loading = false;
    },
    loadingBtn(){
      this.dataLoadingBtn = this.loadingBtn;
    },
    blockerId(){
      this.dataBlockerId = this.blockerId;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods:{
    refreshInfos() {
      this.hideClickIcon = false;
      if (this.idAsk != -2){
        this.loading = true;
        if(this.idAsk){
          let params = {
            idAsk: this.idAsk,
            idRecipient: this.idRecipient
          }
          axios.post(this.$t("urlGetAdAsk"), params)
            .then(response => {
              this.infosComplete = response.data;

              // If the user can be driver and passenger, we display driver infos by default
              if (this.infosComplete.resultDriver !== null && this.infosComplete.resultPassenger !== null) {
                this.infos = this.infosComplete.resultDriver;
                this.driver = this.passenger = true;
              } else if (this.infosComplete.resultPassenger !== null && this.infosComplete.resultDriver === null) {
                this.infos = this.infosComplete.resultPassenger;
                this.driver = false;
                this.passenger = true;
              } else {
                this.infos = this.infosComplete.resultDriver;
                this.driver = true;
                this.passenger = false;
              }
            })
            .catch(function (error) {
            // console.log(error);
            })
            .finally(() => {
              this.$emit("refreshActionsCompleted");
            });
        }
        else{
          this.$emit("refreshActionsCompleted");
        }

      }else{
        this.hideClickIcon = true;
        this.$emit("refreshActionsCompleted");
      }
    },
    formatHour(date){
      return moment.utc(date).format("HH")+'h'+moment.utc(date).format("mm")
    },
    formatArrayForRegular(results,direction){
      let currentTrip = null;
      (direction=="outward") ? currentTrip = this.outwardTrip : currentTrip = this.returnTrip;

      if(results.monCheck){
        currentTrip.push({
          "day": "mon",
          "time": this.formatHour(results.monTime),
          "min": results.fromDate,
          "max": results.fromDate
        });
      }
      if(results.tueCheck){
        currentTrip.push({
          "day": "tue",
          "time": this.formatHour(results.tueTime),
          "min": results.fromDate,
          "max": results.fromDate
        });
      }
      if(results.wedCheck){
        currentTrip.push({
          "day": "wed",
          "time": this.formatHour(results.wedTime),
          "min": results.fromDate,
          "max": results.fromDate
        });
      }
      if(results.thuCheck){
        currentTrip.push({
          "day": "thu",
          "time": this.formatHour(results.thuTime),
          "min": results.fromDate,
          "max": results.fromDate
        });
      }
      if(results.friCheck){
        currentTrip.push({
          "day": "fri",
          "time": this.formatHour(results.friTime),
          "min": results.fromDate,
          "max": results.fromDate
        });
      }
      if(results.satCheck){
        currentTrip.push({
          "day": "sat",
          "time": this.formatHour(results.satTime),
          "min": results.fromDate,
          "max": results.fromDate
        });
      }
      if(results.sunCheck){
        currentTrip.push({
          "day": "sun",
          "time": this.formatHour(results.sunTime),
          "min": results.fromDate,
          "max": results.fromDate
        });
      }


    },
    updateStatus(data){
      this.dataLoadingBtn = true;
      // console.info(this.infosComplete)
      // console.info(this.infosComplete.carpooler)
      if(this.infosComplete.askStatus==1 && this.infosComplete.frequency==2){
        // If the Ask is only initiated and that the carpool is regular

        let results = null;
        this.chosenRole = data.role; // The chosen role to init MatchingJourney

        // We build the params to prefill MathingJourney

        if(data.role=='driver'){
          results = this.infosComplete.resultDriver;
        }
        else{
          results = this.infosComplete.resultPassenger;
        }
        
        // Outward parameters (checkbox and time)
        if(results.outward){
          // Times day by day
          if(results.outward.monTime) this.outwardMonTime = this.formatHour(results.outward.monTime);
          if(results.outward.tueTime) this.outwardTueTime = this.formatHour(results.outward.tueTime);
          if(results.outward.wedTime) this.outwardWedTime = this.formatHour(results.outward.wedTime);
          if(results.outward.thuTime) this.outwardThuTime = this.formatHour(results.outward.thuTime);
          if(results.outward.friTime) this.outwardFriTime = this.formatHour(results.outward.friTime);
          if(results.outward.satTime) this.outwardSatTime = this.formatHour(results.outward.satTime);
          if(results.outward.sunTime) this.outwardSunTime = this.formatHour(results.outward.sunTime);

          // For the checkboxes day by day
          this.formatArrayForRegular(results.outward,"outward");
        }

        // Return parameters (checkbox and time)
        if(results.return){
          // Times day by day
          if(results.return.monTime) this.returnMonTime = this.formatHour(results.return.monTime);
          if(results.return.tueTime) this.returnTueTime = this.formatHour(results.return.tueTime);
          if(results.return.wedTime) this.returnWedTime = this.formatHour(results.return.wedTime);
          if(results.return.thuTime) this.returnThuTime = this.formatHour(results.return.thuTime);
          if(results.return.friTime) this.returnFriTime = this.formatHour(results.return.friTime);
          if(results.return.satTime) this.returnSatTime = this.formatHour(results.return.satTime);
          if(results.return.sunTime) this.returnSunTime = this.formatHour(results.return.sunTime);

          // For the checkboxes day by day
          this.formatArrayForRegular(results.outward,"return");
        }

        this.dialogRegular = true;
      }
      else{
        this.dataLoadingBtn = true;
        this.$emit("updateStatusAskHistory",data);
      }
    },
    carpoolFromMatchingJourney(data){
      this.dialogRegular = false;
      this.$emit("updateStatusAskHistory",data);
    },
    block(){
      
      if( (this.dataBlockerId==null) || (this.dataBlockerId == this.idUser)){
        this.loadingBlock = true;
        let params = {
          "blockedUserId":this.idRecipient
        }
        axios.post(this.$t("blockUrl"), params)
          .then(response => {
            if(this.dataBlockerId == null){
              this.dataBlockerId = this.idUser;
            }
            else{
              this.dataBlockerId = null;
            }
          })
          .catch(function (error) {
            // console.log(error);
          })
          .finally(() => {
            this.loadingBlock = false;
          });      
      }
    }

  }
}
</script>