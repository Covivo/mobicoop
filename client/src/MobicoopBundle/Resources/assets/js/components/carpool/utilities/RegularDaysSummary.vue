<template>
  <div>
    <v-chip
      small
      :color="monActive ? 'primary' : null"
    >
      {{ $t('ui.abbr.day.mon') }} 
    </v-chip>
    <v-chip
      small
      :color="tueActive ? 'primary' : null"
    >
      {{ $t('ui.abbr.day.tue') }}
    </v-chip>
    <v-chip
      small
      :color="wedActive ? 'primary' : null"
    >
      {{ $t('ui.abbr.day.wed') }} 
    </v-chip>
    <v-chip
      small
      :color="thuActive ? 'primary' : null"
    >
      {{ $t('ui.abbr.day.thu') }}
    </v-chip>
    <v-chip
      small
      :color="friActive ? 'primary' : null"
    >
      {{ $t('ui.abbr.day.fri') }} 
    </v-chip>
    <v-chip
      small
      :color="satActive ? 'primary' : null"
    >
      {{ $t('ui.abbr.day.sat') }}
    </v-chip>
    <v-chip
      small
      :color="sunActive ? 'primary' : null"
    >
      {{ $t('ui.abbr.day.sun') }}
    </v-chip>
  </div>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/carpool/utilities/RegularDaysSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/RegularDaysSummary.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    monActive: {
      type: Boolean,
      default: false
    },
    tueActive: {
      type: Boolean,
      default: false
    },
    wedActive: {
      type: Boolean,
      default: false
    },
    thuActive: {
      type: Boolean,
      default: false
    },
    friActive: {
      type: Boolean,
      default: false
    },
    satActive: {
      type: Boolean,
      default: false
    },
    sunActive: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
    };
  }
};
</script>