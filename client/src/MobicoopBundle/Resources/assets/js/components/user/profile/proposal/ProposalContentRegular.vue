<template>
  <v-container
    fluid
  >
    <v-row>
      <v-col cols="5">
        <regular-days-summary
          :mon-active="proposal.outward.criteria.monCheck"
          :tue-active="proposal.outward.criteria.tueCheck"
          :wed-active="proposal.outward.criteria.wedCheck"
          :thu-active="proposal.outward.criteria.thuCheck"
          :fri-active="proposal.outward.criteria.friCheck"
          :sat-active="proposal.outward.criteria.satCheck"
          :sun-active="proposal.outward.criteria.sunCheck"
          :date-end-of-validity="proposal.outward.criteria.toDate"
        />
      </v-col>
      <v-col>
        <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('outward') }}</span>

        <v-icon class="accent--text text--darken-2 font-weight-bold">
          mdi-arrow-right
        </v-icon>

        <span class="primary--text text--darken-3 body-1">
          {{ formatTime(proposal.outward.criteria.monTime) }}
        </span>
      </v-col>

      <!-- Return -->
      <v-col
        v-if="hasReturn"
      >
        <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('return') }}</span>

        <v-icon class="accent--text text--darken-2 font-weight-bold">
          mdi-arrow-left
        </v-icon>

        <span class="primary--text text--darken-3 body-1">
          {{ formatTime(proposal.return.criteria.monTime) }}
        </span>
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col
        cols="12"
        class="primary darken-4"
      >
        <v-container class="pa-0">
          <v-row>
            <v-col
              cols="6"
              class="py-0"
            >
              <route-summary
                :origin="proposal.outward.waypoints[0].address"
                :destination="proposal.outward.waypoints[proposal.outward.waypoints.length - 1].address"
                :type="proposal.outward.criteria.frequency"
                :regular="isRegular"
                text-color-class="white--text"
                icon-color="accent"
              />
            </v-col>
          </v-row>
        </v-container>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import moment from "moment";
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/proposal/ProposalContentRegular.js";
import TranslationsClient from "@clientTranslations/components/user/profile/proposal/ProposalContentRegular.js";

import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary.vue';
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    RegularDaysSummary,
    RouteSummary,
  },
  props: {
    proposal: {
      type: Object,
      default: null
    }
  },
  computed: {
    hasReturn () {
      return this.proposal.return;
    },
    isRegular () {
      return this.proposal.outward.criteria.frequency === 2;
    }
  },
  methods: {
    formatTime(time) {
      moment.locale(this.locale);
      return moment(time).format(this.$t("ui.i18n.time.format.hourMinute"));
    }
  }
}
</script>

<style scoped>

</style>