<template>
  <v-container fluid>
    <v-row class="align-center">
      <v-col
        cols="2"
      >
        <ProfileAvatar :avatar="avatar" />
      </v-col>
      <v-col cols="8">
        <v-form v-model="valid">
          <v-textarea
            v-model="content"
            :label="labelTxt"
            :rows="rows"
            required
          />
        </v-form>
      </v-col>
      <v-col
        cols="2"
        class="text-right"
      >
        <v-btn
          color="primary"
          rounded
        >
          Valider
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import {messages_en, messages_fr} from "@translations/components/utilities/Reviews/WriteReview";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  components:{
    ProfileAvatar
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
    }
  },
  data(){
    return{
      content:null,
      valid:false
    }
  },
  computed:{
    avatar(){
      return this.reviewed.avatars[this.reviewed.avatars.length-1];
    },
    labelTxt(){
      if(this.label){
        return this.label;
      }
      else{
        return this.$t('label');
      }
    }
  }
}
</script>