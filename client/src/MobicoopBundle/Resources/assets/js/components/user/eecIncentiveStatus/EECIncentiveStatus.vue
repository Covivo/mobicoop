<template>
  <div
    :id="$t('EEC-form')"
    class="mb-8"
  >
    <v-snackbar
      v-model="snackbar.displayed"
      :color="snackbar.color"
      :multi-line="true"
      top
    >
      <p v-html="snackbar.text" />
      <template v-slot:action="{ attrs }">
        <v-btn
          color="white"
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
        :api-uri="apiUri"
        :platform="platform"
      />
      <EECIncentiveAdditionalInformations
        v-else
        :eec-instance="eecInstance"
        :eec-subscriptions="subscriptions"
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
    },
    eecSsoAuthError: {
      type: String,
      default: null
    },
    apiUri: {
      type: String,
      default: null
    },
    platform: {
      type: String,
      default: ""
    },
  },
  data() {
    return {
      subscriptions: null,
      eecInstance: null,
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
      if(this.subscriptions && (this.subscriptions.longDistanceSubscription || this.subscriptions.shortDistanceSubscription)){
        return true;
      }
      return false;
    }
  },
  mounted(){
    this.getCeeInstance();
    this.getMyCeeSubscriptions();
  },
  methods:{
    getCeeInstance(){
      this.loading = true;
      maxios
        .get(this.$t('routes.getEecInstance'))
        .then(response => {
          this.eecInstance = response.data;
          this.loading = false;
        })
        .catch(error => {});
    },
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
        case this.subscriptions.longDistanceSubscription && this.subscriptions.shortDistanceSubscription:
          this.snackbar.color = 'success';
          this.snackbar.text = this.$t('EEC-subscription-snackbar.success');
          break;
        default:
          this.snackbar.text = `<p class="font-weight-bold">${this.$t('EEC-subscription-snackbar.failed')}</p><p>${this.$t(`EEC-subscription-snackbar.${this.eecSsoAuthError}`)}</p>`;
          break;
        }

        this.snackbar.displayed = true;
      }
    }
  },
};
</script>

