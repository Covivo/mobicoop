<template>
  <v-container fluid>
    <v-row
      justify="center"
    >
      <!-- TITLE -->
      <v-col
        cols="8"
        md="8"
        xl="6"
        align="center"
      >      
        <h1>
          {{ $t('title', { origin:origin, destination: destination }) }}
        </h1>
      </v-col> 
    </v-row>
    <v-row
      justify="center"
    >
      <!-- NB RESULTS -->
      <v-col
        cols="8"
        md="8"
        xl="6"
        align="center"
      >
        {{ $tc('nbResults', total, { nb: total }) }}
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <!-- RESULTS -->
      <v-col
        cols="8"
        md="8"
        xl="6"
        align="center"
      >
        <!-- TABS HEADER -->
        <v-tabs
          v-model="frequencyTab"
          fixed-tabs
          background-color="success"
        >
          <!-- PUNCTUAL -->
          <v-tab
            key="punctual"
          >
            <v-badge
              color="grey"
              :content="journeys.punctual.length>0 ? journeys.punctual.length : '-'"
            >
              {{ $t('punctual') }}
            </v-badge>
          </v-tab>
          <!-- REGULAR -->
          <v-tab
            key="regular"
          >
            <v-badge
              color="grey"
              :content="journeys.regular.length>0 ? journeys.regular.length : '-'"
            >
              {{ $t('regular') }}
            </v-badge>
          </v-tab>
        </v-tabs>

        <!-- TABS DATA -->
        <v-tabs-items v-model="frequencyTab">
          <!-- PUNCTUAL -->
          <v-tab-item
            key="punctual"
          >
            <journey-result-punctual
              v-for="journey in journeys.punctual"
              :key="journey.id"
              class="ma-2"
              :journey="journey"
              @carpool="carpool"
            />
          </v-tab-item>
          <!-- REGULAR -->
          <v-tab-item
            key="regular"
          >
            <journey-result-regular
              v-for="journey in journeys.regular"
              :key="journey.id"
              class="ma-2"
              :journey="journey"
              @carpool="carpool"
            />
          </v-tab-item>
        </v-tabs-items>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import JourneyResultPunctual from './JourneyResultPunctual';
import JourneyResultRegular from './JourneyResultRegular';
import {messages_en, messages_fr} from "@translations/components/journey/JourneyResult/";

export default {
  components: {
    JourneyResultPunctual,
    JourneyResultRegular
  },
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    },
  },
  props: {
    journeys: {
      type: Object,
      default: () => ({
        punctual: [],
        regular: []
      })
    },
    origin: {
      type: String,
      default: ''
    },
    originSanitize: {
      type: String,
      default: ''
    },
    destination: {
      type: String,
      default: ''
    },
    destinationSanitize: {
      type: String,
      default: ''
    },
    frequency: {
      type: Number,
      default: 1
    },
    total: {
      type: Number,
      default: 0
    },
    perPage: {
      type: Number,
      default: 30
    },
    page: {
      type: Number,
      default: 1
    }
  },
  data(){
    return {
      frequencyTab: this.frequency == 1 ? (this.journeys.punctual.length>0 ? 0 : (this.journeys.regular.length>0 ? 1 : 0)) : (this.journeys.regular.length>0 ? 1 : 0),
      lPage:this.page
    }
  },
  methods:{
    carpool(event){
      if(undefined !== event.proposalId){
        console.log(event.proposalId);
      }
    },
    loginOrRegister(carpool){
      this.$emit("loginOrRegister", carpool);
    },
    paginate(page){
      if (this.origin !== '' && this.destination !== '') {
        window.location.href = this.$t('routeFromCityToCity', { origin: this.originSanitize, destination: this.destinationSanitize });
      } else if (this.origin !== '') {
        window.location.href = this.$t('routeFromCity', { city: this.originSanitize, page: this.lPage });
      } else if (this.destination !== '') {
        window.location.href = this.$t('routeToCity', { city: this.destinationSanitize, page: this.lPage });
      }
    }
  }
};
</script>