<template>
  <div>
    <v-row
      align="center"
    >
      <!-- Times -->

      <!-- Single outward -->
      <v-col
        v-if="single && single.outward"
        :cols="single.return ? '3' : '7'"
      >
        <v-row
          dense
        >
          <v-col
            cols="auto"
          >
            {{ $t('outward') }}
          </v-col>
          <v-col
            cols="auto"
          >
            <v-icon
              slot="prepend"
            >
              mdi-arrow-right-circle
            </v-icon>
          </v-col>
          <v-col
            cols="auto"
          >
            {{ single.outward }}
          </v-col>
        </v-row>
      </v-col>

      <!-- Single return -->
      <v-col
        v-if="single && single.return"
        cols="3"
        offset="1"
      >
        <v-row
          dense
        >
          <v-col
            cols="auto"
          >
            {{ $t('return') }}
          </v-col>
          <v-col
            cols="auto"
          >
            <v-icon
              slot="prepend"
            >
              mdi-arrow-left-circle
            </v-icon>
          </v-col>
          <v-col
            cols="auto"
          >
            {{ single.return }}
          </v-col>
        </v-row>
      </v-col>

      <!-- Multi outward only -->
      <v-col
        v-if="!single && !proposal.proposalLinked"
        cols="7"
      >
        <v-row
          dense
        >
          <v-col
            cols="auto"
          >
            {{ $t('outward') }}
          </v-col>
          <v-col
            cols="auto"
          >
            <v-icon
              slot="prepend"
            >
              mdi-arrow-left-circle
            </v-icon>
          </v-col>
          <v-col
            cols="auto"
          >
            <span class="font-italic">{{ $t('multi') }}</span>
          </v-col>
        </v-row>
      </v-col>

      <!-- Multi outward/return -->
      <v-col
        v-if="!single && proposal.proposalLinked"
        cols="7"
      >
        <v-row
          dense
        >
          <v-col
            cols="auto"
          >
            {{ $t('outward') }}
          </v-col>
          <v-col
            cols="auto"
          >
            <v-icon
              slot="prepend"
            >
              mdi-arrow-left-right
            </v-icon>
          </v-col>
          <v-col
            cols="auto"
          >
            {{ $t('return') }} <span class="font-italic">{{ $t('multi') }}</span>
          </v-col>
        </v-row>
      </v-col>

      <!-- Days -->
      <v-col
        cols="5"
        class="text-right"
      >
        <v-chip
          small
          :color="monActive ? 'success' : 'default'"
        >
          {{ $t('ui.abbr.day.mon') }} 
        </v-chip>
        <v-chip
          small
          :color="tueActive ? 'success' : 'default'"
        >
          {{ $t('ui.abbr.day.tue') }}
        </v-chip>
        <v-chip
          small
          :color="wedActive ? 'success' : 'default'"
        >
          {{ $t('ui.abbr.day.wed') }} 
        </v-chip>
        <v-chip
          small
          :color="thuActive ? 'success' : 'default'"
        >
          {{ $t('ui.abbr.day.thu') }}
        </v-chip>
        <v-chip
          small
          :color="friActive ? 'success' : 'default'"
        >
          {{ $t('ui.abbr.day.fri') }} 
        </v-chip>
        <v-chip
          small
          :color="satActive ? 'success' : 'default'"
        >
          {{ $t('ui.abbr.day.sat') }}
        </v-chip>
        <v-chip
          small
          :color="sunActive ? 'success' : 'default'"
        >
          {{ $t('ui.abbr.day.sun') }}
        </v-chip>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/utilities/DaysSummary.json";
import TranslationsClient from "@clientTranslations/components/utilities/DaysSummary.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
    proposal: {
      type: Object,
      default: null
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
    };
  },
  computed: {
    monActive() {
      return (this.proposal.criteria.monCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.moncheck));
    },
    tueActive() {
      return  (this.proposal.criteria.tueCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.tueCheck));
    },
    wedActive() {
      return  (this.proposal.criteria.wedCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.wedCheck));
    },
    thuActive() {
      return  (this.proposal.criteria.thuCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.thuCheck));
    },
    friActive() {
      return  (this.proposal.criteria.friCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.friCheck));
    },
    satActive() {
      return  (this.proposal.criteria.satCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.satCheck));
    },
    sunActive() {
      return  (this.proposal.criteria.sunCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.sunCheck));
    },
    monTimes() {
      moment.locale(this.locale);
      if (this.monActive) {
        return {
          outward: this.proposal.criteria.monTime ? moment.utc(this.proposal.criteria.monTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null,
          return: this.proposal.proposalLinked ? (this.proposal.proposalLinked.criteria.monTime ? moment.utc(this.proposal.proposalLinked.criteria.monTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null) : null
        }
      }
      return null;
    },
    tueTimes() {
      moment.locale(this.locale);
      if (this.tueActive) {
        return {
          outward: this.proposal.criteria.tueTime ? moment.utc(this.proposal.criteria.tueTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null,
          return: this.proposal.proposalLinked ? (this.proposal.proposalLinked.criteria.tueTime ? moment.utc(this.proposal.proposalLinked.criteria.tueTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null) :null
        }
      }
      return null;
    },
    wedTimes() {
      moment.locale(this.locale);
      if (this.wedActive) {
        return {
          outward: this.proposal.criteria.wedTime ? moment.utc(this.proposal.criteria.wedTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null,
          return: this.proposal.proposalLinked ? (this.proposal.proposalLinked.criteria.wedTime ? moment.utc(this.proposal.proposalLinked.criteria.wedTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null) : null
        }
      }
      return null;
    },
    thuTimes() {
      moment.locale(this.locale);
      if (this.thuActive) {
        return {
          outward: this.proposal.criteria.thuTime ? moment.utc(this.proposal.criteria.thuTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null,
          return: this.proposal.proposalLinked ? (this.proposal.proposalLinked.criteria.thuTime ? moment.utc(this.proposal.proposalLinked.criteria.thuTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null) : null
        }
      }
      return null;
    },
    friTimes() {
      moment.locale(this.locale);
      if (this.friActive) {
        return {
          outward: this.proposal.criteria.friTime ? moment.utc(this.proposal.criteria.friTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null,
          return: this.proposal.proposalLinked ? (this.proposal.proposalLinked.criteria.friTime ? moment.utc(this.proposal.proposalLinked.criteria.friTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null) : null
        }
      }
      return null;
    },
    satTimes() {
      moment.locale(this.locale);
      if (this.satActive) {
        return {
          outward: this.proposal.criteria.satTime ? moment.utc(this.proposal.criteria.satTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null,
          return: this.proposal.proposalLinked ? (this.proposal.proposalLinked.criteria.satTime ? moment.utc(this.proposal.proposalLinked.criteria.satTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null) : null
        }
      }
      return null;
    },
    sunTimes() {
      moment.locale(this.locale);
      if (this.sunActive) {
        return {
          outward: this.proposal.criteria.sunTime ? moment.utc(this.proposal.criteria.sunTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null,
          return: this.proposal.proposalLinked ? (this.proposal.proposalLinked.criteria.sunTime ? moment.utc(this.proposal.proposalLinked.criteria.sunTime).format(this.$t("ui.i18n.time.format.hourMinute")) : null) : null
        }
      }
      return null;
    },
    single() {
      let times = null;
      if (this.monTimes) {
        times = this.monTimes;
      }
      if (this.tueTimes && times) {
        if (this.tueTimes.outward != times.outward || this.tueTimes.return != times.return) return null;
      } else if (this.tueTimes) {
        times = this.tueTimes;
      }
      if (this.wedTimes && times) {
        if (this.wedTimes.outward != times.outward || this.wedTimes.return != times.return) return null;
      } else if (this.wedTimes) {
        times = this.wedTimes;
      }
      if (this.thuTimes && times) {
        if (this.thuTimes.outward != times.outward || this.thuTimes.return != times.return) return null;
      } else if (this.thuTimes) {
        times = this.thuTimes;
      }
      if (this.friTimes && times) {
        if (this.friTimes.outward != times.outward || this.friTimes.return != times.return) return null;
      } else if (this.friTimes) {
        times = this.friTimes;
      }
      if (this.satTimes && times) {
        if (this.satTimes.outward != times.outward || this.satTimes.return != times.return) return null;
      } else if (this.satTimes) {
        times = this.satTimes;
      }
      if (this.sunTimes && times) {
        if (this.sunTimes.outward != times.outward || this.sunTimes.return != times.return) return null;
      } else if (this.sunTimes) {
        times = this.sunTimes;
      }
      return times;
    }
  },
  methods: {
    formatTime(time) {
      moment.locale(this.locale);
      return moment.utc(moment(new Date()).format('Y-MM-DD')+' '+time).format(this.$t("ui.i18n.time.format.hourMinute"));
    }
  }
};
</script>