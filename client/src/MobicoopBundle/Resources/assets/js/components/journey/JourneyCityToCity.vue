<template>
  <v-row>
    <v-col cols="6">
      <v-row>
        <v-col
          cols="12"
          class="text-left display-1 primary--text"
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
          >{{ $t('links.byCity.title') }}</a>
        </v-col>
      </v-row>
    </v-col>
    <v-col
      cols="6"
      class="pt-6"
    >
      <p
        v-for="(popularJourney,index) in popularJourneys"
        :key="index"
        class="ma-0 pa-0"
      >
        <a
          :href="popularJourney.uri"
          :title="popularJourney.label"
        >{{ popularJourney.label }}</a>
      </p>
    </v-col>
  </v-row>
</template>
<script>
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
      this.popularJourneys = {
        0:{uri:'/covoiturage/rennes/grenoble',label:'Covoiturage de Rennes à Grenoble'},
        1:{uri:'/covoiturage/rennes/grenoble',label:'Covoiturage de Rennes à Grenoble'},
        2:{uri:'/covoiturage/rennes/grenoble',label:'Covoiturage de Rennes à Grenoble'},
        3:{uri:'/covoiturage/rennes/grenoble',label:'Covoiturage de Rennes à Grenoble'},
        4:{uri:'/covoiturage/rennes/grenoble',label:'Covoiturage de Rennes à Grenoble'}
      }
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