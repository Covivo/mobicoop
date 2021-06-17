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
          v-if="origin!==''"
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
        {{ $tc('nbResults', journeys.length, { nb: journeys.length }) }}
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
        <v-simple-table>
          <template v-slot:default>
            <tbody>
              <tr
                v-for="journey in journeys"
                :key="origin!=='' ? journey.destination : journey.origin"
              >
                <td>{{ $t('journeyFrom') }}<span class="font-weight-bold">{{ journey.origin }}</span>{{ $t('journeyTo') }}<span class="font-weight-bold">{{ journey.destination }}</span></td>
                <td>
                  <v-btn 
                    class="float-right"
                    rounded
                    color="primary"
                    :href="$t('routeFromCityToCity', { origin: journey.originSanitized, destination: journey.destinationSanitized })"
                  >
                    {{ $t('listJourneys') }}
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/journey/JourneyResults/";

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
    }
  }
};
</script>