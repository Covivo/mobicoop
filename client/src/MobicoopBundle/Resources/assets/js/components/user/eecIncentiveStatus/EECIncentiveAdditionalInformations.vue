<template>
  <div>
    <EECAuthenticationAlert
      :eec-subscriptions="eecSubscriptions"
    />
    <v-card
      v-if="!hasBankCoordinates || !validatedIdentity"
      flat
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
          {{ $t('additional.subtitle') }}
        </h2>
        <p class="font-weight-bold">
          {{ $t('additional.intro') }}
        </p>
        <p>
          <v-list class="text-left">
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>{{ $t('additional.mandatory1') }}</v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="loading ? 'silver' : hasBankCoordinates ? 'green' : 'red'">
                  {{ loading ? 'mdi-timer-sand-empty' : hasBankCoordinates ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>{{ $t('additional.mandatory2') }}</v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="loading ? 'silver' : validatedIdentity ? 'green' : 'red'">
                  {{ loading ? 'mdi-timer-sand-empty' : validatedIdentity ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
          </v-list>
          <br>
          <a
            class="subtitle-1"
            href="#"
            @click="changeTab"
          >{{ $t('additional.goToBankCoordinates') }}</a>
        </p>
      </v-card-text>
    </v-card>
    <div v-else>
      <EECIncentiveFollowUpTab
        v-if="isTabView"
        :eec-instance="eecInstance"
        :eec-subscriptions="eecSubscriptions"
        :platform="platform"
      />
      <EECIncentiveFollowUp
        v-else
        :long-distance-subscriptions="eecSubscriptions.longDistanceSubscriptions"
        :short-distance-subscriptions="eecSubscriptions.shortDistanceSubscriptions"
        :long-distance-subscription-expiration-date="eecSubscriptions.longDistanceSubscriptionExpirationDate"
        :short-distance-subscription-expiration-date="eecSubscriptions.shortDistanceSubscriptionExpirationDate"
        :pending-proofs="eecSubscriptions.pendingProofs"
        :refused-proofs="eecSubscriptions.refusedProofs"
      />
    </div>
  </div>
</template>

<script>
import { merge } from "lodash";
import maxios from "@utils/maxios";
import EECIncentiveFollowUp from '@components/user/eecIncentiveStatus/EECIncentiveFollowUp';
import EECIncentiveFollowUpTab from '@components/user/eecIncentiveStatus/EECIncentiveFollowUpTab';
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/EECIncentiveStatus/";
import EECAuthenticationAlert from "./EECAuthenticationAlert.vue";

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
    EECIncentiveFollowUp,
    EECIncentiveFollowUpTab,
    EECAuthenticationAlert,
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
      bankCoordinates: null,
      loading: false
    }
  },
  computed:{
    isTabView() {
      return this.eecInstance.tabView;
    },
    hasBankCoordinates(){
      if(!this.bankCoordinates || this.bankCoordinates.status == 0){
        return false;
      }
      return true;
    },
    validatedIdentity(){
      if(!this.hasBankCoordinates || this.bankCoordinates.validationStatus == 0 || this.bankCoordinates.validationStatus > 1){
        return false;
      }
      return true;
    }
  },
  mounted(){
    this.getBankCoordinates();
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
    changeTab(){
      this.$emit('changeTab', 'bankCoordinates');
    }
  }

};
</script>

