<template>
  <v-content>
    <v-card
      class="pa-2 text-center"
    >
      <!-- Always visible (carpool or not) -->
      <v-avatar v-if="infos.avatar && !loading">
        <img :src="infos.avatar">
      </v-avatar>
      <v-card-text
        v-if="!loading"
        class="font-weight-bold headline"
      >
        {{ infos.contactName }}
      </v-card-text>

      <!-- Only visible for carpool -->
      <v-card
        v-if="idAsk && !loading"
        class="mb-3"
        flat
      >
        <v-chip
          v-if="infos.roundTrip"
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
          v-if="infos.frequency==2" 
          :mon-active="infos.regular.days.monCheck"
          :tue-active="infos.regular.days.tueCheck"
          :wed-active="infos.regular.days.wedCheck"
          :thu-active="infos.regular.days.thuCheck"
          :fri-active="infos.regular.days.friCheck"
          :sat-active="infos.regular.days.satCheck"
          :sun-active="infos.regular.days.sunCheck"
        />

        <v-journey
          :waypoints="infos.waypoints"
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
                {{ infos.rounded_price }} €
              </td>
            </tr>
          </tbody>
        </v-simple-table>
        <threads-actions-buttons
          :requester="infos.requester"
          :status="infos.status"
          :regular="infos.frequency==2"
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
      v-if="infos.frequency==2"
      v-model="dialogRegular"
    >
      <v-card>
        <v-toolbar
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
        </v-toolbar>
        <regular-ask
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
import RegularAsk from '@components/carpool/utilities/RegularAsk'
import axios from "axios";

export default {
  i18n: {
    messages: Translations,
  },
  components:{
    ThreadsActionsButtons,
    RegularDaysSummary,
    VJourney,
    RegularAsk
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
      infos:[],
      driver:false,
      passenger:false,
      dialogRegular:false
    }
  },
  computed:{
    distanceInKm(){
      return parseInt(this.infos.distance) / 1000 + 'km';
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
          // If the user can be driver and passenger, we display driver infos by default
          if(response.data.driver !== undefined && response.data.passenger !== undefined){
            this.infos = response.data.driver;
            this.driver = this.passenger = true;
          }
          else if(response.data.passenger !== undefined){
            this.infos = response.data.passenger;
            this.driver = false;
            this.passenger = true;
          }
          else{
            this.infos = response.data.driver;
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
      if(this.infos.status==1 && this.infos.frequency==2){
        // If the Ask is only initiated and that the carpool is regular
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