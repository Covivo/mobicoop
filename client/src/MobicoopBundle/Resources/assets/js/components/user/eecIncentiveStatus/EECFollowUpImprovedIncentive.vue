<template>
  <v-container class="pa-7 white">
    <v-row>
      <v-col
        class="primary--text d-flex align-center"
      >
        <v-icon
          color="primary"
          class="mr-2"
        >
          mdi-cash-multiple
        </v-icon>
        <span v-html="$t(`followupTab.subscriptions.${type}.paymentImproved`)" />
      </v-col>
    </v-row>
    <v-row
      align-content="start"
    >
      <v-col
        class="text-left"
        v-html="$t(`followupTab.subscriptions.${type}.intro`)"
      />
    </v-row>
    <v-row
      v-if="displayExpirationDate"
      class="text-left mb-4"
    >
      <v-col>
        <v-icon color="secondary">
          mdi-clock-time-five
        </v-icon>
        {{ $t('expirationText', { date: getDateAsString(subscription.expirationDate) }) }}
      </v-col>
    </v-row>
    <v-row
      align-content="center"
      class="my-5"
    >
      <v-col v-if="!hasSubscriptionExpired">
        <v-progress-linear
          v-model="subscriptionProgressPercentage"
          height="25"
        >
          <strong>{{ $t('followupTab.subscriptions.progress', {'nb':subscriptionProgress, 'max': getMaxJourneysNumber}) }}</strong>
        </v-progress-linear>
      </v-col>
      <v-col
        v-else
        class="text-left"
      >
        {{ $t(`followupTab.subscriptions.${type}.expiredText`) }}
      </v-col>
    </v-row>
    <v-row align-content="center">
      <v-col cols="1" />
      <v-col cols="4">
        <span class="font-weight-bold">{{ nbPendingProofs }}</span><br>
        {{ $t('followupTab.proofs.pending') }}
      </v-col>
      <v-col cols="2" />
      <v-col cols="4">
        <span class="font-weight-bold">{{ nbRejectedProofs }}</span><br>
        {{ $t('followupTab.proofs.refused') }}
      </v-col>
      <v-col cols="1" />
    </v-row>
    <v-row
      align-content="center"
      class="mt-10"
    >
      <v-col>
        <v-btn
          rounded
          color="secondary"
          :href="$t('followupTab.buttons.publish.href')"
        >
          {{ $t('followupTab.buttons.publish.text') }}
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import { merge } from "lodash";
import moment from 'moment';

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/EECIncentiveStatus/";

import { eec_type_short, eec_type_long, eec_type_short_journeys_max, eec_type_long_journeys_max } from "@utils/constants";

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
    type: {
      type: String,
      default: null
    },
    subscription: {
      type: Object,
      default: () => ({})
    },
    nbPendingProofs: {
      type: Number,
      default: 0
    },
    nbRejectedProofs: {
      type: Number,
      default: 0
    },
    nbValidatedProofs: {
      type: Number,
      default: 0
    },
  },
  computed: {
    displayExpirationDate() {
      return this.isSetExpirationDate && !this.hasSubscriptionExpired;
    },
    isShortType() {
      return this.type === eec_type_short;
    },
    isLongType() {
      return this.type === eec_type_long;
    },
    isSetExpirationDate() {
      return this.subscription.expirationDate;
    },
    getMaxJourneysNumber()
    {
      return this.isLongType ? eec_type_long_journeys_max : eec_type_short_journeys_max;
    },
    hasSubscriptionExpired() {
      const now = new Date();

      return this.isSetExpirationDate && this.subscription.expirationDate < now;
    },
    subscriptionProgress(){
      if(this.subscription && this.subscription.journeys){
        return this.subscription.journeys.length;
      }
      return 0;
    },
    subscriptionProgressPercentage() {
      return 100 / this.getMaxJourneysNumber * this.subscriptionProgress;
    },
  },
  methods: {
    getDateAsString(date) {
      return moment(date).format('LL')
    }
  }
}
</script>
