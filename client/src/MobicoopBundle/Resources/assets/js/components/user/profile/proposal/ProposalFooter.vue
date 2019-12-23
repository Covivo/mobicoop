<template>
  <v-container fluid>
    <v-row>
      <v-col
        cols="3"
        class="primary--text"
      >
        <span v-if="seats && seats > 0">{{ seats }}&nbsp;{{ seats > 1 ? $t('seat.plural') : $t('seat.singular') }}</span>
      </v-col>
      <v-col
        cols="3"
        class="primary--text"
      >
        <span v-if="price && price > '0'">{{ price }} €</span>
      </v-col>
      <v-col
        cols="6"
        align="right"
      >
        <!-- <v-btn
          icon
          :disabled="idMessage === -1"
          outlined 
          fab 
          color="primary lighten-4"
          @click="openMailBox()"
        >
          <v-icon>
            mdi-email
          </v-icon>
        </v-btn> -->
        <v-btn
          color="secondary"
          rounded
          :disabled="computedRequestsCount <= 0"
          :href="$t('urlResult',{id:id})"
        >
          {{ computedRequestsCount }}&nbsp;{{ computedRequestsCount > 1 ? $t('potentialCarpooler.plural') : $t('potentialCarpooler.singular') }}
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { merge, find } from "lodash";
import Translations from "@translations/components/user/profile/proposal/ProposalFooter.js";
import TranslationsClient from "@clientTranslations/components/user/profile/proposal/ProposalFooter.js";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    id: {
      type: Number,
      default: null
    },
    seats: {
      type: Number,
      default: null
    },
    price: {
      type: String,
      default: null
    },
    isDriver: {
      type: Boolean,
      default: false
    },
    isPassenger: {
      type: Boolean,
      default: false
    },
    idMessage: {
      type: Number,
      default: -1
    },
    // passengers
    carpoolRequests: {
      type: Array,
      default: () => []
    },
    // drivers
    carpoolOffers: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    computedRequestsCount () {
      if (this.isDriver && !this.isPassenger) {
        return this.carpoolRequests.length;
      } else if (!this.isDriver && this.isPassenger) {
        return this.carpoolOffers.length;
      } else if (this.isDriver && this.isPassenger) {
        let data = [];
        this.carpoolRequests.forEach(request => {
          data.push(request.proposalRequest)
        });
        this.carpoolOffers.forEach(offer => {
          if (!find(data, {id: offer.proposalOffer.id})) {
            data.push(offer.proposalOffer)
          }
        });
        return data.length;
      } else {
        return 0;
      }
    }
  },
  methods: {
    post: function (path, params, method='post') {
      const form = document.createElement('form');
      form.method = method;
      form.action = window.location.origin+'/'+path;

      for (const key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement('input');
          hiddenField.type = 'hidden';
          hiddenField.name = key;
          hiddenField.value = params[key];
          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();
    },
    openMailBox () {
      let lParams = {
        idMessage: this.idMessage
      };
      this.post(`${this.$t("utilisateur/messages")}`, lParams);
    }
  }
}
</script>

<style scoped>

</style>