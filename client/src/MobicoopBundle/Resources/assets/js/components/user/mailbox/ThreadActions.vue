<template>
  <v-content>
    <v-card
      class="pa-2 text-center"
    >
      <!-- Always visible (carpool or not) -->
      <v-avatar v-if="infosComplete.carpooler && (infosComplete.carpooler.avatars || infos.avatar) && !loading">
        <img :src="infosComplete.carpooler ? infosComplete.carpooler.avatars[0] : infos.avatar">
      </v-avatar>
      <v-card-text
        v-if="!loading"
        class="font-weight-bold headline"
      >
        {{ infosComplete.carpooler ? infosComplete.carpooler.givenName+' '+infosComplete.carpooler.shortFamilyName : infos.contactName }}
      </v-card-text>

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
          :waypoints="infos.outward.waypoints"
          :time="true"
          :role="driver ? 'driver' : 'passenger'"
        />
        <v-simple-table>
          <tbody>
            <tr>
              <td class="text-left">
                {{ $t('distance') }}
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
                {{ infos.seats }}
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
        <threads-actions-buttons
          :can-ask="infosComplete.canAsk"
          :status="infosComplete.status"
          :regular="infosComplete.frequency==2"
          :loading-btn="dataLoadingBtn"
          :driver="driver"
          :passenger="passenger"
          @updateStatus="updateStatus"
        />
      </v-card>
      <v-card v-else-if="!loading">
        <v-card-text>
          {{ $t("notLinkedToACarpool") }}
        </v-card-text>
      </v-card>
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
          @close="dialogRegular=false"
          @carpool="carpoolFromMatchingJourney"
        />
      </v-card>
    </v-dialog>
  </v-content>
</template>
<script>
import Translations from "@translations/components/user/mailbox/ThreadActions.json";
import ThreadsActionsButtons from '@components/user/mailbox/ThreadsActionsButtons'
import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary'
import VJourney from '@components/carpool/utilities/VJourney'
import MatchingJourney from '@components/carpool/results/MatchingJourney'
import axios from "axios";
import moment from "moment";

export default {
  i18n: {
    messages: Translations,
  },
  components:{
    ThreadsActionsButtons,
    RegularDaysSummary,
    VJourney,
    MatchingJourney
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
    }
  },
  data(){
    return{
      loading:this.loadingInit,
      recipientName:"",
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
      chosenRole:null
    }
  },
  computed:{
    distanceInKm(){
      return (this.driver) ? parseInt(this.infos.outward.newDistance) / 1000 + ' km' : parseInt(this.infos.outward.originalDistance) / 1000 + ' km';
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
    }
  },
  methods:{
    refreshInfos(){
      this.loading = true;
      let params = {
        idAsk:this.idAsk,
        idRecipient:this.idRecipient
      }
      axios.post(this.$t("urlGetAskHistory"),params)
        .then(response => {
          //console.error(response.data);
          this.infosComplete = response.data;

          // If the user can be driver and passenger, we display driver infos by default
          if(this.infosComplete.resultDriver !== undefined && this.infosComplete.resultPassenger !== undefined){
            this.infos = this.infosComplete.resultDriver;
            this.driver = this.passenger = true;
          }
          else if(this.infosComplete.resultPassenger !== undefined){
            this.infos = this.infosComplete.resultPassenger;
            this.driver = false;
            this.passenger = true;
          }
          else{
            this.infos = this.infosComplete.resultDriver;
            this.driver = true;
            this.passenger = false;
          }
        })
        .catch(function (error) {
          console.log(error);
        })
        .finally(()=>{
          this.$emit("refreshActionsCompleted");
        });
    },
    formatHour(date){
      return moment(date).format("HH")+'h'+moment(date).format("mm")
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
      if(this.infosComplete.status==1 && this.infosComplete.frequency==2){
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
    }
  }
}
</script>