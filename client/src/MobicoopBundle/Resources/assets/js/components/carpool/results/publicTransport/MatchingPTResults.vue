<template>
  <!-- Matching PT results -->
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
      v-if="ptResults && ptResults.length>0"
    >
      <v-row 
        v-for="(ptResult,index) in ptResults"
        :key="index"
        justify="center"
      >
        <v-col
          cols="12"
          align="left"
        >
          <!-- Matching pt result -->
          <MatchingPTResult
            :pt-result="ptResult"
          />
        </v-col>
      </v-row>
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

import MatchingPTResult from "@components/carpool/results/publicTransport/MatchingPTResult";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/results/publicTransport/MatchingPTResults/";

export default {
  components: {
    MatchingPTResult,
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
    ptResults: {
      type: Array,
      default:function(){return []}
    },
    loadingPtResults: {
      type: Boolean,
      default: true
    }    
  },
  data(){
    return {
      loading:this.loadingPtResults
    }
  },
  watch:{
    loadingPtResults(){
      this.loading = this.loadingPtResults
    }
  },  
}
</script>