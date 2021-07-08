<template>
  <v-container fluid>
    <v-row
      v-if="showTitle"
      no-gutters
    >
      <v-col
        cols="12"
        class="text-left title"
      >
        {{ $t('reviewsTitle') }}
      </v-col>
    </v-row>
    <v-row
      no-gutters
    >
      <v-col
        v-for="review in reviews"
        :key="review.id"
        cols="12"
        lg="6"
        class="text-left"
      >
        <Review
          :review="review"
          :show-reviewed-infos="showReviewedInfos"
        />
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/Reviews/Reviews/";
import Review from "@components/utilities/Reviews/Review";
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
    Review
  },
  props:{
    reviews:{
      type:Array,
      default: null
    },
    showTitle:{
      type: Boolean,
      default: true
    },
    showReviewedInfos:{
      type: Boolean,
      default: false
    }
  }
}
</script>