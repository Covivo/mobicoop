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
      <!--      <v-col-->
      <!--        cols="6"-->
      <!--        align="right"-->
      <!--      >-->
      <!--        <v-btn-->
      <!--          icon-->
      <!--          :disabled="idMessage === -1"-->
      <!--          outlined-->
      <!--          fab-->
      <!--          color="primary lighten-4"-->
      <!--          @click="openMailBox()"-->
      <!--        >-->
      <!--          <v-icon>-->
      <!--            mdi-email-->
      <!--          </v-icon>-->
      <!--        </v-btn>-->
      <!--      </v-col>-->
    </v-row>
    <v-expansion-panels
      v-model="panelActive"
      :accordion="true"
      :tile="true"
      :flat="true"
    >
      <v-expansion-panel>
        <v-expansion-panel-header>
          {{ panelActive === 0 ? $t('passengers.hide') :$t('passengers.show') }}
          <template v-slot:actions>
            <v-icon
              color="teal"
              large
            >
              $expand
            </v-icon>
          </template>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <carpooler
            v-for="result in ad.results"
            :key="result.carpooler.id"
            :result="result"
            :ad="ad"
          />
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/carpool/CarpoolFooter.js";
import TranslationsClient from "@clientTranslations/components/user/profile/carpool/CarpoolFooter.js";

import Carpooler from '@components/user/profile/carpool/Carpooler.vue';

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    Carpooler
  },
  props: {
    ad: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      panelActive: false,
      seats: (this.isDriver) ? this.ad.seatsDriver : this.ad.seatsPassenger,
      price: (this.isDriver) ? this.ad.outwardDriverPrice : this.ad.outwardPassengerPrice
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
    // openMailBox () {
    //   let lParams = {
    //     idMessage: this.idMessage
    //   };
    //   this.post(`${this.$t("utilisateur/messages")}`, lParams);
    // }
  }
}
</script>

<style scoped>

</style>