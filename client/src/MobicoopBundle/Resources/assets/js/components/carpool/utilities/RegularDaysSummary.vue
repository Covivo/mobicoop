<template>
  <div>
    <v-chip
      small
      :color="monActive ? 'primary' : null"
    >
      {{ $t('mon') }} 
    </v-chip>
    <v-chip
      small
      :color="tueActive ? 'primary' : null"
    >
      {{ $t('tue') }}
    </v-chip>
    <v-chip
      small
      :color="wedActive ? 'primary' : null"
    >
      {{ $t('wed') }} 
    </v-chip>
    <v-chip
      small
      :color="thuActive ? 'primary' : null"
    >
      {{ $t('thu') }}
    </v-chip>
    <v-chip
      small
      :color="friActive ? 'primary' : null"
    >
      {{ $t('fri') }} 
    </v-chip>
    <v-chip
      small
      :color="satActive ? 'primary' : null"
    >
      {{ $t('sat') }}
    </v-chip>
    <v-chip
      small
      :color="sunActive ? 'primary' : null"
    >
      {{ $t('sun') }}
    </v-chip>
    <p
      v-if="dateStartOfValidity && dateEndOfValidity"
      class="font-italic mt-1 text-caption"
    >
      {{ $t('valid') }}&nbsp;{{ formattedDateStartOfValidity }}&nbsp;{{ $t('dateValidUntil') }}&nbsp;{{ formattedDateEndOfValidity }}
    </p>
    <p
      v-else-if="dateEndOfValidity"
      class="font-italic mt-1"
    >
      {{ $t('dateValid') }}&nbsp;{{ formattedDateEndOfValidity }}
    </p>
  </div>
</template>

<script>
import moment from "moment";
import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/carpool/utilities/RegularDaysSummary/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/carpool/utilities/RegularDaysSummary/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
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
    dateEndOfValidity: {
      type: String,
      default: null
    },
    dateStartOfValidity: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      locale: this.$i18n.locale,
    }
  },
  computed: {
    formattedDateEndOfValidity () {
      return this.dateEndOfValidity
        ? moment(this.dateEndOfValidity).format(this.$t("shortCompleteDate"))
        : "";
    },
    formattedDateStartOfValidity () {
      return this.dateStartOfValidity
        ? moment(this.dateStartOfValidity).format(this.$t("shortCompleteDate"))
        : "";
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  }
};
</script>
<style scoped lang="scss">
</style>