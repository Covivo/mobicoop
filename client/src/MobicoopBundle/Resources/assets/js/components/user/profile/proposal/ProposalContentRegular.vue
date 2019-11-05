<template>
  <v-container
    fluid
  >
    <v-row>
      <v-col cols="5">
        <regular-days-summary
          :mon-active="hasMonday"
          :tue-active="hasTuesday"
          :wed-active="hasWednesday"
          :thu-active="hasThursday"
          :fri-active="hasFriday"
          :sat-active="hasSaturday"
          :sun-active="hasSunday"
          :date-end-of-validity="proposal.outward.criteria.toDate"
        />
      </v-col>
      
      <!--Outward-->
      <v-col>
        <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('outward') }}</span>

        <v-icon class="accent--text text--darken-2 font-weight-bold">
          mdi-arrow-right
        </v-icon>

        <span
          v-if="hasSameOutwardTimes"
          class="primary--text text--darken-3 body-1"
        >
          {{ formatTime(proposal.outward.criteria.monTime) }}
        </span>
        <span
          v-else
          class="primary--text text--darken-3 body-1"
        >
          {{ $t('multiple') }}
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

        <span
          v-if="hasSameReturnTimes"
          class="primary--text text--darken-3 body-1"
        >
          {{ formatTime(proposal.return.criteria.monTime) }}
        </span>
        <span
          v-else
          class="primary--text text--darken-3 body-1"
        >
          {{ $t('multiple') }}
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
      required: true
    }
  },
  data () {
    return {
      outwardTimes: this.proposal.outward ? [
        this.proposal.outward.criteria.monTime, 
        this.proposal.outward.criteria.tueTime, 
        this.proposal.outward.criteria.wedTime, 
        this.proposal.outward.criteria.thuTime, 
        this.proposal.outward.criteria.friTime, 
        this.proposal.outward.criteria.satTime, 
        this.proposal.outward.criteria.sunTime
      ] : [],
      returnTimes: this.proposal.return ? [
        this.proposal.return.criteria.monTime,
        this.proposal.return.criteria.tueTime,
        this.proposal.return.criteria.wedTime,
        this.proposal.return.criteria.thuTime,
        this.proposal.return.criteria.friTime,
        this.proposal.return.criteria.satTime,
        this.proposal.return.criteria.sunTime
      ] : []
    }
  },
  computed: {
    hasReturn () {
      return this.proposal.return;
    },
    isRegular () {
      return this.proposal.outward.criteria.frequency === 2;
    },
    hasMonday () {
      return (this.proposal.outward && this.proposal.outward.criteria.monCheck) || 
        (this.proposal.return && this.proposal.return.criteria.monCheck);
    },
    hasTuesday () {
      return (this.proposal.outward && this.proposal.outward.criteria.tueCheck) || 
        (this.proposal.return && this.proposal.return.criteria.tueCheck);
    },
    hasWednesday () {
      return (this.proposal.outward && this.proposal.outward.criteria.wedCheck) || 
        (this.proposal.return && this.proposal.return.criteria.wedCheck);
    },
    hasThursday () {
      return (this.proposal.outward && this.proposal.outward.criteria.thuCheck) || 
        (this.proposal.return && this.proposal.return.criteria.thuCheck);
    },
    hasFriday () {
      return (this.proposal.outward && this.proposal.outward.criteria.friCheck) || 
        (this.proposal.return && this.proposal.return.criteria.friCheck);
    },
    hasSaturday () {
      return (this.proposal.outward && this.proposal.outward.criteria.satCheck) || 
        (this.proposal.return && this.proposal.return.criteria.satCheck);
    },
    hasSunday () {
      return (this.proposal.outward && this.proposal.outward.criteria.sunCheck) || 
        (this.proposal.return && this.proposal.return.criteria.sunCheck);
    },
    hasSameOutwardTimes () {
      moment.locale(this.locale);
      let isSame = true;
      // start to 1 because we don't compare index 0 with index 0
      for (let i = 1; i < this.outwardTimes.length; i++) {
        if (!this.outwardTimes[i]) {
          continue;
        }
        isSame = moment(this.outwardTimes[0]).isSame(this.outwardTimes[i]);
        if (!isSame) {
          break;
        }
      }
      return isSame;
    },
    hasSameReturnTimes () {
      moment.locale(this.locale);
      let isSame = true;
      // start to 1 because we don't compare index 0 with index 0
      for (let i = 1; i < this.returnTimes.length; i++) {
        if (!this.returnTimes[i]) {
          continue;
        }
        isSame = moment(this.returnTimes[0]).isSame(this.returnTimes[i]);
        if (!isSame) {
          break;
        }
      }
      return isSame;
    }
  },
  methods: {
    formatTime(time) {
      return moment(time).format(this.$t("ui.i18n.time.format.hourMinute"));
    }
  }
}
</script>

<style scoped>

</style>