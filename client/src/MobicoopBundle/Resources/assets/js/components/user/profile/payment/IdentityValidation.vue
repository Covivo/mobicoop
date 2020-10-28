<template>
  <div>
    <v-row justify="center">
      <v-col
        cols="12"
        md="10"
        class="text-center text-h6 pt-4"
        :class="(!formActive && validationAskedDate==null) ? 'text--disabled' : ''"
      >
        {{ $t('title') }}
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col
        cols="12"
        class="text-justify pt-4 font-italic"
        :class="(!formActive && validationAskedDate==null) ? 'text--disabled' : ''"
      >
        {{ $t('text') }}
      </v-col>
    </v-row>
    <v-row v-if="validationAskedDate!=null || validationStatus > 0">
      <v-col cols="12">
        <v-card
          flat
          class="text-center"
        >
          <!-- Pending -->
          <v-card-text
            v-if="validationStatus==0"
            class="warning--text"
          >
            <v-icon class="warning--text">
              mdi-magnify-scan
            </v-icon>
            {{ $t('status.pending') }}
          </v-card-text>
          <!-- Validated -->
          <v-card-text
            v-else-if="validationStatus==1"
            class="success--text"
          >
            <v-icon class="success--text">
              mdi-check-circle-outline
            </v-icon>
            {{ $t('status.validated') }}
          </v-card-text>
          <!-- Refused -->
          <v-card-text
            v-else-if="validationStatus==2"
            class="error--text"
          >
            <v-icon class="error--text">
              mdi-close-circle-outline
            </v-icon>
            {{ $t('status.refused') }}
          </v-card-text>          
          <!-- Outdated -->
          <v-card-text
            v-else-if="validationStatus==3"
            class="warning--text"
          >
            <v-icon class="warning--text">
              mdi-alert-circle-outline
            </v-icon>
            {{ $t('status.outdated') }}
          </v-card-text>          
        </v-card>        
      </v-col>
    </v-row>    
    <v-row
      v-if="(validationAskedDate==null || validationStatus >= 2)"
      justify="center"
    >
      <v-col
        cols="10"
        class="text-left pt-4 font-italic"
      >
        <template>
          <v-file-input
            v-model="document"
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
          :disabled="!formActive || document == null"
          :loading="loading"
          @click="send"
        >
          {{ $t('send') }}
        </v-btn>          
      </v-col>
    </v-row>
  </div>
</template>
<script>
import axios from "axios";
import {merge} from "lodash";
import {messages_en, messages_fr} from "@translations/components/user/profile/payment/IdentityValidation/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/user/profile/payment/IdentityValidation/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
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
    },
    validationAskedDate: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      document:null,
      loading:false
    }
  },
  computed:{
    formActive(){
      if(this.paymentProfileStatus==0){
        // No active payment profile
        return false;
      }
      else if(this.validationStatus==0 && this.validationAskedDate==null){
        // Active payment profile but identity validation hasn't been asked
        return true;
      }
      else if(this.validationStatus >= 2){
        // Identity doccument rejected or outdated
        return true;
      }
      return false;
    }
  },
  mounted(){
  },
  methods: {
    send(){
      let sendDocument = new FormData();
      sendDocument.append("document", this.document);
      this.loading = true;
      axios
        .post(this.$t('sendUrl'), sendDocument,
          {
            headers:{
              'content-type': 'multipart/form-data'
            }
          })
        .then(res => {
          this.document = null;
          this.loading = false;
          this.$emit("identityDocumentSent",res.data);
        });

    }
  }
}
</script>