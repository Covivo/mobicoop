<template>
  <v-container fluid>
    <v-row no-gutters>
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
        <Review :review="review" />
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import {messages_en, messages_fr} from "@translations/components/user/profile/Reviews/";
import Review from "@components/utilities/Review";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
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
  }
}
</script>