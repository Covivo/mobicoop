<template>
  <v-row>
    <v-col cols="6">
      <v-row>
        <v-col
          cols="12"
          class="primary--text text-left display-1 font-weight-black"
        >
          {{ $t('title.part1') }} <br> {{ $t('title.part2') }}
        </v-col>
      </v-row>
      <v-row>
        <v-col
          cols="12"
          class="text-left"
        >
          &gt;
          <a
            :href="$t('links.byCity.uri')"
            :title="$t('links.byCity.title')"
          >{{ $t('links.byCity.title') }}</a><br>
          &gt;
          <a
            :href="$t('links.popular.uri')"
            :title="$t('links.popular.title')"
          >{{ $t('links.popular.title') }}</a>
        </v-col>
      </v-row>
    </v-col>
    <v-col
      cols="6"
      class="pt-6 text-left"
    >
      <p
        v-for="(popularJourney,index) in popularJourneys"
        :key="index"
        class="ma-0 pa-0"
      >
        <a
          :href="$t('popularJourneys.uri', {origin:popularJourney.originSanitize, destination:popularJourney.destinationSanitize})"
          :title="$t('popularJourneys.from') + ' ' + popularJourney.origin + ' ' + $t('popularJourneys.to') + ' ' + popularJourney.destination"
        >{{ $t('popularJourneys.from') }} {{ popularJourney.origin }} {{ $t('popularJourneys.to') }} {{ popularJourney.destination }}</a>
      </p>
    </v-col>
  </v-row>
</template>
<script>
import axios from "axios";
import {messages_en, messages_fr} from "@translations/components/journey/JourneyCityToCity/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  data () {
    return {
      popularJourneys:null
    }
  },
  mounted(){
    this.getPopularJourneys();
  },
  methods:{
    getPopularJourneys(){
      axios.post(this.$t("getPopularJourneysUrl"))
        .then(response => {
          // console.log(response.data);
          this.popularJourneys = response.data;
        })
        .catch(function (error) {
          // console.log(error);
        })
    }
  }    
}
</script>
<style scoped lang="scss">
  a{
    text-decoration:none;
    color: rgba(0, 0, 0, 0.87) !important;
  }
</style>