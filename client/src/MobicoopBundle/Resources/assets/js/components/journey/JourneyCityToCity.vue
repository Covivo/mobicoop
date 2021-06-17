<template>
  <v-row>
    <v-col cols="6">
      <v-row>
        <v-col
          cols="12"
          class="white--text text-left ml-md-14"
        >
          <h3 class="font-weight-bold">
            {{ $t('title.part1') }} <br> {{ $t('title.part2') }}<h2 />
          </h3>
        </v-col>
      </v-row>
      <v-row>
        <v-col
          cols="12"
          class="text-left white--text ml-md-14"
        >
          &gt;
          <a
            :href="$t('links.byCity.uri')"
            :title="$t('links.byCity.title')"
            class="white--text"
          >{{ $t('links.byCity.title') }}</a><br>
          &gt;
          <a
            :href="$t('links.popular.uri')"
            :title="$t('links.popular.title')"
            class="white--text"
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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/journey/JourneyCityToCity/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  data () {
    return {
      popularJourneys:"cc"
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
    color: white !important;
  }
h3{
    font-size: 1.8rem;
    line-height: 1.2;
  }

</style>