<template>
  <v-container fluid>
    <v-row>
      <v-col
        cols="2"
        lg="3"
      >
        <ProfileAvatar :avatar="avatar" />
      </v-col>
      <v-col>
        <v-row><v-col>{{ givenName }} {{ shortFamilyName }}<br>{{ reviewDate }}</v-col></v-row>
        <v-row><v-col><div v-html="review.content" /></v-col></v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import moment from "moment";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
export default {
  components:{
    ProfileAvatar
  },
  props: {
    review: {
      type: Object,
      default: null
    },
    showReviewedInfos:{
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
    };
  },  
  computed:{
    reviewDate(){
      return moment(this.review.date).format('DD/MM/YYYY');
    },
    givenName(){
      return (this.showReviewedInfos) ? this.review.reviewed.givenName : this.review.reviewer.givenName;
    },
    shortFamilyName(){
      return (this.showReviewedInfos) ? this.review.reviewed.shortFamilyName : this.review.reviewer.shortFamilyName;
    },
    avatar(){
      return (this.showReviewedInfos) ? this.review.reviewed.avatar : this.review.reviewer.avatar;
    }
  }
}
</script>