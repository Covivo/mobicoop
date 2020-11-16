<template>
  <v-tabs grow>
    <v-tab href="#reviewsToGive">
      {{ $t('tabs.reviewsToGive') }}
    </v-tab>
    <v-tab href="#receivedReviews">
      {{ $t('tabs.receivedReviews') }}
    </v-tab>
    <v-tab href="#givenReviews">
      {{ $t('tabs.givenReviews') }}
    </v-tab>
    <v-tab-item value="reviewsToGive">
      <WriteReview
        v-for="(reviewToGive,index) in reviewsToGive"
        :key="index"
        :reviewed="reviewToGive.reviewed"
        :reviewer="reviewToGive.reviewer"
        :show-reviewed="true"
        @reviewLeft="reviewLeft"
      />
    </v-tab-item>
    <v-tab-item value="receivedReviews">
      <Reviews
        :reviews="receivedReviews"
        :show-title="false"
      />
    </v-tab-item>
    <v-tab-item value="givenReviews">
      <Reviews
        :reviews="givenReviews"
        :show-title="false"
        :show-reviewed-infos="true"
      />
    </v-tab-item>
  </v-tabs>
</template>
<script>
import axios from "axios";
import {messages_en, messages_fr} from "@translations/components/user/profile/review/ReviewDashboard/";
import Reviews from "@components/utilities/Reviews/Reviews";
import WriteReview from "@components/utilities/Reviews/WriteReview"
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  components:{
    Reviews,
    WriteReview
  },
  data(){
    return{
      givenReviews:null,
      receivedReviews:null,
      reviewsToGive:null
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
          this.givenReviews = res.data.givenReviews;
          this.receivedReviews = res.data.receivedReviews;
          this.reviewsToGive = res.data.reviewsToGive;
        });        
    },
    reviewLeft(data){
      console.log("reviewleft");
    }
  }
}
</script>