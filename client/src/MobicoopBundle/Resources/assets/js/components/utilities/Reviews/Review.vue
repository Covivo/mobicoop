<template>
  <v-container fluid>
    <v-row>
      <v-col
        cols="2"
        lg="3"
      >
        <v-card
          flat
          @click="showProfileDialog = true"
        >
          <ProfileAvatar :avatar="avatar" />
        </v-card>
      </v-col>
      <v-col>
        <v-row>
          <v-col>
            <v-card
              flat
              @click="showProfileDialog = true"
            >
              {{ givenName }} {{ shortFamilyName }}<br>{{ reviewDate }}
            </v-card>
          </v-col>
        </v-row>
        <v-row><v-col><div v-html="review.content" /></v-col></v-row>
      </v-col>
    </v-row>
    <PopupPublicProfile
      v-if="review.reviewed"
      :carpooler-id="carpoolerId"
      :carpooler-name="givenName+' '+shortFamilyName"
      :show-profile-dialog="showProfileDialog"
      @dialogClosed="showProfileDialog = false"
    />
  </v-container>
</template>

<script>
import moment from "moment";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
export default {
  components:{
    ProfileAvatar,
    PopupPublicProfile: () => import('@components/user/profile/PopupPublicProfile')
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
      showProfileDialog: false
    };
  },
  computed:{
    reviewDate(){
      return moment(this.review.date).format('DD/MM/YYYY');
    },
    carpoolerId(){
      return (this.showReviewedInfos) ? this.review.reviewed.id : this.review.reviewer.id;
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
