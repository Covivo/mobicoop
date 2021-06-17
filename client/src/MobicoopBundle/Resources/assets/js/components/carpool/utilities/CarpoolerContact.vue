<template>
  <v-row
    align="center"
    justify="end"
    class="min-width-no-flex mr-1"
  >
    <div v-if="carpooler.canReceiveReview && showReviewButton">
      <PopUpReview
        :reviewed="carpooler"
        :reviewer="user"
        @reviewLeft="reviewLeft"
      />
    </div>
    <div v-if="user && carpooler.telephone">
      <v-btn
        v-show="!phoneButtonToggled"
        class="mb-1"

        color="secondary"
        small
        depressed
        fab
        @click="toggleButton"
      >
        <v-icon>
          mdi-phone
        </v-icon>
      </v-btn>
      <v-btn
        v-show="phoneButtonToggled"
        color="secondary"
        small
        dark
        depressed
        rounded
        height="40px"
        class="mb-1"
        @click="toggleButton"
      >
        <v-icon>mdi-phone</v-icon>
        {{ carpooler.telephone }}
      </v-btn>
    </div>
    <div v-if="displayMailBox">
      <v-btn
        color="secondary"
        small
        depressed
        fab
        class="ml-2 mb-1"
      >
        <v-icon
          @click="openMailBox()"
        >
          mdi-email
        </v-icon>
      </v-btn>
    </div>
  </v-row>
</template>

<script>
import PopUpReview from "@js/components/utilities/Reviews/PopUpReview";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/utilities/CarpoolerSummary/";
import formData from "../../../utils/request";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components:{
    PopUpReview
  },
  props: {
    carpooler: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      default: null
    },
    askId: {
      type: Number,
      default: -99
    },
    displayMailBox: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      phoneButtonToggled: false,
      showReviewButton:true
    }
  },
  methods: {
    toggleButton () {
      this.phoneButtonToggled = !this.phoneButtonToggled;
    },
    openMailBox () {
      let lParams = {
        idAsk: this.askId
      };
      formData(this.$t('route.user.message'), lParams);
    },
    reviewLeft(){
      this.showReviewButton = false;
    }
  }
}
</script>