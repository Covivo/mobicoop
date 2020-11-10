<template>
  <v-tabs>
    <v-tab href="#reviewsToGive">
      {{ $t('tabs.reviewsToGive') }}
    </v-tab>
    <v-tab href="#receivedReviews">
      {{ $t('tabs.receivedReviews') }}
    </v-tab>
    <v-tab href="#givenReviews">
      {{ $t('tabs.givenReviews') }}
    </v-tab>
    <v-tab-item value="givenReviews">
      givenReviews
    </v-tab-item>
    <v-tab-item value="receivedReviews">
      receivedReviews
    </v-tab-item>
    <v-tab-item value="reviewsToGive">
      reviewsToGive
    </v-tab-item>
  </v-tabs>
</template>
<script>
import axios from "axios";
import {messages_en, messages_fr} from "@translations/components/user/profile/review/ReviewDashboard/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  mounted(){
    this.getDashboard();
  },
  methods:{
    getDashboard(){
      axios
        .post(this.$t('getDashboardUri'))
        .then(res => {
          console.log(res.data);
        });        
    }
  }
}
</script>