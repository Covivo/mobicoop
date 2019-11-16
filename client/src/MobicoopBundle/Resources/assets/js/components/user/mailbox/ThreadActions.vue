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
        <v-chip class="secondary mb-4">
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
          :mon-active="infos.days.monCheck"
          :tue-active="infos.days.tueCheck"
          :wed-active="infos.days.wedCheck"
          :thu-active="infos.days.thuCheck"
          :fri-active="infos.days.friCheck"
          :sat-active="infos.days.satCheck"
          :sun-active="infos.days.sunCheck"
        />

        <v-journey :waypoints="infos.waypoints" />
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
          :user-id="idUser"
          :requester-id="infos.requester"
          :status="infos.status"
          :regular="infos.frequency==2"
          :loading-btn="dataLoadingBtn"
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
  </v-content>
</template>
<script>
import Translations from "@translations/components/user/mailbox/ThreadActions.json";
import ThreadsActionsButtons from '@components/user/mailbox/ThreadsActionsButtons'
import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary'
import VJourney from '@components/carpool/utilities/VJourney'
import axios from "axios";

export default {
  i18n: {
    messages: Translations,
  },
  components:{
    ThreadsActionsButtons,
    RegularDaysSummary,
    VJourney
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
      infos:[]
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
          this.infos = response.data;
        })
        .catch(function (error) {
          console.log(error);
        })
        .finally(()=>{
          this.$emit("refreshActionsCompleted");
        });
    },
    updateStatus(data){
      this.$emit("updateStatusAskHistory",data);
    }
  }
}
</script>