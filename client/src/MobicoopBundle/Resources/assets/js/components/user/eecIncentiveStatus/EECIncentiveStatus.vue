<template>
  <div
    :id="$t('EEC-form')"
    class="mb-8"
  >
    <v-snackbar
      v-model="snackbar.displayed"
      :color="snackbar.color"
      top
    >
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn
          color="black"
          text
          v-bind="attrs"
          @click="snackbar.displayed = false"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
    <div v-if="!loading">
      <EECIncentiveInitiateSubscription
        v-if="!subscriptionInitiated"
        :confirmed-phone-number="confirmedPhoneNumber"
        :driving-licence-number-filled="drivingLicenceNumberFilled"
      />
      <EECIncentiveAdditionalInformations
        v-else
        :long-distance-subscriptions="subscriptions.longDistanceSubscriptions"
        :short-distance-subscriptions="subscriptions.shortDistanceSubscriptions"
        :pending-proofs="subscriptions.nbPendingProofs"
        :refused-proofs="subscriptions.nbRejectedProofs"
        @changeTab="changeTab"
      />
    </div>
    <div v-else>
      <v-skeleton-loader
        class="mx-auto"
        max-width="100%"
        type="paragraph@2"
      />
    </div>
  </div>
</template>

<script>
import { merge } from "lodash";
import maxios from "@utils/maxios";
import EECIncentiveInitiateSubscription from '@components/user/eecIncentiveStatus/EECIncentiveInitiateSubscription';
import EECIncentiveAdditionalInformations from '@components/user/eecIncentiveStatus/EECIncentiveAdditionalInformations';
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

  components:{
    EECIncentiveInitiateSubscription,
    EECIncentiveAdditionalInformations
  },
  props: {
    confirmedPhoneNumber:{
      type: Boolean,
      default: false
    },
    drivingLicenceNumberFilled:{
      type: Boolean,
      default: false
    },
    isAfterEecSubscription: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      subscriptions:null,
      loading: false,
      snackbar: {
        color: 'error',
        displayed: false,
        text: null,
      },
    }
  },
  computed:{
    subscriptionInitiated(){
      if(this.subscriptions && (this.subscriptions.longDistanceSubscriptions || this.subscriptions.shortDistanceSubscriptions)){
        return true;
      }
      return false;
    }
  },
  mounted(){
    this.getMyCeeSubscriptions();
  },
  methods:{
    getMyCeeSubscriptions(){
      this.loading = true;
      maxios.get(this.$t("routes.getMyCeeSubscriptions"))
        .then(res => {
          this.subscriptions = res.data;
          this.loading = false;
          this.afterEECSubscriptionValidation();
        })
        .catch(function (error) {

        });
    },
    changeTab(tab){
      this.$emit('changeTab', tab);
    },
    afterEECSubscriptionValidation() {
      if (this.isAfterEecSubscription) {
        switch (true) {
        case this.subscriptions.longDistanceSubscriptions && this.getMyCeeSubscriptions.shortDistanceSubscriptions:
          this.snackbar.color = 'success';
          this.snackbar.text = this.$t('EEC-subscription-snackbar.success');
          break;
        case !this.subscriptions.longDistanceSubscriptions && this.getMyCeeSubscriptions.shortDistanceSubscriptions:
          this.snackbar.text = this.$t('EEC-subscription-snackbar.longDistanceFailed');
          break;
        case this.subscriptions.longDistanceSubscriptions && !this.subscriptions.shortDistanceSubscriptions:
          this.snackbar.text = this.$t('EEC-subscription-snackbar.shortDistanceFailed');
          break;
        default:
          this.snackbar.text = this.$t('EEC-subscription-snackbar.failed');
          break;
        }

        this.snackbar.displayed = true;
      }
    }
  },
};
</script>

