<template>
  <v-container>
    <v-row class="justify-center">
      <v-col cols="10">
        <v-row
          align="center"
          justify="center"
        >
          <!-- TITLE -->
          <v-col
            align="center"
          >
            <h1>{{ $t('title') }}</h1>
            <h2>{{ $t('subtitle') }}</h2>
          </v-col> 
        </v-row>
        <v-row>
          <v-col
            class="text-left"
          >
            {{ $t('intro') }} <a
              :href="$t('links.cityByCity.uri')"
              :alt="$t('links.cityByCity.label')"
            >{{ $t('links.cityByCity.label') }}</a>.
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-row>
              <v-col class="headline">
                {{ $t('popularJourneys') }}
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <p
                  v-for="(popularJourney,index) in popularJourneys"
                  :key="index"
                  class="ma-0 pa-0"
                >
                  <a
                    :href="$t('links.popularJourneys.uri', {origin:popularJourney.originSanitize, destination:popularJourney.destinationSanitize})"
                    :title="$t('links.popularJourneys.from') + ' ' + popularJourney.origin + ' ' + $t('links.popularJourneys.to') + ' ' + popularJourney.destination"
                  >{{ $t('links.popularJourneys.from') }} {{ popularJourney.origin }} {{ $t('links.popularJourneys.to') }} {{ popularJourney.destination }}</a>
                </p>                
              </v-col>            
            </v-row>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>     
</template>

<script>
import axios from "axios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/journey/JourneyCityPopular/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props: {
  },
  data () {
    return {
      popularJourneys:null
    };
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
};
</script>
<style scoped lang="scss">
  a{
    text-decoration:none;
    color: rgba(0, 0, 0, 0.87) !important;
    &:hover{
      text-decoration: underline !important;
    }
  }
</style>