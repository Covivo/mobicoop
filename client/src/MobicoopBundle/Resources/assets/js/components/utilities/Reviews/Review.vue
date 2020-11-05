<template>
  <v-container fluid>
    <v-row>
      <v-col
        cols="2"
        lg="3"
      >
        <ProfileAvatar :avatar="review.reviewer.avatar" />
      </v-col>
      <v-col>
        <v-row><v-col>{{ review.reviewer.givenName }} {{ review.reviewer.shortFamilyName }}<br>{{ reviewDate }}</v-col></v-row>
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
  },
  data() {
    return {
      locale: this.$i18n.locale,
    };
  },  
  computed:{
    reviewDate(){
      return moment(this.review.date.moment).format('DD/MM/YYYY');
    }
  }
}
</script>