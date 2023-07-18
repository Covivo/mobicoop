<template>
  <div v-if="isMessageDisplayed">
    <v-card
      color="info"
      flat
      dark
      max-height="140px"
      rounded="0"
    >
      <v-card-text>
        <v-icon
          left
        >
          mdi-alert
        </v-icon>
        <span class="white--text ">
          CEE
        </span>
      </v-card-text>
    </v-card>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col
            sm="12"
            md="4"
            lg="3"
            xl="2"
          >
            <v-img
              max-width="100"
              src="/images/communication/logo-cee.png"
              class="mb-5"
            />
          </v-col>
          <v-col>
            <p v-html="getWarningMessage" />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import { DRIVER, PASSENGER } from "./../../user/mailbox/Messages";

import { merge } from "lodash";

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/WarningMessage";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/WarningMessage";

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
    carpoolersIdentity: {
      type: Object,
      default: () => {}
    },
    eecDisplay: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isMessageDisplayed: false,
      warningMessages: null,
    }
  },
  computed: {
    getWarningMessage() {
      return this.warningMessages.join('<br />');
    },
  },
  watch: {
    carpoolersIdentity: function (newVal, oldVal) {
      if (newVal != oldVal && this.carpoolersIdentity) {
        this.isMessageDisplayed = false;
        this.warningMessages = [];

        if (this.eecDisplay) {
          this.build();
        }
      }
    }
  },
  methods: {
    build() {
      switch (this.carpoolersIdentity.sender.role) {
      case DRIVER:
        // Driver with EEC status and passenger does not have his identity validated
        if (this.carpoolersIdentity.sender.eecStatus && 2 != this.carpoolersIdentity.recipient.identityStatus) {
          this.isMessageDisplayed = true;
          this.warningMessages.push(this.$t('eecWarning.driverWithEecStatusPassengerWithoutValidatedIdentity', {
            givenName: this.carpoolersIdentity.recipient.givenName,
            shortFamilyName: this.carpoolersIdentity.recipient.shortFamilyName,
            gender: this.carpoolersIdentity.recipient.gender === 1 ? this.$t('eecWarning.female') : this.$t('eecWarning.male')
          }, ));
        }

        break;

      case PASSENGER:
        // Passenger does not have identity validated
        if (2 != this.carpoolersIdentity.recipient.identityStatus) {
          this.isMessageDisplayed = true;
          this.warningMessages.push(this.$t('eecWarning.passengerWithoutValidatedIdentity'));
        }

        break;
      }
    },
  },
}
</script>
