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
        <div v-if="isTabView">
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
              <EECFollowUpImprovedIncentive
                v-if="isImprovedVersion('SD')"
                type="SD"
                :subscription="eecSubscriptions.shortDistanceSubscription"
                :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
                :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
                :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
              />
              <EECFollowUpStandardIncentive
                v-else
                type="SD"
                :subscription="eecSubscriptions.shortDistanceSubscription"
                :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
                :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
                :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
                :platform="platform"
              />
            </v-tab-item>
            <v-tab-item>
              <EECFollowUpImprovedIncentive
                v-if="isImprovedVersion('LD')"
                type="LD"
                :subscription="eecSubscriptions.longDistanceSubscription"
                :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
                :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
                :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
              />
              <EECFollowUpStandardIncentive
                v-else
                type="LD"
                :subscription="eecSubscriptions.longDistanceSubscription"
                :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
                :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
                :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
                :platform="platform"
              />
            </v-tab-item>
          </v-tabs-items>
        </div>
        <div v-else>
          <div v-if="isSdAvailable">
            <EECFollowUpImprovedIncentive
              v-if="isImprovedVersion('SD')"
              type="SD"
              :subscription="eecSubscriptions.shortDistanceSubscription"
              :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
              :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
              :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
            />
            <EECFollowUpStandardIncentive
              v-else
              type="SD"
              :subscription="eecSubscriptions.shortDistanceSubscription"
              :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
              :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
              :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
              :platform="platform"
            />
          </div>
          <div v-else-if="isLdAvailable">
            <EECFollowUpImprovedIncentive
              v-if="isImprovedVersion('LD')"
              type="LD"
              :subscription="eecSubscriptions.longDistanceSubscription"
              :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
              :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
              :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
            />
            <EECFollowUpStandardIncentive
              v-else
              type="LD"
              :subscription="eecSubscriptions.longDistanceSubscription"
              :nb-pending-proofs="eecSubscriptions.nbPendingProofs"
              :nb-rejected-proofs="eecSubscriptions.nbPendingProofs"
              :nb-validated-proofs="eecSubscriptions.nbPendingProofs"
              :platform="platform"
            />
          </div>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import { merge } from "lodash";

import EECFollowUpStandardIncentive from '@components/user/eecIncentiveStatus/EECFollowUpStandardIncentive';
import EECFollowUpImprovedIncentive from '@components/user/eecIncentiveStatus/EECFollowUpImprovedIncentive';

import { eec_improved_version } from "@utils/constants";

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
    eecInstance: {
      type: Object,
      default: () => ({})
    },
    eecSubscriptions: {
      type: Object,
      default: () => ({})
    },
    platform: {
      type: String,
      default: ""
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
  computed: {
    isTabView() {
      return this.isLdAvailable && this.isSdAvailable                                   // The 2 subscriptions are available
      || (!this.isLdAvailable && this.eecSubscriptions.longDistanceSubscription)        // The LD subscription is not avavailable but the user has susbcribed to
      || (!this.isSdAvailable && this.eecSubscriptions.shortDistanceSubscription);      // The SD subscription is not avavailable but the user has susbcribed to
    },
    isLdAvailable() {
      return this.eecInstance.ldAvailable;
    },
    isSdAvailable() {
      return this.eecInstance.sdAvailable;
    }
  },
  methods:{
    isImprovedVersion(type) {
      if (this.eecSubscriptions) {
        switch (type) {
        case 'LD':
          return this.eecSubscriptions.longDistanceSubscription && eec_improved_version === this.eecSubscriptions.longDistanceSubscription.version;

        case 'SD':
          return this.eecSubscriptions.shortDistanceSubscription && eec_improved_version === this.eecSubscriptions.shortDistanceSubscription.version;
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
