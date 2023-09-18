<template>
  <v-container>
    <v-row class="justify-center">
      <v-col
        cols="12"
        align="center"
      >
        <h1 class="primary--text">
          {{ $t('incentives.incentive.title') }}
        </h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col
        offset="2"
        cols="8"
      >
        <v-card class="my-16">
          <v-card-title>
            {{ incentive.title }}
          </v-card-title>
          <v-card-text>
            {{ incentive.description }}
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn
              v-if="isActionBtnDisplayed"
              class="my-5"
              color="secondary"
              target="_blank"
              :disabled="!incentive.subscriptionLink"
              :href="incentive.subscriptionLink"
            >
              {{ $t('incentives.incentive.button.text') }}
            </v-btn>
            <v-spacer />
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import { not_displayed_incentive_types } from "@utils/constants";

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/assistiveDevices";


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
    incentive: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    isActionBtnDisplayed() {
      return !not_displayed_incentive_types.find(type => type === this.incentive.type);
    }
  },
  methods: {
    onClickSubscriptionLink() {
      document.open(this.incentive.subscriptionLink);
    }
  },
}
</script>
