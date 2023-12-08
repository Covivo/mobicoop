<template>
  <div>
    <v-card color="grey lighten-4">
      <v-card-title
        class="text-center"
      >
        {{ $t('title') }}
      </v-card-title>
      <v-card-text class="text-center">
        <h2
          class="mb-4"
        >
          {{ $t('followupTab.subtitle') }}
        </h2>

        <v-tabs
          v-model="tabs.tab"
          grow
          background-color="transparent"
        >
          <v-tab
            v-for="title in tabs.titles"
            :key="title"
          >
            {{ title }}
          </v-tab>
        </v-tabs>
        <v-tabs-items
          v-model="tabs.tab"
          class="transparent-background"
        >
          <v-tab-item>
            <EECFollowUpStandardIncentive
              v-if="isStandardVersion('SD')"
              type="SD"
              :subscription="eecSubscriptions.shortDistanceSubscription"
              :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
              :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
              :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
            />
            <EECFollowUpImprovedIncentive
              v-else
              type="SD"
              :subscription="eecSubscriptions.shortDistanceSubscription"
              :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
              :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
              :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
            />
          </v-tab-item>
          <v-tab-item>
            <EECFollowUpStandardIncentive
              v-if="isStandardVersion('LD')"
              type="LD"
              :subscription="eecSubscriptions.longDistanceSubscription"
              :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
              :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
              :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
            />
            <EECFollowUpImprovedIncentive
              v-else
              type="LD"
              :subscription="eecSubscriptions.longDistanceSubscription"
              :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
              :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
              :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
            />
          </v-tab-item>
        </v-tabs-items>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import { merge } from "lodash";

import EECFollowUpStandardIncentive from '@components/user/eecIncentiveStatus/EECFollowUpStandardIncentive';
import EECFollowUpImprovedIncentive from '@components/user/eecIncentiveStatus/EECFollowUpImprovedIncentive';

import { eec_standard_version } from "@utils/constants";

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/EECIncentiveStatus/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);



export default {
  components: {
    EECFollowUpStandardIncentive,
    EECFollowUpImprovedIncentive
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  props: {
    eecSubscriptions: {
      type: Object,
      default: () => ({})
    },
  },
  data() {
    return {
      tabs: {
        tab: null,
        titles: [
          this.$t('followupTab.sd-tab.title'),
          this.$t('followupTab.ld-tab.title'),
        ],
        content: []
      }
    }
  },
  methods:{
    isStandardVersion(type) {
      if (this.eecSubscriptions && this.eecSubscriptions.longDistanceSubscription && this.eecSubscriptions.shortDistanceSubscription) {
        switch (type) {
        case 'LD': return eec_standard_version === this.eecSubscriptions.longDistanceSubscription.version;

        case 'SD': return eec_standard_version === this.eecSubscriptions.shortDistanceSubscription.version;
        }
      }

      return false;
    }
  }

};
</script>
<style lang="scss" scoped>
.transparent-background {
  background-color: transparent !important;
}
</style>
