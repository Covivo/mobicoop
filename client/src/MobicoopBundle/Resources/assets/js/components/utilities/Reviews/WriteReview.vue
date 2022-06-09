<template>
  <v-container fluid>
    <v-alert
      v-if="alertFail"
      type="error"
    >
      {{ $t('fail') }}
    </v-alert>
    <v-row class="align-center">
      <v-col
        cols="2"
        class="text-center"
      >
        <v-card
          flat
          @click="showProfileDialog = true"
        >
          <ProfileAvatar :avatar="avatar" />
        </v-card>
        <Report
          v-if="showReport"
          :user="reviewed"
          class="mt-2"
        />
      </v-col>
      <v-col
        v-if="showReviewed"
        cols="2"
      >
        <v-card
          flat
          @click="showProfileDialog = true"
        >
          {{ reviewedName }}
        </v-card>
      </v-col>
      <v-col :cols="showReviewed ? 6 : 8">
        <v-textarea
          v-model="content"
          :label="labelTxt"
          :rows="rows"
          required
        />
      </v-col>
      <v-col
        cols="2"
        class="text-right"
      >
        <v-btn
          color="primary"
          rounded
          :disabled="!content"
          :loading="loading"
          @click="leaveReview"
        >
          {{ $t("validate") }}
        </v-btn>
      </v-col>
    </v-row>
    <PopupPublicProfile
      :carpooler-id="reviewed.id"
      :carpooler-name="reviewed.givenName+' '+reviewed.shortFamilyName"
      :show-profile-dialog="showProfileDialog"
      @dialogClosed="showProfileDialog = false"
    />
  </v-container>
</template>
<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/Reviews/WriteReview";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
import Report from "@components/utilities/Report";
import PopupPublicProfile from "@components/user/profile/PopupPublicProfile";
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
    ProfileAvatar,
    Report,
    PopupPublicProfile
  },
  props:{
    reviewer:{
      type: Object,
      default: null
    },
    reviewed:{
      type: Object,
      default: null
    },
    label:{
      type:String,
      default:null
    },
    rows:{
      type:Number,
      default:3
    },
    showReviewed:{
      type: Boolean,
      default: false
    },
    showReport:{
      type: Boolean,
      default: true
    }
  },
  data(){
    return{
      content:null,
      valid:false,
      loading:false,
      alertFail:false,
      showProfileDialog: false
    }
  },
  computed:{
    avatar(){
      if(this.reviewed.avatars){
        return this.reviewed.avatars[this.reviewed.avatars.length-1];
      }
      else{
        return this.reviewed.avatar;
      }
    },
    labelTxt(){
      if(this.label){
        return this.label;
      }
      else{
        return this.$t('label');
      }
    },
    reviewedName(){
      return this.reviewed.givenName+' '+this.reviewed.shortFamilyName;
    }
  },
  mounted(){
    this.snackbarSuccess = true;
  },
  methods:{
    leaveReview(){
      this.loading = true;
      let reviewData = {
        "reviewerId": this.reviewer.id,
        "reviewedId": this.reviewed.id,
        "content":this.content
      };
      maxios.post(this.$t("leaveReviewUri"), reviewData)
        .then(response => {
          // console.error(response.data);
          this.loading = false;
          if(response.data.success){
            this.$emit("reviewLeft",{'success':response.data});
          }
          else{
            this.alertFail = true;
          }
        })
        .catch(function (error) {
          console.error(error);
        });
    }
  }
}
</script>
