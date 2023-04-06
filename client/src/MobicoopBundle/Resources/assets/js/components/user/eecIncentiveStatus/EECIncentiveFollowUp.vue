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
                      <li>{{ $t('followup.longDistance.hints.hint3') }}</li>
                      <li>{{ $t('followup.longDistance.hints.hint4') }}</li>
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
                      <li>{{ $t('followup.shortDistance.hints.hint2') }}</li>
                      <li>{{ $t('followup.shortDistance.hints.hint3') }}</li>
                    </ul>
                  </v-col>
                </v-row>
              </p>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
        <v-row>
          <v-col cols="6">
            <v-row>
              <v-col class="text-h3">
                {{ pendingProofs }}
              </v-col>
            </v-row>
            <v-row>
              <v-col>{{ $t('followup.proofs.pending') }}</v-col>
            </v-row>
          </v-col>
          <v-col cols="6">
            <v-row>
              <v-col class="text-h3">
                {{ refusedProofs }}
              </v-col>
            </v-row>
            <v-row>
              <v-col>{{ $t('followup.proofs.refused') }}</v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import { merge } from "lodash";
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/EECIncentiveStatus/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
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
    },
    pendingProofs:{
      type: Number,
      default: 0
    },
    refusedProofs:{
      type: Number,
      default: 0
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

