<template>
  <div>
    <v-row justify="center">
      <v-col
        cols="12"
        md="10"
        class="text-center text-h6 pt-4"
        :class="(!formActive) ? 'text--disabled' : ''"
      >
        {{ $t('title') }}
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col
        cols="12"
        class="text-justify pt-4 font-italic"
        :class="(!formActive) ? 'text--disabled' : ''"
      >
        {{ $t('text') }}
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col
        cols="10"
        class="text-left pt-4 font-italic"
      >
        <template>
          <v-file-input
            :accept="validationDocsAuthorizedExtensions"
            :label="$t('fileInput')"
            :disabled="!formActive"
          />
        </template>
      </v-col>
      <v-col cols="2">
        <v-btn 
          rounded
          color="secondary"
          :disabled="!formActive"
        >
          {{ $t('send') }}
        </v-btn>          
      </v-col>
    </v-row>
  </div>
</template>
<script>
import Translations from "@translations/components/user/profile/payment/IdentityValidation.json";
export default {
  i18n: {
    messages: Translations
  },
  props: {
    canBePaid:{
      type: Boolean,
      default: false
    },
    validationDocsAuthorizedExtensions:{
      type: String,
      default: null
    },
    paymentProfileStatus: {
      type: Number,
      default: 0
    },    
    validationStatus: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
    }
  },
  computed:{
    formActive(){
      if(this.paymentProfileStatus==0){
        return false;
      }
      else if(this.validationStatus==1){
        return true;
      }
      return false;
    }
  },
  mounted(){
  }    
}
</script>