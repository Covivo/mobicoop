<template>
  <!-- Matching results -->
  <div v-if="loading">
    <v-row
      v-for="n in 3"
      :key="n"
      class="text-left"
    >
      <v-col cols="12">
        <v-skeleton-loader
          ref="skeleton"
          type="article"
          class="mx-auto"
        />
        <v-skeleton-loader
          ref="skeleton"
          type="actions"
          class="mx-auto"
        />
      </v-col>
    </v-row>
  </div>
  <div v-else>
    <div
      v-if="results.length>0"
    >
      <v-pagination
        v-if="nbResults>perPage"
        v-model="lPage"
        :length="Math.ceil(nbResults/perPage)"
        @input="paginate(lPage)"
      />
      <v-row
        v-for="(result,index) in results"
        :key="index"
        justify="center"
      >
        <v-col
          cols="12"
          align="left"
        >
          <!-- Matching result -->
          <matching-result
            :result="result"
            :user="user"
            :distinguish-regular="distinguishRegular"
            :external-rdex-journeys="externalRdexJourneys"
            :age-display="ageDisplay"
            :birthdate-display="birthdateDisplay"
            :carpool-standard-booking-enabled="carpoolStandardBookingEnabled"
            :carpool-standard-messaging-enabled="carpoolStandardMessagingEnabled"
            @carpool="carpool(result)"
            @booking="booking(result)"
            @loginOrRegister="loginOrRegister(result)"
          />
        </v-col>
      </v-row>
      <v-pagination
        v-if="nbResults>perPage"
        v-model="lPage"
        :length="Math.ceil(nbResults/perPage)"
        @input="paginate(lPage)"
      />
    </div>
    <div v-else>
      <v-col
        cols="12"
        align="left"
      >
        {{ $t('noResult') }}
      </v-col>
    </div>
  </div>
</template>
<script>

import MatchingResult from "@components/carpool/results/MatchingResult";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/results/MatchingResults/";

export default {
  components: {
    MatchingResult,
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props:{
    externalRdexJourneys:{
      type: Boolean,
      default: false
    },
    user: {
      type:Object,
      default: null
    },
    results:{
      type:Array,
      default:null
    },
    nbResults:{
      type:Number,
      default:0
    },
    page:{
      type:Number,
      default:1
    },
    distinguishRegular: {
      type: Boolean,
      default: false
    },
    loadingProp: {
      type: Boolean,
      default: false
    },
    perPage: {
      type: Number,
      default:10
    },
    ageDisplay: {
      type: Boolean,
      default: false
    },
    birthdateDisplay: {
      type: Boolean,
      default: false
    },
    carpoolStandardBookingEnabled: {
      type: Boolean,
      default: false
    },
    carpoolStandardMessagingEnabled: {
      type: Boolean,
      default: false
    },
  },
  data(){
    return {
      loading:this.loadingProp,
      lPage:this.page
    }
  },
  watch:{
    page(){
      this.lPage = this.page;
    },
    loadingProp(){
      this.loading = this.loadingProp
    }
  },
  methods:{
    carpool(carpool){
      this.$emit("carpool", carpool);
    },
    loginOrRegister(carpool){
      this.$emit("loginOrRegister", carpool);
    },
    paginate(page){
      this.$emit("paginate", page)
    },
    booking(booking){
      this.$emit("booking", booking);
    },
  }
}
</script>
