<template>
  <div>
    <v-card
      color="grey lighten-4"
    >
      <v-card-title
        class="text-center"
      >
        {{ $t('title') }}
      </v-card-title>
      <v-card-text class="text-center">
        <h2
          class="mb-4"
        >
          {{ $t('followup.subtitle') }}
        </h2>
        <v-expansion-panels
          v-model="panel"
          multiple
          flat
        >
          <v-expansion-panel>
            <v-expansion-panel-header>
              {{ $t('followup.longDistance.header') }}
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-progress-linear
                v-model="longDistanceProgress"
                height="25"
              >
                <strong>{{ $t('followup.longDistance.progress', {'nb':longDistanceProgress}) }}</strong>
              </v-progress-linear>
              <p>
                <v-row>
                  <v-col
                    cols="1"
                    class="text-right"
                  >
                    <v-icon>mdi-information</v-icon>
                  </v-col>
                  <v-col class="text-left">
                    <ul>
                      <li>{{ $t('followup.longDistance.hints.hint1') }}</li>
                      <li>{{ $t('followup.longDistance.hints.hint2') }}</li>
                    </ul>
                  </v-col>
                </v-row>
              </p>
            </v-expansion-panel-content>
          </v-expansion-panel>
          <v-expansion-panel>
            <v-expansion-panel-header>
              {{ $t('followup.shortDistance.header') }}
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-progress-linear
                v-model="shortDistanceProgress"
                height="25"
              >
                <strong>{{ $t('followup.shortDistance.progress', {'nb':shortDistanceProgress}) }}</strong>
              </v-progress-linear>
              <p>
                <v-row>
                  <v-col
                    cols="1"
                    class="text-right"
                  >
                    <v-icon>mdi-information</v-icon>
                  </v-col>
                  <v-col class="text-left">
                    <ul>
                      <li>{{ $t('followup.shortDistance.hints.hint1') }}</li>
                    </ul>
                  </v-col>
                </v-row>
              </p>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    longDistanceSubscriptions:{
      type: Object,
      default: null
    },
    shortDistanceSubscriptions:{
      type: Object,
      default: null
    }
  },
  data() {
    return {
      panel: [0,1]
    }
  },
  computed:{
    shortDistanceProgress(){
      if(this.shortDistanceSubscriptions){
        return this.shortDistanceSubscriptions.length;
      }
      return 0;
    },
    longDistanceProgress(){
      if(this.longDistanceSubscriptions){
        return this.longDistanceSubscriptions.length;
      }
      return 0;
    }
  },
  mounted(){

  },
  methods:{
    getBankCoordinates(){
      this.loading = true;
      maxios.post(this.$t("additional.uri.getCoordinates"))
        .then(response => {
          // console.error(response.data);
          if(response.data){
            if(response.data[0]) this.bankCoordinates = response.data[0];
            this.title = this.$t('titleAlreadyRegistered')
            this.loading = false;
          }
        })
        .catch(function (error) {
          console.error(error);
        });
    },
  }

};
</script>

