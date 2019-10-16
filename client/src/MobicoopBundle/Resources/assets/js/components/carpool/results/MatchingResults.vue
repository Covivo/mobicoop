<template>
  <v-content>
    <!--loading overlay-->
    <v-overlay 
      :value="loading"
      absolute
    >
      <v-progress-circular
        indeterminate
      />
    </v-overlay>

    <v-container fluid>
      <!-- Number of matchings -->
      <v-row 
        justify="center"
        align="center"
      >
        <v-col
          v-if="!loading"
          cols="12"
          align="left"
        >
          {{ $tc('matchingNumber', numberOfMatchings, { number: numberOfMatchings }) }}
        </v-col>

        <v-col
          v-else
          cols="12"
          align="left"
        >
          {{ $t('search') }}
        </v-col>
      </v-row>

      <!-- Matching results -->
      <v-row 
        v-for="(matching,index) in matchings"
        :key="index"
        justify="center"
      >
        <v-col
          cols="12"
          align="left"
        >    
          <!-- Matching result -->
          <matching-result
            :matching="matching"
            :user="user"
            :regular="regular"
            :distinguish-regular="distinguishRegular"
            :date="date"
            @carpool="carpool"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
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
    sharedMessages: CommonTranslations
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