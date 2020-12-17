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
        <h1
          v-if="origin!=='' && destination !==''"
        >
          {{ $t('titleFromCityToCity', { origin:origin, destination: destination }) }}
        </h1>
        <h1
          v-else-if="origin!==''"
        >
          {{ $t('titleFromCity', { city: origin }) }}
        </h1>
        <h1
          v-else-if="destination!==''"
        >
          {{ $t('titleToCity', { city: destination }) }}
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
        <v-pagination
          v-if="total>perPage"
          v-model="lPage"
          :length="Math.ceil(total/perPage)"
          @input="paginate(lPage)"
        />
        <v-row 
          v-for="(journey,index) in journeys"
          :key="index"
          justify="center"
        >
          <v-col
            cols="12"
            align="left"
          >
            {{ journey.origin }} - {{ journey.destination }}
          </v-col>
        </v-row>
        <v-pagination
          v-if="total>perPage"
          v-model="lPage"
          :length="Math.ceil(total/perPage)"
          @input="paginate(lPage)"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {messages_en, messages_fr} from "@translations/components/journey/JourneyResults/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    },
  },
  props: {
    journeys: {
      type: Array,
      default: () => []
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
      lPage:this.page
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
      if (this.origin !== '' && this.destination !== '') {
        window.location.href = this.$t('routeFromCityToCity', { origin: this.originSanitize, destination: this.destinationSanitize, page: this.lPage });
      } else if (this.origin !== '') {
        window.location.href = this.$t('routeFromCity', { city: this.originSanitize, page: this.lPage });
      } else if (this.destination !== '') {
        window.location.href = this.$t('routeToCity', { city: this.destinationSanitize, page: this.lPage });
      }
    }
  }
};
</script>