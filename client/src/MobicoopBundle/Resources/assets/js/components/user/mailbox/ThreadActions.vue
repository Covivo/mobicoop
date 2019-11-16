<template>
  <v-content>
    <v-card
      class="pa-2 text-center"
    >
      <!-- Always visible (carpool or not) -->
      <v-avatar v-if="infosFromAPI.carpooler && (infosFromAPI.carpooler.avatars || infos.avatar) && !loading">
        <img :src="infosFromAPI.carpooler ? infosFromAPI.carpooler.avatars[0] : infos.avatar">
      </v-avatar>
      <v-card-text
        v-if="!loading"
        class="font-weight-bold headline"
      >
        {{ infosFromAPI.carpooler ? infosFromAPI.carpooler.givenName+' '+infosFromAPI.carpooler.shortFamilyName : infos.contactName }}
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
          v-if="infosFromAPI.frequency==2" 
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
                Distance
              </td>
              <td class="text-left">
                {{ distanceInKm }}
              </td>
            </tr>
            <tr>
              <td class="text-left">
                Places disponibles
              </td>
              <td class="text-left">
                {{ infos.seats }}
              </td>
            </tr>
            <tr>
              <td class="text-left font-weight-bold">
                Prix
              </td>
              <td class="text-left font-weight-bold">
                {{ infosFromAPI.roundedPrice }} €
              </td>
            </tr>
          </tbody>
        </v-simple-table>
        <threads-actions-buttons
          :can-ask="infosFromAPI.canAsk"
          :status="infosFromAPI.status"
          :regular="infosFromAPI.frequency==2"
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
        <!-- <v-toolbar
          flat
          color="primary"
        >
          <v-toolbar-title>{{ $t('regular.ask') }}</v-toolbar-title>
          <v-spacer />
          
          <v-btn
            icon
            @click="dialogRegular = false"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar> -->
        <!-- <regular-ask
          :type="infos.type"
          :origin-driver="infos.regular.originDriver"
          :destination-driver="infos.regular.destinationDriver"
          :origin-passenger="infos.regular.originPassenger"
          :destination-passenger="infos.regular.destinationPassenger"
          :from-date="infos.regular.fromDate"
          :max-date="infos.regular.maxDate"
          :mon-check-default="infos.regular.days.monCheck"
          :mon-time="infos.regular.days.monTime"
          :tue-check-default="infos.regular.days.tueCheck"
          :tue-time="infos.regular.days.tueTime"
          :wed-check-default="infos.regular.days.wedCheck"
          :wed-time="infos.regular.days.wedTime"
          :thu-check-default="infos.regular.days.thuCheck"
          :thu-time="infos.regular.days.thuTime"
          :fri-check-default="infos.regular.days.friCheck"
          :fri-time="infos.regular.days.friTime"
          :sat-check-default="infos.regular.days.satCheck"
          :sat-time="infos.regular.days.satTime"
          :sun-check-default="infos.regular.days.sunCheck"
          :sun-time="infos.regular.days.sunTime"
        /> -->
        <matching-journey
          :result="infosFromAPI"
          @close="dialogRegular=false"
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
//import RegularAsk from '@components/carpool/utilities/RegularAsk'
import MatchingJourney from '@components/carpool/results/MatchingJourney'
import axios from "axios";

export default {
  i18n: {
    messages: Translations,
  },
  components:{
    ThreadsActionsButtons,
    RegularDaysSummary,
    VJourney,
    //RegularAsk,
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
      infosFromAPI:[],
      infos:[],
      driver:false,
      passenger:false,
      dialogRegular:false
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
          console.error(response.data);
          this.infosFromAPI = response.data;

          // If the user can be driver and passenger, we display driver infos by default
          if(this.infosFromAPI.resultDriver !== undefined && this.infosFromAPI.resultPassenger !== undefined){
            this.infos = this.infosFromAPI.resultDriver;
            this.driver = this.passenger = true;
          }
          else if(this.infosFromAPI.resultPassenger !== undefined){
            this.infos = this.infosFromAPI.resultPassenger;
            this.driver = false;
            this.passenger = true;
          }
          else{
            this.infos = this.infosFromAPI.resultDriver;
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
    updateStatus(data){
      if(this.infosFromAPI.status==1 && this.infosFromAPI.frequency==2){
        // If the Ask is only initiated and that the carpool is regular

        // If the user can be driver and passenger, we swich the right infos
        // if(this.driver && this.passenger){
        //   data.role=="driver" ? this.infos = this.infosFromAPI.driver : this.infos = this.infosFromAPI.passenger
        // }
        console.error("regular");
        this.dialogRegular = true;
      }
      else{
        this.dataLoadingBtn = true;
        this.$emit("updateStatusAskHistory",data);
      }
    }
  }
}
</script>