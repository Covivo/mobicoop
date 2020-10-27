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
            :carpooler-rate="carpoolerRate"
            :external-rdex-journeys="externalRdexJourneys"
            @carpool="carpool(result)"
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

import { merge } from "lodash";
import MatchingResult from "@components/carpool/results/MatchingResult";
import {messages_fr, messages_en} from "@translations/components/carpool/results/MatchingResults/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/carpool/results/MatchingResults/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  components: {
    MatchingResult,
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
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
    carpoolerRate: {
      type: Boolean,
      default: true
    },
    loadingProp: {
      type: Boolean,
      default: false
    },
    perPage: {
      type: Number,
      default:10
    }
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
    }
  }
}
</script>