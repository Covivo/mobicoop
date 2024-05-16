<template>
  <div
    :id="$t('EEC-form')"
    class="mb-8"
  >
    <v-snackbar
      v-model="snackbar.displayed"
      :color="snackbar.color"
      :multi-line="snackbar.multiLine"
      :timeout="snackbar.timeout"
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
        :platform="platform"
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
        multiLine: false,
        text: null,
        timeout: 5000,
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
        case this.eecInstance.ldAvailable && this.eecInstance.sdAvailable && (!this.subscriptions.longDistanceSubscription || !this.subscriptions.longDistanceSubscription):
        case this.eecInstance.ldAvailable && !this.subscriptions.longDistanceSubscription:
        case this.eecInstance.sdAvailable && !this.subscriptions.shortDistanceSubscription:
          this.snackbar.multiLine = true
          this.snackbar.text = `<p class="font-weight-bold">${this.$t('EEC-subscription-snackbar.failed')}</p><p>${this.getSnackbarText()}</p>`
          this.snackbar.timeout = -1
          break

        default:
          this.snackbar.color = 'success'
          this.snackbar.multiLine = false
          this.snackbar.text = this.$t('EEC-subscription-snackbar.success')
          this.snackbar.timeout = 5000
          break
        }

        this.snackbar.displayed = true
      }
    },
    getSnackbarText() {
      return this.eecSsoAuthError === 'eec_user_not_france_connected'
        ? `${this.$t(`EEC-subscription-snackbar.errors.${this.eecSsoAuthError}`, {document: `https://cloud.fabmob.io/s/rm8zJAF7kwCSNM6`, contact: 'https://moncomptemobilite.fr/contact'})}`
        : `${this.$t(`EEC-subscription-snackbar.errors.${this.eecSsoAuthError}`)}`
    }
  },
};
</script>

