<template>
  <div>
    <v-container fluid />
  </div>
</template>

<script>
import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/carpool/results/MatchingResults.json";
import TranslationsClient from "@clientTranslations/components/carpool/results/MatchingResults.json";
import MatchingResult from "@components/carpool/results/MatchingResult";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    MatchingResult
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    origin: {
      type: Object,
      default: null
    },
    destination: {
      type: Object,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    time: {
      type: String,
      default: null
    },
    user: {
      type:Object,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    communityId: {
      type: Number,
      default: null
    },
    distinguishRegular: {
      type: Boolean,
      default: false
    }
  },
  data : function() {
    return {
      loading : true,
      matchings: null
    };
  },
  computed: {
    numberOfMatchings() {
      return this.matchings ? Object.keys(this.matchings).length : 0 // ES5+
    }
  },
  created() {
    this.loading = true;
    axios.get(this.$t("matchingUrl"), {
      params: {
        "origin_latitude": Number(this.origin.latitude),
        "origin_longitude": Number(this.origin.longitude),
        "destination_latitude": Number(this.destination.latitude),
        "destination_longitude": Number(this.destination.longitude),
        "date": this.date,
        "time": this.time,
        "regular": this.regular,
        "userId": this.user ? this.user.id : null,
        "communityId": this.communityId
      }
    })
      .then((response) => {
        this.loading = false;
        this.matchings = response.data;
      })
      .catch((error) => {
        console.log(error);
      });
  },
  methods: {
    carpool(params) {
      this.$emit("carpool", {
        matching: params.matching
      });
    }
  }
};
</script>
<style>
</style>